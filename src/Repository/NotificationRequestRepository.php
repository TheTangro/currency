<?php

namespace App\Repository;

use App\Entity\NotificationRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationRequest>
 */
class NotificationRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationRequest::class);
    }

    public function save(NotificationRequest $notificationRequest): void
    {
        $entityManager = $this->getEntityManager();

        foreach ($notificationRequest->getNotificationChannels() as $notificationChannel) {
            $notificationChannel->setNotificationRequest($notificationRequest);
        }

        $entityManager->persist($notificationRequest);
        $entityManager->flush();
    }

    public function findAllNonFinished(): array
    {
        return $this->findBy(['isFinished' => false]);
    }
}
