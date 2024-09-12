<?php

declare(strict_types=1);

namespace App\Service\Notification\Transport;

use App\Api\NotificationChannelType;
use App\Kernel;

class TransportFactory
{
    public function __construct(
        private readonly Kernel $kernel
    ) {
    }

    public function createTransport(NotificationChannelType $type): TransportInterface
    {
        return match ($type) {
            NotificationChannelType::TELEGRAM => clone $this->kernel->getContainer()->get(TelegramTransport::class),
            default => throw new \UnexpectedValueException(
                "Unsupported notification channel type: $type->value"
            ),
        };
    }
}