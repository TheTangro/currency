<?php

declare(strict_types=1);

namespace App\Service\Notification\SendingStrategy;

interface SendingStrategyInterface
{
    public function getRepeatsAmount(): int;

    public function getFrequency(): int;
}