<?php

namespace App\Entity;

use App\Api\NotificationChannelType;
use App\Repository\NotificationChannelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: NotificationChannelRepository::class)]
class NotificationChannel
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'notification_channel_id', initialValue: 1)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private NotificationChannelType|null $type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $payloadSerialized = null;

    #[ManyToOne(
        targetEntity: NotificationRequest::class,
        cascade: ['persist', 'remove', 'detach', 'refresh'],
        inversedBy: 'features')
    ]
    #[JoinColumn(name: 'notification_request_id', referencedColumnName: 'id')]
    private NotificationRequest|null $notificationRequest = null;

    public function getNotificationRequest(): ?NotificationRequest
    {
        return $this->notificationRequest;
    }

    public function setNotificationRequest(?NotificationRequest $notificationRequest): void
    {
        $this->notificationRequest = $notificationRequest;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): NotificationChannelType
    {
        return $this->type;
    }

    public function setType(NotificationChannelType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPayloadSerialized(): ?string
    {
        return $this->payloadSerialized;
    }

    public function setPayloadSerialized(string $payloadSerialized): static
    {
        $this->payloadSerialized = $payloadSerialized;

        return $this;
    }

    public function setPayload(array $payload): static
    {
        $this->setPayloadSerialized(json_encode($payload));

        return $this;
    }

    public function getPayload(): array
    {
        try {
            return json_decode($this->payloadSerialized, true);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
