<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Config>
 */
class ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Config::class);
    }

        public function findOneByPath(string $path): ?Config
        {
            return $this->createQueryBuilder('c')
                ->andWhere('c.path = :val')
                ->setParameter('val', $path)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }
}
