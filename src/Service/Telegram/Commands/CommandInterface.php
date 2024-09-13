<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

interface CommandInterface
{
    public function process(Message $message): string|array;

    public function getParseMode(): ?string;

    public function getKeyboard(): ?ReplyKeyboardMarkup;
}