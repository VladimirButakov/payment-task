<?php

namespace App\Service;

use App\Service\Dto\PurchaseData;

interface PaymentInterface
{
    public function purchase(PurchaseData $data): float;
}