<?php

declare(strict_types=1);

namespace App\Service\Utils;

class FloatUtils
{
    public static function getScale(float $number): int
    {
        $numberAsString = (string) $number;
        $decimalPosition = strpos($numberAsString, '.');

        if ($decimalPosition === false) {
            return 0;
        }

        $numberAsString = rtrim($numberAsString, '0');

        return strlen($numberAsString) - $decimalPosition - 1;
    }
}