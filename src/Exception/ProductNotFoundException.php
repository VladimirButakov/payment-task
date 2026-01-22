<?php

declare(strict_types=1);

namespace App\Exception;

class ProductNotFoundException extends \InvalidArgumentException
{
    public function __construct(int $productId)
    {
        parent::__construct(sprintf('Product with ID %d not found', $productId));
    }
}

