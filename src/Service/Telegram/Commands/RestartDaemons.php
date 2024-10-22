<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use App\Service\PoisonPillManager;
use TelegramBot\Api\Types\Message;

class RestartDaemons extends AbstractSimpleCommand
{
    public function __construct(
        private readonly PoisonPillManager $poisonPillManager
    ) {
    }

    public function process(Message $message): string|array
    {
        $this->poisonPillManager->regeneratePoison();

        return 'Grabbers has been restarted';
    }
}
