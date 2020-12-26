<?php

namespace App\Repository;

use App\Entity\ModeReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ModeReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModeReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModeReservation[]    findAll()
 * @method ModeReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModeReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModeReservation::class);
    }

    // /**
    //  * @return ModeReservation[] Returns an array of ModeReservation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ModeReservation
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
