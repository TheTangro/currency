<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Repository\CurrencyRateRepository;
use App\Service\Notification\SendingStrategy\SendingStrategyInterface;
use DateTime;
use DateTimeZone;

abstract class AbstractCurrencyNotification implements NotificationSenderInterface
{
    protected CurrencyRateRepository|null $currencyRateRepository = null;

    protected const EPSILON = 0.00001;

    protected array $sendTimeStamps = [];

    protected \DateTimeInterface|null $nextAllowedSending = null;

    public function __construct(
        protected SendingStrategyInterface $sendingStrategy
    ) {
    }

    public function isNeedSend(): bool
    {
        if ($this->currencyRateRepository === null || !$this->validateSendingStrategy()) {
            return false;
        } else {
            return $this->isMainLogicTrue();
        }
    }

    protected function isMainLogicTrue(): bool
    {
        return false;
    }

    public function setCurrencyRateRepository(CurrencyRateRepository $currencyRateRepository): void
    {
        $this->currencyRateRepository = $currencyRateRepository;
    }

    public function isFinished(): bool
    {
        return count($this->sendTimeStamps) >= $this->sendingStrategy->getRepeatsAmount();
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

    protected function isFloatEqual(float $a, float $b): bool
    {
        return abs($a - $b) <= self::EPSILON;
    }

    protected function isFloatGreaterThan(float $a, float $b): bool
    {
        return ($a - $b) > self::EPSILON;
    }

    public function __serialize(): array
    {
        return [
            'sendingStrategy' => $this->sendingStrategy,
            'sendTimeStamps' => $this->sendTimeStamps,
            'nextAllowedSending' => $this->nextAllowedSending
        ];
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

    protected function formatUsd($amount): string
    {
        return (string) round((float) $amount, 2);
    }
}