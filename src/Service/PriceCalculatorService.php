<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Coupon;
use App\Enum\CouponType;
use App\Exception\CouponNotFoundException;
use App\Exception\PaymentException;
use App\Exception\ProductNotFoundException;
use App\Exception\TaxNotFoundException;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Repository\TaxRepository;
use App\Service\Dto\PriceCalculationData;

/**
 * Сервис расчета цены.
 * Работает с Service DTO - не знает ничего о HTTP слое.
 */
class PriceCalculatorService implements PriceCalculatorInterface
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly TaxRepository $taxRepository,
        private readonly CouponRepository $couponRepository,
    )
    {
    }

    /**
     * Рассчитывает итоговую цену продукта с учетом налога и купона
     */
    public function calculate(PriceCalculationData $data): float
    {
        if ($data->productId <= 0) {
            throw new PaymentException('Product ID must be positive');
        }

        $taxNumber = trim($data->taxNumber);
        if ($taxNumber === '') {
            throw new PaymentException('Tax number is required');
        }

        $product = $this->productRepository->find($data->productId);
        if (!$product) {
            throw new ProductNotFoundException($data->productId);
        }

        $countryCode = $this->extractCountryCode($taxNumber);
        $tax = $this->taxRepository->findByCountryCode($countryCode);
        if (!$tax) {
            throw new TaxNotFoundException($countryCode);
        }

        $this->assertTaxNumberMatchesPattern($taxNumber, $tax->getTaxNumberPattern(), $countryCode);

        $coupon = null;
        $couponCode = $data->couponCode !== null ? trim($data->couponCode) : null;
        if ($couponCode !== null && $couponCode !== '') {
            $coupon = $this->couponRepository->findOneBy(['code' => $couponCode]);
            if (!$coupon) {
                throw new CouponNotFoundException($couponCode);
            }
        }

        $basePrice = (float) $product->getPrice();
        
        // Применяем скидку
        $priceAfterDiscount = $coupon
            ? $this->applyDiscount($basePrice, $coupon) 
            : $basePrice;

        // Применяем налог к цене после скидки
        $taxRate = (float) $tax->getRate();
        $finalPrice = $priceAfterDiscount * (PriceCalculatorInterface::BASE_MULTIPLIER + $taxRate / PriceCalculatorInterface::PERCENT_DIVISOR);

        return round($finalPrice, PriceCalculatorInterface::MONEY_SCALE);
    }

    private function extractCountryCode(string $taxNumber): string
    {
        if (strlen($taxNumber) < PriceCalculatorInterface::TAX_NUMBER_MIN_LENGTH) {
            throw new PaymentException('Invalid tax number: too short');
        }

        return strtoupper(substr($taxNumber, 0, 2));
    }

    private function assertTaxNumberMatchesPattern(string $taxNumber, string $pattern, string $countryCode): void
    {
        $regex = '/' . $pattern . '/';
        if (!preg_match($regex, $taxNumber)) {
            throw new PaymentException(sprintf('Invalid tax number "%s" for country %s', $taxNumber, $countryCode));
        }
    }

    /**
     * Применяет скидку по купону
     */
    private function applyDiscount(float $price, Coupon $coupon): float
    {
        $discountValue = (float) $coupon->getValue();

        return match ($coupon->getType()) {
            CouponType::FIXED => max(PriceCalculatorInterface::MIN_PRICE, $price - $discountValue),
            CouponType::PERCENT => $price * (PriceCalculatorInterface::BASE_MULTIPLIER - $discountValue / PriceCalculatorInterface::PERCENT_DIVISOR),
        };
    }
}
