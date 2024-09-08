<?php

namespace App\Repository;

use App\Entity\PoisonPill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PoisonPill>
 */
class PoisonPillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PoisonPill::class);
    }

    public function getOrNull(): ?PoisonPill
    {
        $poisonPill = $this->findOneBy([]);

        return $poisonPill;
    }

    public function save(PoisonPill $poisonPill): void
    {
        $entityManager = $this->getEntityManager();
        $all = $this->findAll();
        array_walk($all, $entityManager->remove(...));
        $entityManager->persist($poisonPill);
        $entityManager->flush();
    }
}
