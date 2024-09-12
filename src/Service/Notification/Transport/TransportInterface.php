<?php

declare(strict_types=1);

namespace App\Service\Notification\Transport;

use App\Entity\NotificationChannel;

interface TransportInterface
{
    public function send(NotificationChannel $channel, string $text): void;
}