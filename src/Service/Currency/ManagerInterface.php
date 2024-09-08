<?php

declare(strict_types=1);

namespace App\Service\Currency;

use App\Entity\CurrencyRate;

interface ManagerInterface
{
    public function writeNewCurrencyRate(string $currencyFrom, string $currencyTo, float $currency): CurrencyRate;

    public function writeNewCurrencyRateAsObject(CurrencyRate $currencyRate): CurrencyRate;
}