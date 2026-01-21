<?php

declare(strict_types=1);

namespace App\Service\Payment;

interface PaymentProcessorInterface
{
    /**
     * Проводит платеж
     *
     * @param float $amount сумма в евро
     * @throws \Exception если платеж не удался
     */
    public function process(float $amount): void;

    /**
     * Имя процессора
     */
    public function getName(): string;
}

