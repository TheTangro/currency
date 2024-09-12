<?php

declare(strict_types=1);

namespace App\Service\Notification\Transport;

use App\Entity\NotificationChannel;
use App\Service\ConfigManager;
use TelegramBot\Api\BotApi;

class TelegramTransport implements TransportInterface
{
    public function __construct(
        private readonly ConfigManager $configManager
    ) {
    }

    public function send(NotificationChannel $channel, string $text): void
    {
        $chatId = $channel->getPayload()['chat_id'] ?? null;
        $bot = new BotApi($this->configManager->getTelegramBotApiToken());

        if ($chatId) {
            $bot->sendMessage(chatId: $chatId, text: $text);
        }
    }
}