<?php

declare(strict_types=1);

namespace App\Controller\Input;

class PurchaseRequest
{
    public function __construct(
        public readonly int $product,

        public readonly string $taxNumber,

        public readonly string $paymentProcessor,

        public readonly ?string $couponCode = null,
    ) {
    }

    public function getCountryCode(): string
    {
        return strtoupper(substr($this->taxNumber, 0, 2));
    }
}
