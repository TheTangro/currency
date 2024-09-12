<?php

namespace App\Entity;

use App\Repository\NotificationRequestRepository;
use App\Service\Notification\NotificationSenderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;

#[ORM\Entity(repositoryClass: NotificationRequestRepository::class)]
class NotificationRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'notification_request_id', initialValue: 1)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $notificationSerialized = null;

    #[ORM\Column]
    private bool $isFinished = false;

    #[OneToMany(
        targetEntity: NotificationChannel::class,
        mappedBy: 'notificationRequest',
        cascade: ['persist', 'remove', 'detach', 'refresh'],
        fetch: 'LAZY'
    )]
    private Collection|null $notificationChannels = null;

    public function getNotificationChannels(): Collection
    {
        if ($this->notificationChannels === null) {
            $this->notificationChannels = new ArrayCollection();
        }

        return $this->notificationChannels;
    }

    public function setNotificationChannels(Collection $notificationChannels): void
    {
        $this->notificationChannels = $notificationChannels;
    }

    public function getIsFinished(): ?bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(?bool $isFinished): void
    {
        $this->isFinished = $isFinished;
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

    public function setNotification(NotificationSenderInterface $notification): static
    {
        $this->setNotificationSerialized(base64_encode(serialize($notification)));

        return $this;
    }

    public function getNotification(): NotificationSenderInterface
    {
        return unserialize(base64_decode($this->getNotificationSerialized()));
    }

    public function getNotificationSerialized(): ?string
    {
        return $this->notificationSerialized;
    }

    public function setNotificationSerialized(string $notificationSerialized): static
    {
        $this->notificationSerialized = $notificationSerialized;

        return $this;
    }

    public function isFinished(): ?bool
    {
        return $this->isFinished;
    }

    public function setFinished(bool $is_finished): static
    {
        $this->isFinished = $is_finished;

        return $this;
    }
}
