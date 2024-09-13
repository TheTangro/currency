<?php

namespace App\Entity;

use App\Repository\NotificationHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: NotificationHistoryRepository::class)]
class NotificationHistoryEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'notification_history_entry_id', initialValue: 1)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sentAt = null;

    #[ManyToOne(
        targetEntity: NotificationRequest::class,
        cascade: ['persist', 'remove', 'detach', 'refresh'],
        inversedBy: 'notificationHistory'
    )]
    #[JoinColumn(name: 'notification_request_id', referencedColumnName: 'id')]
    private NotificationRequest|null $notificationRequest = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAT): static
    {
        $this->sentAt = $sentAT;

        return $this;
    }

    public function getNotificationRequest(): ?NotificationRequest
    {
        return $this->notificationRequest;
    }

    public function setNotificationRequest(?NotificationRequest $notificationRequest): void
    {
        $this->notificationRequest = $notificationRequest;
    }
}
