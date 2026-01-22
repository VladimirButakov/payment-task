<?php

declare(strict_types=1);

namespace App\Exception;

class TaxNotFoundException extends \InvalidArgumentException
{
    public function __construct(string $countryCode)
    {
        parent::__construct(sprintf('Tax configuration not found for country %s', $countryCode));
    }
}

