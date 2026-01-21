<?php

declare(strict_types=1);

namespace App\Service\Payment;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

class PaypalPaymentAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private readonly PaypalPaymentProcessor $paypalProcessor
    ) {
    }

    public function process(float $amount): void
    {
        // Paypal принимает сумму в центах (минимальных единицах)
        $amountInCents = (int) round($amount * 100);
        
        $this->paypalProcessor->pay($amountInCents);
    }

    public function getName(): string
    {
        return 'paypal';
    }
}

