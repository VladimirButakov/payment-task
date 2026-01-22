<?php

namespace App\Service;

use App\Entity\Coupon;
use App\Service\Dto\PriceCalculationData;

interface PriceCalculatorInterface
{
    /**
     * Делитель для перевода процентов в доли (например, 19% => 19/100).
     */
    public const PERCENT_DIVISOR = 100.0;

    /**
     * Количество знаков после запятой для денежного значения.
     */
    public const MONEY_SCALE = 2;

    /**
     * Минимально допустимая цена (после скидок не должна уходить в минус).
     */
    public const MIN_PRICE = 0.0;

    /**
     * Базовый множитель при расчёте налога: price * (1 + rate).
     */
    public const BASE_MULTIPLIER = 1.0;

    public const TAX_NUMBER_MIN_LENGTH = 2;

    public function calculate(PriceCalculationData $data): float;
}