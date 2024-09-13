<?php

declare(strict_types=1);

namespace App\Service\Utils;

class TimePeriodMatcher
{
    public const PERIODS_MAP = [
        'день' => 'day',
        'минута' => 'minute',
        'час' => 'hour'
    ];

    public static function fromNaturalToPhp(string $naturalPeriod): string
    {
        return self::PERIODS_MAP[$naturalPeriod] ?? 'day';
    }

    public static function fromPhpToNatural(string $phpPeriod): string
    {
        $key = array_search($phpPeriod, self::PERIODS_MAP);

        return $key === false ? 'день' : $key;
    }
}