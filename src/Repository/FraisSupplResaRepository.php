<?php

namespace App\Repository;

use App\Entity\FraisSupplResa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FraisSupplResa|null find($id, $lockMode = null, $lockVersion = null)
 * @method FraisSupplResa|null findOneBy(array $criteria, array $orderBy = null)
 * @method FraisSupplResa[]    findAll()
 * @method FraisSupplResa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FraisSupplResaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FraisSupplResa::class);
    }

    // /**
    //  * @return FraisSupplResa[] Returns an array of FraisSupplResa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FraisSupplResa
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
