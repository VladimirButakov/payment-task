<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Entity\Tax;
use App\Enum\CouponType;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Repository\TaxRepository;

class PriceCalculatorService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly TaxRepository $taxRepository,
        private readonly CouponRepository $couponRepository,
    ) {
    }

    /**
     * Рассчитывает итоговую цену продукта с учетом налога и купона
     *
     * @throws \InvalidArgumentException если продукт не найден или налог не определен
     */
    public function calculatePrice(int $productId, string $taxNumber, ?string $couponCode = null): float
    {
        $product = $this->productRepository->find($productId);
        if (!$product) {
            throw new \InvalidArgumentException(sprintf('Product with ID %d not found', $productId));
        }

        $countryCode = strtoupper(substr($taxNumber, 0, 2));
        $tax = $this->taxRepository->findByCountryCode($countryCode);
        if (!$tax) {
            throw new \InvalidArgumentException(sprintf('Tax configuration not found for country %s', $countryCode));
        }

        $coupon = null;
        if ($couponCode) {
            $coupon = $this->couponRepository->findOneBy(['code' => $couponCode]);
            if (!$coupon) {
                throw new \InvalidArgumentException(sprintf('Coupon with code "%s" not found', $couponCode));
            }
        }

        return $this->calculate($product, $tax, $coupon);
    }

    /**
     * Выполняет расчет цены: (базовая цена - скидка) + налог
     */
    private function calculate(Product $product, Tax $tax, ?Coupon $coupon): float
    {
        $basePrice = (float) $product->getPrice();
        
        // Применяем скидку
        $priceAfterDiscount = $basePrice;
        if ($coupon) {
            $priceAfterDiscount = $this->applyDiscount($basePrice, $coupon);
        }

        // Применяем налог к цене после скидки
        $taxRate = (float) $tax->getRate();
        $finalPrice = $priceAfterDiscount * (1 + $taxRate / 100);

        // Округляем до 2 знаков
        return round($finalPrice, 2);
    }

    /**
     * Применяет скидку по купону
     */
    private function applyDiscount(float $price, Coupon $coupon): float
    {
        $discountValue = (float) $coupon->getValue();

        return match ($coupon->getType()) {
            CouponType::FIXED => max(0, $price - $discountValue),
            CouponType::PERCENT => $price * (1 - $discountValue / 100),
        };
    }
}

