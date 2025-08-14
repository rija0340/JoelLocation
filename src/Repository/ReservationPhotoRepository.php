<?php

namespace App\Repository;

use App\Entity\ReservationPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReservationPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReservationPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReservationPhoto[]    findAll()
 * @method ReservationPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationPhoto::class);
    }

    // /**
    //  * @return ReservationPhoto[] Returns an array of Agence objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Agence
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
