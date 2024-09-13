<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use TelegramBot\Api\Types\Message;

class SendCouldNotRecognizeCommand extends AbstractSimpleCommand
{
    public function process(Message $message): string
    {
        return 'Could not recognize command';
    }
}