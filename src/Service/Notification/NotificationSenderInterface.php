<?php

declare(strict_types=1);

namespace App\Service\Notification;

interface NotificationSenderInterface
{
    public function isNeedSend(): bool;

    public function getText(): string;

    public function isFinished(): bool;

    public function updateNotificationData(): void;

    public function __serialize(): array;
}