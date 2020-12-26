<?php

namespace App\Repository;

use App\Entity\EtatReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EtatReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtatReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtatReservation[]    findAll()
 * @method EtatReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtatReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtatReservation::class);
    }

    // /**
    //  * @return EtatReservation[] Returns an array of EtatReservation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EtatReservation
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
