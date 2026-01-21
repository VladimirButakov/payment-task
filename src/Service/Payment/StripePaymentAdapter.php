<?php

declare(strict_types=1);

namespace App\Service\Payment;

use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class StripePaymentAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private readonly StripePaymentProcessor $stripeProcessor
    ) {
    }

    public function process(float $amount): void
    {
        // Stripe принимает сумму в валюте и возвращает bool
        $result = $this->stripeProcessor->processPayment($amount);
        
        if (!$result) {
            throw new \Exception('Payment failed: amount is too low (minimum 100 EUR)');
        }
    }

    public function getName(): string
    {
        return 'stripe';
    }
}

