<?php

declare(strict_types=1);

namespace App\Tests\unit\Service;

use App\Exception\PaymentException;
use App\Service\Dto\PriceCalculationData;
use App\Service\Dto\PurchaseData;
use App\Service\Payment\PaymentProcessorFactory;
use App\Service\Payment\PaymentProcessorInterface;
use App\Service\PaymentService;
use App\Service\PriceCalculatorInterface;
use Codeception\Test\Unit;

final class PaymentServiceTest extends Unit
{
    private PriceCalculatorInterface $priceCalculator;
    private PaymentProcessorFactory $processorFactory;
    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->priceCalculator = $this->createMock(PriceCalculatorInterface::class);
        $this->processorFactory = $this->createMock(PaymentProcessorFactory::class);
        
        $this->service = new PaymentService(
            $this->priceCalculator,
            $this->processorFactory,
        );
    }

    public function testPurchaseSuccess(): void
    {
        $processor = $this->createMock(PaymentProcessorInterface::class);

        $this->priceCalculator
            ->expects($this->once())
            ->method('calculate')
            ->with($this->callback(function (PriceCalculationData $data) {
                return $data->productId === 1
                    && $data->taxNumber === 'DE123456789'
                    && $data->couponCode === null;
            }))
            ->willReturn(119.00);

        $this->processorFactory
            ->expects($this->once())
            ->method('getProcessor')
            ->with('paypal')
            ->willReturn($processor);

        $processor
            ->expects($this->once())
            ->method('process')
            ->with(119.00);

        $result = $this->service->purchase(new PurchaseData(
            productId: 1,
            taxNumber: 'DE123456789',
            paymentProcessor: 'paypal',
            couponCode: null,
        ));

        $this->assertEquals(119.00, $result);
    }

    public function testPurchaseWithCoupon(): void
    {
        $processor = $this->createMock(PaymentProcessorInterface::class);

        $this->priceCalculator
            ->expects($this->once())
            ->method('calculate')
            ->with($this->callback(function (PriceCalculationData $data) {
                return $data->productId === 1
                    && $data->taxNumber === 'IT12345678900'
                    && $data->couponCode === 'D15';
            }))
            ->willReturn(103.70);

        $this->processorFactory
            ->expects($this->once())
            ->method('getProcessor')
            ->with('stripe')
            ->willReturn($processor);

        $processor
            ->expects($this->once())
            ->method('process')
            ->with(103.70);

        $result = $this->service->purchase(new PurchaseData(
            productId: 1,
            taxNumber: 'IT12345678900',
            paymentProcessor: 'stripe',
            couponCode: 'D15',
        ));

        $this->assertEquals(103.70, $result);
    }

    public function testExceptionIfPriceIsZero(): void
    {
        $this->priceCalculator
            ->expects($this->once())
            ->method('calculate')
            ->willReturn(0.00);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Calculated price must be greater than 0');

        $this->service->purchase(new PurchaseData(
            productId: 1,
            taxNumber: 'DE123456789',
            paymentProcessor: 'paypal',
            couponCode: 'F100', // 100% скидка
        ));
    }

    public function testExceptionIfPaymentProcessorNotFound(): void
    {
        $this->priceCalculator
            ->expects($this->once())
            ->method('calculate')
            ->willReturn(119.00);

        $this->processorFactory
            ->expects($this->once())
            ->method('getProcessor')
            ->with('unknown')
            ->willThrowException(new \InvalidArgumentException('Payment processor "unknown" not found'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment processor "unknown" not found');

        $this->service->purchase(new PurchaseData(
            productId: 1,
            taxNumber: 'DE123456789',
            paymentProcessor: 'unknown',
            couponCode: null,
        ));
    }

    public function testExceptionIfPaymentFails(): void
    {
        $processor = $this->createMock(PaymentProcessorInterface::class);

        $this->priceCalculator
            ->expects($this->once())
            ->method('calculate')
            ->willReturn(10.00);

        $this->processorFactory
            ->expects($this->once())
            ->method('getProcessor')
            ->with('stripe')
            ->willReturn($processor);

        $processor
            ->expects($this->once())
            ->method('process')
            ->with(10.00)
            ->willThrowException(new \Exception('Payment failed: amount is too low'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment failed: amount is too low');

        $this->service->purchase(new PurchaseData(
            productId: 3,
            taxNumber: 'DE123456789',
            paymentProcessor: 'stripe',
            couponCode: null,
        ));
    }
}
