<?php

declare(strict_types=1);

namespace App\Exception;

class CouponNotFoundException extends \InvalidArgumentException
{
    public function __construct(string $couponCode)
    {
        parent::__construct(sprintf('Coupon with code "%s" not found', $couponCode));
    }
}

