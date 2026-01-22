<?php

declare(strict_types=1);

namespace App\Controller\Input;

use App\Validator\ValidTaxNumber;
use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Product ID is required')]
        #[Assert\Type(type: 'integer', message: 'Product ID must be an integer')]
        #[Assert\Positive(message: 'Product ID must be positive')]
        public readonly int $product,

        #[Assert\NotBlank(message: 'Tax number is required')]
        #[Assert\Type(type: 'string', message: 'Tax number must be a string')]
        #[ValidTaxNumber]
        public readonly string $taxNumber,

        #[Assert\NotBlank(message: 'Payment processor is required')]
        #[Assert\Choice(
            choices: ['paypal', 'stripe'],
            message: 'Payment processor must be either "paypal" or "stripe"'
        )]
        public readonly string $paymentProcessor,

        #[Assert\Type(type: 'string', message: 'Coupon code must be a string')]
        public readonly ?string $couponCode = null,
    ) {
    }

    public function getCountryCode(): string
    {
        return strtoupper(substr($this->taxNumber, 0, 2));
    }
}
