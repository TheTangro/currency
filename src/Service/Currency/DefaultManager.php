<?php

declare(strict_types=1);

namespace App\Service\Currency;

use App\Entity\CurrencyRate;
use App\Repository\CurrencyRateRepository;

class DefaultManager implements ManagerInterface
{
    public function __construct(
        private readonly CurrencyRateRepository $currencyRateRepository
    ) {
    }

    public function writeNewCurrencyRate(string $currencyFrom, string $currencyTo, float $currency): CurrencyRate
    {
        $newCurrency = new CurrencyRate();
        $newCurrency->setCreatedAt(new \DateTimeImmutable());
        $newCurrency->setCurrencyFrom($currencyFrom);
        $newCurrency->setCurrencyTo($currencyTo);
        $newCurrency->setRate((string) $currency);

        $this->writeNewCurrencyRateAsObject($newCurrency);

        return $newCurrency;
    }

    public function writeNewCurrencyRateAsObject(CurrencyRate $currencyRate): CurrencyRate
    {
        $this->currencyRateRepository->save($currencyRate);

        return $currencyRate;
    }
}