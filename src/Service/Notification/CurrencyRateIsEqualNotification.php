<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Service\Notification\SendingStrategy\SendingStrategyInterface;

class CurrencyRateIsEqualNotification extends AbstractCurrencyNotification
{
    public function __construct(
        private float $currencyRate,
        private string $currencyFrom,
        SendingStrategyInterface $sendingStrategy,
        private string $currencyTo = 'USDT'
    ) {
        parent::__construct($sendingStrategy);
    }

    protected function isMainLogicTrue(): bool
    {
        $last = $this->currencyRateRepository->getLast($this->currencyFrom, $this->currencyTo);
        $currentRate = (float) $last?->getRate();
        $isMainLogicTrue = $this->isFloatEqual($this->currencyRate, (float) $currentRate)
            || $this->isFloatGreaterThan($currentRate, $this->currencyRate);

        return $isMainLogicTrue;
    }

    public function getText(): string
    {
        $last = $this->currencyRateRepository->getLast($this->currencyFrom, $this->currencyTo);
        $currencyRate = $last?->getRate();

        return sprintf('Текущий курс %s = %s', $this->currencyFrom, $this->formatUsd($currencyRate));
    }

    public function __serialize(): array
    {
        $result = [
            'currencyFrom' => $this->currencyFrom,
            'currencyRate' => $this->currencyRate,
            'currencyTo' => $this->currencyTo
        ];

        return array_replace(parent::__serialize(), $result);
    }
}