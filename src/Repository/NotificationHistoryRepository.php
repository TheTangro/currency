<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\NotificationHistoryEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationHistoryEntry::class);
    }

    public function getLast(): ?NotificationHistoryEntry
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.sentAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}