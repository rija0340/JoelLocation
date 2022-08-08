<?php

namespace App\Repository;

use App\Entity\ConducteurOptionnelPrix;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConducteurOptionnelPrix|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConducteurOptionnelPrix|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConducteurOptionnelPrix[]    findAll()
 * @method ConducteurOptionnelPrix[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConducteurOptionnelPrixRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConducteurOptionnelPrix::class);
    }

    // /**
    //  * @return ConducteurOptionnelPrix[] Returns an array of ConducteurOptionnelPrix objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConducteurOptionnelPrix
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
