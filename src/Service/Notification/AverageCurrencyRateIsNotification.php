<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Service\Notification\SendingStrategy\SendingStrategyInterface;
use App\Service\Utils\TimePeriodMatcher;

class AverageCurrencyRateIsNotification extends AbstractCurrencyNotification
{
    private float|null $calculatedPercent = null;

    public function __construct(
        private readonly string $currencyFrom,
        private readonly string $timePeriod,
        private readonly int $timeAmount,
        private readonly float $amountPercent,
        SendingStrategyInterface $sendingStrategy,
        private readonly string $currencyTo = 'USDT'
    ) {
        parent::__construct($sendingStrategy);
    }

    public function getText(): string
    {
        return sprintf(
            'На текущий момент цена %s %s среднего значения за %s %s на %s процента (ов)',
            $this->currencyFrom,
            $this->calculatedPercent > 0 ? 'выше' : 'ниже',
            $this->timeAmount,
            TimePeriodMatcher::fromPhpToNatural($this->timePeriod),
            number_format((float) $this->calculatedPercent, 4, '.', '')
        );
    }

    protected function isMainLogicTrue(): bool
    {
        $now = new \DateTimeImmutable();
        $dateBeforeNow = $now->modify(sprintf('-%d %s', $this->timeAmount, $this->timePeriod));
        $average = $this->currencyRateRepository->getAverageRateFromDate(
            $dateBeforeNow,
            $this->currencyFrom,
            $this->currencyTo
        );
        $last = $this->currencyRateRepository->getLast($this->currencyFrom, $this->currencyTo);

        if ($last && $average) {
            $lastCurrencyRate = (float) $last->getRate();
            $percent = ($lastCurrencyRate / $average) * 100;
            $percent = round($percent, 4);
            $percentDiff = $percent - 100;
            $this->calculatedPercent = $percentDiff;

            switch (true) {
                case $this->isFloatGreaterThan(0., $percentDiff)
                    && $this->isFloatGreaterThan(0., $this->amountPercent):
                case $this->isFloatGreaterThan($percentDiff, 0.)
                    && $this->isFloatGreaterThan($this->amountPercent, 0):

                    return $this->isFloatEqual(abs($percentDiff), abs($this->amountPercent))
                        || $this->isFloatGreaterThan(abs($percentDiff), abs($this->amountPercent));
                case $this->isFloatGreaterThan($this->amountPercent, 0.)
                    && $this->isFloatGreaterThan(0., $percentDiff):
                case $this->isFloatGreaterThan(0., $this->amountPercent)
                    && $this->isFloatGreaterThan($percentDiff, 0.):

                    return false;
            }
        }

        return false;
    }

    public function __serialize(): array
    {
        $result = [
            'currencyFrom' => $this->currencyFrom,
            'timePeriod' => $this->timePeriod,
            'currencyTo' => $this->currencyTo,
            'amountPercent' => $this->amountPercent,
            'timeAmount' => $this->timeAmount,
        ];

        return array_replace(parent::__serialize(), $result);
    }
}