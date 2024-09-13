<?php

declare(strict_types=1);

namespace App\Service\Telegram\NaturalLanguage;

use App\Service\Notification\CurrencyRateIsEqualNotification;
use App\Service\Telegram\Commands\CommandInterface;
use App\Service\Telegram\Commands\SendCouldNotRecognizeCommand;
use App\Service\Telegram\Commands\SendOk;
use TelegramBot\Api\Types\Message;

class CurrencyRateIsEqualAtNotification extends AbstractNaturalLanguage
{
    public const MAIN_EXP = '/[Нн]апомни мне.*?когда\s([A-Z]+) будет стоить\s([0-9.,]+)\./m';

    public function isSupported(string $phrase): bool
    {
        return (bool) preg_match(self::MAIN_EXP, $phrase);
    }

    public function process(string $phrase, Message $message): ?CommandInterface
    {
        preg_match(self::MAIN_EXP, $phrase, $matches);

        if (count($matches) === 3) {
            $currencyFrom = $matches[1];
            $currencyRate = (float) $matches[2];
            $simpleCurrencyRateIsEqual = new CurrencyRateIsEqualNotification(
                $currencyRate,
                $currencyFrom,
                $this->generateNotificationStrategy($phrase)
            );
            $this->persistNotificationProcessor($simpleCurrencyRateIsEqual);

            return new SendOk;
        }

        return new SendCouldNotRecognizeCommand;
    }
}