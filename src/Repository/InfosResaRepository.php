<?php

namespace App\Repository;

use App\Entity\InfosResa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InfosResa|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfosResa|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfosResa[]    findAll()
 * @method InfosResa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfosResaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InfosResa::class);
    }

    // /**
    //  * @return InfosResa[] Returns an array of InfosResa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InfosResa
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
