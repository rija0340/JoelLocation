<?php

namespace App\Repository;

use App\Entity\Tarifs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tarifs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tarifs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tarifs[]    findAll()
 * @method Tarifs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarifsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarifs::class);
    }


    public function findTarifs($vehicule, $mois): ?Tarifs
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.vehicule = :vehicule AND r.mois = :mois')
            ->setParameter('vehicule', $vehicule)
            ->setParameter('mois', $mois)
            ->getQuery()
            ->getOneOrNullResult();
    }
    // /**
    //  * @return Tarifs[] Returns an array of Tarifs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tarifs
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
