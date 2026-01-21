<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Payment\PaymentProcessorFactory;

class PaymentService
{
    public function __construct(
        private readonly PaymentProcessorFactory $processorFactory,
        private readonly PriceCalculatorService $priceCalculator,
    ) {
    }

    /**
     * Проводит покупку: рассчитывает цену и выполняет платеж
     *
     * @return float итоговая сумма платежа
     * @throws \Exception если платеж не удался
     */
    public function purchase(
        int $productId,
        string $taxNumber,
        string $paymentProcessor,
        ?string $couponCode = null
    ): float {
        // Рассчитываем итоговую цену
        $finalPrice = $this->priceCalculator->calculatePrice($productId, $taxNumber, $couponCode);

        // Получаем процессор оплаты
        $processor = $this->processorFactory->getProcessor($paymentProcessor);

        // Проводим платеж
        $processor->process($finalPrice);

        return $finalPrice;
    }
}

