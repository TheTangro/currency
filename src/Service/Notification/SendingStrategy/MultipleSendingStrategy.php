<?php

declare(strict_types=1);

namespace App\Service\Notification\SendingStrategy;

class MultipleSendingStrategy implements SendingStrategyInterface
{
    public function __construct(
        private readonly int $repeatsAmount,
        private readonly int $frequency = PHP_INT_MAX
    ) {
    }

    public function getRepeatsAmount(): int
    {
        return $this->repeatsAmount;
    }

    public function getFrequency(): int
    {
        return $this->frequency;
    }
}