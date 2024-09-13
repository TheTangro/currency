<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class SendOk extends AbstractSimpleCommand
{
    public function process(Message $message): string
    {
        return 'Your command has been accepted';
    }

    public function getKeyboard(): ?ReplyKeyboardMarkup
    {
        return null;
    }
}