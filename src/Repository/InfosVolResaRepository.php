<?php

namespace App\Repository;

use App\Entity\InfosVolResa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InfosVolResa|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfosVolResa|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfosVolResa[]    findAll()
 * @method InfosVolResa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfosVolResaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InfosVolResa::class);
    }

    // /**
    //  * @return InfosVolResa[] Returns an array of InfosVolResa objects
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
    public function findOneBySomeField($value): ?InfosVolResa
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
