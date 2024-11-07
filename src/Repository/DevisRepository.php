<?php

namespace App\Repository;

use App\Entity\Devis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Devis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Devis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Devis[]    findAll()
 * @method Devis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DevisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Devis::class);
    }


    /**
     * @return Devis[] Returns an array of Devis objects
     */

    public function findDevisTransformes()
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.transformed = :val')
            ->setParameter('val', true)
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findByHashedId(string $hashedId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT r.*
                FROM devis r
                WHERE SHA1(r.id) = :hashedId';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['hashedId' => $hashedId]);

        if (!$result->rowCount()) {
            return null;
        }

        $reservationData = $result->fetchAssociative();
        if (!$reservationData) {
            return null;
        }

        return $this->getEntityManager()
            ->find('App\Entity\Devis', $reservationData['id']);
    }


    // /**
    //  * @return Devis[] Returns an array of Devis objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Devis
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
