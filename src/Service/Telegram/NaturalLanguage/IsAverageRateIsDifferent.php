<?php

declare(strict_types=1);

namespace App\Service\Telegram\NaturalLanguage;

use App\Service\Notification\AverageCurrencyRateIsNotification;
use App\Service\Telegram\Commands\CommandInterface;
use App\Service\Telegram\Commands\SendCouldNotRecognizeCommand;
use App\Service\Telegram\Commands\SendOk;
use App\Service\Utils\TimePeriodMatcher;
use TelegramBot\Api\Types\Message;

class IsAverageRateIsDifferent extends AbstractNaturalLanguage
{
    public const MAIN_EXPR = '/[Нн]апомни.*?когда\sцена\s([A-Z]+)\sбудет\s(больше|меньше)\sсредней\sза\s(\d+)\s(день|час|минут)\sна\s([0-9\.,]+)\%\./m';

    public function isSupported(string $phrase): bool
    {
        return (bool) preg_match(self::MAIN_EXPR, $phrase);
    }

    public function process(string $phrase, Message $message): ?CommandInterface
    {
        preg_match(self::MAIN_EXPR, $phrase, $matches);

        if (count($matches) === 6) {
            [,$currencyFrom, $type, $timeAmount, $timePeriod, $amountPercent] = $matches;

            $amountPercent = (float) $amountPercent;
            $amountPercent = $type === 'меньше' ? -$amountPercent : $amountPercent;
            $period = TimePeriodMatcher::fromNaturalToPhp($timePeriod);
            $notificationProcessor = new AverageCurrencyRateIsNotification(
                $currencyFrom,
                $period,
                (int) $timeAmount,
                $amountPercent,
                $this->generateNotificationStrategy($phrase)
            );
            $this->persistNotificationProcessor($notificationProcessor, $message);

            return new SendOk;
        }

        return new SendCouldNotRecognizeCommand;
    }
}