<?php

declare(strict_types=1);

namespace App\Service\Dto;

/**
 * Service DTO для расчета цены.
 * Чистый объект без HTTP-специфики.
 */
readonly class PriceCalculationData
{
    public function __construct(
        public int $productId,
        public string $taxNumber,
        public ?string $couponCode = null,
    ) {
    }

    public function getCountryCode(): string
    {
        return strtoupper(substr($this->taxNumber, 0, 2));
    }
}

