<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\CurrencyRate;
use App\Service\Notification\SendingStrategy\SendingStrategyInterface;
use App\Service\Utils\FloatUtils;

class CurrencyRateIsEqualNotification extends AbstractCurrencyNotification
{
    private ?CurrencyRate $lastRate = null;

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
        try {
            $currentRateObject = $this->currencyRateRepository->getLast($this->currencyFrom, $this->currencyTo);

            if (false === $currentRateObject instanceof CurrencyRate) {
                return false;
            } else {
                $currentRate = $currentRateObject?->getRate();
                $scale = FloatUtils::getScale($this->currencyRate);

                $result = bccomp((string) $this->currencyRate, (string) $currentRate, $scale) === 0;

                if (!$result && $this->lastRate !== null) {
                    $isPreviousIsLowerThanAndCurrentIsGreaterThanTarget =
                        bccomp($this->lastRate->getRate(), (string) $this->currencyRate, $scale) < 0
                        && bccomp($currentRate, (string) $this->currencyRate, $scale) > 0;
                    $isPreviousIsGreaterThanAndCurrentIsLowerThanTarget =
                        bccomp($this->lastRate->getRate(), (string) $this->currencyRate, $scale) > 0
                        && bccomp($currentRate, (string) $this->currencyRate, $scale) < 0;

                    $result = $isPreviousIsGreaterThanAndCurrentIsLowerThanTarget
                        || $isPreviousIsLowerThanAndCurrentIsGreaterThanTarget;
                }

                return $result;
            }
        } finally {
            $this->lastRate = $currentRateObject ?? null;
        }
    }

    public function getText(): string
    {
        $last = $this->currencyRateRepository->getLast($this->currencyFrom, $this->currencyTo);
        $currencyRate = $last?->getRate();

        return sprintf(
            'Текущий курс %s = %s. Целевой курс сравнения %s',
            $this->currencyFrom,
            $this->formatUsd($currencyRate),
            $this->currencyRate
        );
    }

    public function __serialize(): array
    {
        $result = [
            'currencyFrom' => $this->currencyFrom,
            'currencyRate' => $this->currencyRate,
            'currencyTo' => $this->currencyTo,
            'lastRate ' => $this->lastRate,
        ];

        return array_replace(parent::__serialize(), $result);
    }
}