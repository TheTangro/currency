<?php

declare(strict_types=1);

namespace App\Service\Telegram\Commands;

use App\Repository\NotificationHistoryRepository;
use App\Repository\NotificationRequestRepository;
use TelegramBot\Api\Types\Message;

class StopLastNotification extends AbstractSimpleCommand
{
    public function __construct(
        private readonly NotificationHistoryRepository $notificationHistoryRepository,
        private readonly NotificationRequestRepository $notificationRequestRepository
    ) {
    }

    public function process(Message $message): string
    {
        $lastHistoryEntry = $this->notificationHistoryRepository->getLast();

        if ($lastHistoryEntry === null) {
            return 'Any notification has been found';
        } else {
            $notificationRequest = $lastHistoryEntry->getNotificationRequest();
            $notificationRequest->setFinished(true);
            $this->notificationRequestRepository->save($notificationRequest);
        }

        return 'Your notification request has been stopped';
    }
}