<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Kernel;
use App\Repository\CurrencyRateRepository;
use App\Service\Notification\SendingStrategy\SendingStrategyInterface;
use DateTime;
use DateTimeZone;

class SimpleProfitNotification extends AbstractCurrencyNotification
{
    private const EPSILON = 0.00001;

    private array $sendTimeStamps = [];
    private CurrencyRateRepository|null $currencyRateRepository = null;

    private \DateTimeInterface|null $nextAllowedSending = null;

    public function __construct(
        private readonly string $currencyFrom,
        private readonly float $tradeAmount,
        private readonly float $dealCost,
        private readonly float $profitAmount,
        private readonly SendingStrategyInterface $sendingStrategy,
        private readonly string $currencyTo = 'USDT',
    ) {
    }

    public function isNeedSend(): bool
    {
        $result = false;

        if ($this->currencyRateRepository && $this->validateSendingStrategy()) {
            $last = $this->currencyRateRepository->getLast(
                $this->currencyFrom,
                $this->currencyTo,
            );

            if ($last) {
                $cryptoCurrencyAmount = $this->tradeAmount / $this->dealCost;
                $newFiatCost = $cryptoCurrencyAmount * $last->getRate();
                $profit = $newFiatCost - $this->tradeAmount;
                $profit = round($profit, 2);
                $targetProfit = round($this->profitAmount, 2);

                return $this->isFloatEqual($profit, $targetProfit) || $this->isFloatGreaterThan($profit, $targetProfit);
            }
        }

        return $result;
    }

    public function validateSendingStrategy(): bool
    {
        if (empty($this->sendTimeStamps)) {
            return true;
        } else {
            if (count($this->sendTimeStamps) >= $this->sendingStrategy->getRepeatsAmount()) {
                return false;
            } elseif ($this->nextAllowedSending !== null) {
                $now = new DateTime();
                $now->setTimezone(new DateTimeZone('UTC'));

                return $now >= $this->nextAllowedSending;
            } else {
                $now = new DateTime();
                $now->setTimezone(new DateTimeZone('UTC'));
                $now->setTime((int) $now->format('H'), 0, 0);

                $lastMinuteSendings = array_filter(
                    $this->sendTimeStamps,
                    function (\DateTimeInterface $dateTime) use ($now) {
                        $dateTime = $dateTime->setTimezone(new \DateTimeZone('UTC'));

                        return $dateTime >= $now;
                    }
                );

                return count($lastMinuteSendings) < $this->sendingStrategy->getFrequency();
            }
        }
    }

    public function setCurrencyRateRepository(CurrencyRateRepository $currencyRateRepository): void
    {
        $this->currencyRateRepository = $currencyRateRepository;
    }

    public function getText(): string
    {
        return sprintf(
            'Ваша сделка на сумму %s (%s => %s) может быть закрыта с профитом = %s',
            $this->tradeAmount,
            $this->currencyFrom,
            $this->currencyTo,
            $this->profitAmount
        );
    }

    public function __serialize(): array
    {
        return [
            'currencyFrom' => $this->currencyFrom,
            'tradeAmount' => $this->tradeAmount,
            'dealCost' => $this->dealCost,
            'profitAmount' => $this->profitAmount,
            'currencyTo' => $this->currencyTo,
            'sendingStrategy' => $this->sendingStrategy,
            'sendTimeStamps' => $this->sendTimeStamps,
            'nextAllowedSending' => $this->nextAllowedSending
        ];
    }

    public function isFinished(): bool
    {
        return count($this->sendTimeStamps) >= $this->sendingStrategy->getRepeatsAmount();
    }

    public function updateNotificationData(): void
    {
        $this->sendTimeStamps[] = new \DateTimeImmutable();

        if (!$this->isFinished()) {
            $now = new DateTime();
            $secondsBetweenSending = ceil(60 / $this->sendingStrategy->getFrequency());
            $now->modify("+{$secondsBetweenSending} seconds");
            $this->nextAllowedSending = $now;
        }
    }

    private function isFloatEqual(float $a, float $b): bool
    {
        return abs($a - $b) <= self::EPSILON;
    }

    public function isFloatGreaterThan(float $a, float $b): bool
    {
        return ($a - $b) > self::EPSILON;
    }
}