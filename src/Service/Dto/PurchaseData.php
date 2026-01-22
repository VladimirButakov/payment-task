<?php

declare(strict_types=1);

namespace App\Service\Dto;

/**
 * Service DTO для проведения покупки.
 * Чистый объект без HTTP-специфики.
 */
readonly class PurchaseData
{
    public function __construct(
        public int $productId,
        public string $taxNumber,
        public string $paymentProcessor,
        public ?string $couponCode = null,
    ) {
    }

    public function getCountryCode(): string
    {
        return strtoupper(substr($this->taxNumber, 0, 2));
    }
}

