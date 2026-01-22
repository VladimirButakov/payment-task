<?php

declare(strict_types=1);

namespace App\Tests\unit\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Entity\Tax;
use App\Enum\CouponType;
use App\Exception\CouponNotFoundException;
use App\Exception\PaymentException;
use App\Exception\ProductNotFoundException;
use App\Exception\TaxNotFoundException;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Repository\TaxRepository;
use App\Service\Dto\PriceCalculationData;
use App\Service\PriceCalculatorService;
use Codeception\Test\Unit;

final class PriceCalculatorServiceTest extends Unit
{
    private ProductRepository $productRepository;
    private TaxRepository $taxRepository;
    private CouponRepository $couponRepository;
    private PriceCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->taxRepository = $this->createMock(TaxRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);
        
        $this->service = new PriceCalculatorService(
            $this->productRepository,
            $this->taxRepository,
            $this->couponRepository,
        );
    }

    /**
     * Тест: Iphone (100€) + Германия (19%) = 119€
     */
    public function testCalculatePriceWithoutCoupon(): void
    {
        $product = $this->createProduct(1, 'Iphone', '100.00');
        $tax = $this->createTax('DE', '19.00', '^DE[0-9]{9}$');

        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);

        $this->taxRepository
            ->expects($this->once())
            ->method('findByCountryCode')
            ->with('DE')
            ->willReturn($tax);

        $result = $this->service->calculate(new PriceCalculationData(
            productId: 1,
            taxNumber: 'DE123456789',
            couponCode: null,
        ));

        $this->assertEquals(119.00, $result);
    }

    /**
     * Тест: Iphone (100€) + Греция (24%) = 124€
     */
    public function testCalculatePriceForGreece(): void
    {
        $product = $this->createProduct(1, 'Iphone', '100.00');
        $tax = $this->createTax('GR', '24.00', '^GR[0-9]{9}$');

        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);

        $this->taxRepository
            ->expects($this->once())
            ->method('findByCountryCode')
            ->with('GR')
            ->willReturn($tax);

        $result = $this->service->calculate(new PriceCalculationData(
            productId: 1,
            taxNumber: 'GR123456789',
            couponCode: null,
        ));

        $this->assertEquals(124.00, $result);
    }

    /**
     * Тест из README: Iphone (100€) - 6% + Греция (24%) = 116.56€
     */
    public function testCalculatePriceWithPercentCoupon(): void
    {
        $product = $this->createProduct(1, 'Iphone', '100.00');
        $tax = $this->createTax('GR', '24.00', '^GR[0-9]{9}$');
        $coupon = $this->createCoupon('D6', CouponType::PERCENT, '6.00');

        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);

        $this->taxRepository
            ->expects($this->once())
            ->method('findByCountryCode')
            ->with('GR')
            ->willReturn($tax);

        $this->couponRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'D6'])
            ->willReturn($coupon);

        $result = $this->service->calculate(new PriceCalculationData(
            productId: 1,
            taxNumber: 'GR123456789',
            couponCode: 'D6',
        ));

        // 100 - 6% = 94, 94 + 24% = 116.56
        $this->assertEquals(116.56, $result);
    }

    /**
     * Тест: Iphone (100€) - 10€ (fixed) + Германия (19%) = 107.10€
     */
    public function testCalculatePriceWithFixedCoupon(): void
    {
        $product = $this->createProduct(1, 'Iphone', '100.00');
        $tax = $this->createTax('DE', '19.00', '^DE[0-9]{9}$');
        $coupon = $this->createCoupon('F10', CouponType::FIXED, '10.00');

        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);

        $this->taxRepository
            ->expects($this->once())
            ->method('findByCountryCode')
            ->with('DE')
            ->willReturn($tax);

        $this->couponRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'F10'])
            ->willReturn($coupon);

        $result = $this->service->calculate(new PriceCalculationData(
            productId: 1,
            taxNumber: 'DE123456789',
            couponCode: 'F10',
        ));

        // 100 - 10 = 90, 90 + 19% = 107.10
        $this->assertEquals(107.10, $result);
    }

    /**
     * Тест: Фиксированная скидка больше цены товара — цена не уходит в минус
     */
    public function testFixedCouponDoesNotMakePriceNegative(): void
    {
        $product = $this->createProduct(3, 'Чехол', '10.00');
        $tax = $this->createTax('DE', '19.00', '^DE[0-9]{9}$');
        $coupon = $this->createCoupon('F100', CouponType::FIXED, '100.00');

        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(3)
            ->willReturn($product);

        $this->taxRepository
            ->expects($this->once())
            ->method('findByCountryCode')
            ->with('DE')
            ->willReturn($tax);

        $this->couponRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'F100'])
            ->willReturn($coupon);

        $result = $this->service->calculate(new PriceCalculationData(
            productId: 3,
            taxNumber: 'DE123456789',
            couponCode: 'F100',
        ));

        // 10 - 100 = 0 (не минус), 0 + 19% = 0
        $this->assertEquals(0.00, $result);
    }

    public function testExceptionIfProductNotFound(): void
    {
        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(ProductNotFoundException::class);
        $this->expectExceptionMessage('Product with ID 999 not found');

        $this->service->calculate(new PriceCalculationData(
            productId: 999,
            taxNumber: 'DE123456789',
            couponCode: null,
        ));
    }

    public function testExceptionIfTaxNotFound(): void
    {
        $product = $this->createProduct(1, 'Iphone', '100.00');

        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);

        $this->taxRepository
            ->expects($this->once())
            ->method('findByCountryCode')
            ->with('XX')
            ->willReturn(null);

        $this->expectException(TaxNotFoundException::class);
        $this->expectExceptionMessage('Tax configuration not found for country XX');

        $this->service->calculate(new PriceCalculationData(
            productId: 1,
            taxNumber: 'XX123456789',
            couponCode: null,
        ));
    }

    public function testExceptionIfCouponNotFound(): void
    {
        $product = $this->createProduct(1, 'Iphone', '100.00');
        $tax = $this->createTax('DE', '19.00', '^DE[0-9]{9}$');

        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);

        $this->taxRepository
            ->expects($this->once())
            ->method('findByCountryCode')
            ->with('DE')
            ->willReturn($tax);

        $this->couponRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'INVALID'])
            ->willReturn(null);

        $this->expectException(CouponNotFoundException::class);
        $this->expectExceptionMessage('Coupon with code "INVALID" not found');

        $this->service->calculate(new PriceCalculationData(
            productId: 1,
            taxNumber: 'DE123456789',
            couponCode: 'INVALID',
        ));
    }

    public function testExceptionIfInvalidTaxNumberFormat(): void
    {
        $product = $this->createProduct(1, 'Iphone', '100.00');
        $tax = $this->createTax('DE', '19.00', '^DE[0-9]{9}$');

        $this->productRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);

        $this->taxRepository
            ->expects($this->once())
            ->method('findByCountryCode')
            ->with('DE')
            ->willReturn($tax);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Invalid tax number "DE12345" for country DE');

        $this->service->calculate(new PriceCalculationData(
            productId: 1,
            taxNumber: 'DE12345', // Слишком короткий
            couponCode: null,
        ));
    }

    public function testExceptionIfProductIdNotPositive(): void
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Product ID must be positive');

        $this->service->calculate(new PriceCalculationData(
            productId: 0,
            taxNumber: 'DE123456789',
            couponCode: null,
        ));
    }

    public function testExceptionIfTaxNumberEmpty(): void
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Tax number is required');

        $this->service->calculate(new PriceCalculationData(
            productId: 1,
            taxNumber: '',
            couponCode: null,
        ));
    }

    private function createProduct(int $id, string $name, string $price): Product
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn($id);
        $product->method('getName')->willReturn($name);
        $product->method('getPrice')->willReturn($price);
        
        return $product;
    }

    private function createTax(string $countryCode, string $rate, string $pattern): Tax
    {
        $tax = $this->createMock(Tax::class);
        $tax->method('getCountryCode')->willReturn($countryCode);
        $tax->method('getRate')->willReturn($rate);
        $tax->method('getTaxNumberPattern')->willReturn($pattern);
        
        return $tax;
    }

    private function createCoupon(string $code, CouponType $type, string $value): Coupon
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('getCode')->willReturn($code);
        $coupon->method('getType')->willReturn($type);
        $coupon->method('getValue')->willReturn($value);
        
        return $coupon;
    }
}
