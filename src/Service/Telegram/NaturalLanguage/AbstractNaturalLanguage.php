<?php

declare(strict_types=1);

namespace App\Service\Telegram\NaturalLanguage;

use App\Api\NotificationChannelType;
use App\Entity\NotificationChannel;
use App\Entity\NotificationRequest;
use App\Repository\NotificationRequestRepository;
use App\Service\Notification\NotificationSenderInterface;
use TelegramBot\Api\Types\Message;

abstract class AbstractNaturalLanguage
{
    use RepeatableNotificationTrait;

    public function __construct(
        private readonly NotificationRequestRepository $notificationRequestRepository
    ) {
    }

    protected function persistNotificationProcessor(
        NotificationSenderInterface $notificationSender,
        Message $message
    ): void {
        $notificationRequest = new NotificationRequest();
        $notificationChannel = new NotificationChannel();
        $notificationChannel->setType(NotificationChannelType::TELEGRAM);
        $notificationChannel->setPayload([
            'chat_id' => $message->getChat()->getId(),
        ]);
        $notificationRequest->setNotification($notificationSender);
        $notificationRequest->getNotificationChannels()->add($notificationChannel);
        $this->notificationRequestRepository->save($notificationRequest);
    }
}