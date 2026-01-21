<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validator\ValidTaxNumber;
use Symfony\Component\Validator\Constraints as Assert;

class CalculatePriceRequest
{
    #[Assert\NotBlank(message: 'Product ID is required')]
    #[Assert\Type(type: 'integer', message: 'Product ID must be an integer')]
    #[Assert\Positive(message: 'Product ID must be positive')]
    public int $product;

    #[Assert\NotBlank(message: 'Tax number is required')]
    #[Assert\Type(type: 'string', message: 'Tax number must be a string')]
    #[ValidTaxNumber]
    public string $taxNumber;

    #[Assert\Type(type: 'string', message: 'Coupon code must be a string')]
    public ?string $couponCode = null;
}

