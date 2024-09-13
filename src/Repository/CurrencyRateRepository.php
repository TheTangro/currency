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
            ->setParameter('ct', $currencyTo)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getRowsCount(string $currencyFrom, string $currencyTo): int
    {
        $qb = $this->createQueryBuilder('c');
        $qb->andWhere('c.currency_from = :cf')
            ->setParameter('cf', $currencyFrom)
            ->andWhere('c.currency_to = :ct')
            ->setParameter('ct', $currencyTo);
        $qb->select('COUNT(c.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function getAllRowsCount(): int
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('COUNT(c.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function getAverageRateFromDate(
        \DateTimeInterface $dateTime,
        string $currencyFrom,
        string $currencyTo
    ): ?float {
        $qb = $this->createQueryBuilder('cr')
            ->select('AVG(cr.rate) as avg_rate')
            ->where('cr.created_at >= :dateTime')
            ->andWhere('cr.currency_from = :currencyFrom')
            ->andWhere('cr.currency_to = :currencyTo')
            ->setParameter('dateTime', $dateTime)
            ->setParameter('currencyFrom', $currencyFrom)
            ->setParameter('currencyTo', $currencyTo);

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result !== null ? (float) $result : null;
    }
}
