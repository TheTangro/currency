<?php

namespace App\Repository;

use App\Entity\CurrencyRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CurrencyRate>
 */
class CurrencyRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyRate::class);
    }

    public function save(CurrencyRate $currencyRate): void
    {
        $em = $this->getEntityManager();
        $em->persist($currencyRate);
        $em->flush();
    }

        public function getLast(string $currencyFrom, string $currencyTo): ?CurrencyRate
        {
            return $this->createQueryBuilder('c')
                ->andWhere('c.currency_from = :cf')
                ->setParameter('cf', $currencyFrom)
                ->andWhere('c.currency_to = :ct')
                ->setParameter('cf', $currencyTo)
                ->orderBy('c.id', 'DESC')
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }
}
