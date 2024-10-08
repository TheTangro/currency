<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use TelegramBot\Api\Types\ReplyKeyboardMarkup;

abstract class AbstractSimpleCommand implements CommandInterface
{
    public function getParseMode(): ?string
    {
        return null;
    }

    public function getKeyboard(): ?ReplyKeyboardMarkup
    {
        return null;
    }
}