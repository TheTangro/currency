<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class NotificationRequestExamples extends AbstractHtmlCommand
{
    public const EXAMPLES = [
        'Напомни мне, когда ETH будет стоить 255. Напоминание повторять 5 раза с частотой 1 раза в минуте',
        'Я вошел в сделку по ETH на сумму 1000 баксов по цене 2352. 
        Напомни мне когда прибыль будет 17 баксов. Напоминание повторять 5 раза с частотой 1 раза в минуте',
        'Напомни мне когда цена ETH будет меньше средней за 2 минута на 1%. 
        Напоминание повторять 1 раза с частотой 1 раза в минуте'
    ];

    public function process(Message $message): string|array
    {
        $result = [];

        foreach (self::EXAMPLES as $example) {
            $result[] = sprintf('<code>%s</code>', trim($example));
        }

        return $result;
    }

    public function getKeyboard(): ?ReplyKeyboardMarkup
    {
        return null;
    }
}