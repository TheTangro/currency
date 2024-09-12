<?php

namespace App\Api;

enum NotificationChannelType: string
{
    case TELEGRAM = 'TELEGRAM';

    public static function toString($enumValue): string
    {
        return match ($enumValue) {
            self::TELEGRAM => 'TELEGRAM',
        };
    }
}
