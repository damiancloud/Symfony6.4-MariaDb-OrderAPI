<?php

namespace App\Service\Configuration;

class VatConfig
{
    public const VAT_RATE = 0.23;

    public function getVatRate(): float
    {
        return self::VAT_RATE;
    }
}
