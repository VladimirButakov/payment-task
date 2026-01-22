<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\PaymentException;
use App\Service\Dto\PriceCalculationData;
use App\Service\Dto\PurchaseData;
use App\Service\Payment\PaymentProcessorFactory;

/**
 * Сервис проведения платежей.
 * Работает с Service DTO - не знает о HTTP слое.
 */
class PaymentService implements PaymentInterface
{
    public function __construct(
        private readonly PriceCalculatorInterface $priceCalculator,
        private readonly PaymentProcessorFactory $paymentProcessorFactory,
    ) {
    }

    /**
     * Проводит покупку: рассчитывает цену и выполняет платеж
     *
     * @return float итоговая сумма платежа
     * @throws \Exception если платеж не удался
     */
    public function purchase(PurchaseData $data): float
    {
        $finalPrice = $this->priceCalculator->calculate(new PriceCalculationData(
            productId: $data->productId,
            taxNumber: $data->taxNumber,
            couponCode: $data->couponCode,
        ));

        if ($finalPrice <= 0) {
            throw new PaymentException('Calculated price must be greater than 0');
        }

        $processor = $this->paymentProcessorFactory->getProcessor($data->paymentProcessor);
        $processor->process($finalPrice);

        return $finalPrice;
    }
}
