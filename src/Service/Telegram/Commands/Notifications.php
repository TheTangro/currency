<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class Notifications extends AbstractSimpleCommand
{
    public function process(Message $message): string
    {
        return 'Choose a command please';
    }

    public function getKeyboard(): ?ReplyKeyboardMarkup
    {
        return new ReplyKeyboardMarkup(
            [
                [
                    '/stop_last_notification',
                    '/notification_request_examples'
                ]
            ],
            true,
            true
        );
    }
}