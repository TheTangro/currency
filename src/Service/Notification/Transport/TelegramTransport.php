<?php

declare(strict_types=1);

namespace App\Service\Notification\Transport;

use App\Entity\NotificationChannel;
use App\Entity\NotificationHistoryEntry;
use App\Repository\NotificationRequestRepository;
use App\Service\ConfigManager;
use TelegramBot\Api\BotApi;

class TelegramTransport implements TransportInterface
{
    public function __construct(
        private readonly ConfigManager $configManager,
        private readonly NotificationRequestRepository $notificationRequestRepository
    ) {
    }

    public function send(NotificationChannel $channel, string $text): void
    {
        $chatId = $channel->getPayload()['chat_id'] ?? null;
        $bot = new BotApi($this->configManager->getTelegramBotApiToken());
        $notificationHistoryEntry = new NotificationHistoryEntry();
        $notificationHistoryEntry->setSentAt(new \DateTimeImmutable());
        $notificationRequest = $channel->getNotificationRequest();
        $notificationHistoryEntry->setNotificationRequest($channel->getNotificationRequest());
        $notificationRequest->getNotificationHistory()->add($notificationHistoryEntry);
        $this->notificationRequestRepository->save($notificationRequest);

        if ($chatId) {
            $bot->sendMessage(chatId: $chatId, text: $text);
        }
    }
}