<?php

namespace App\Repository;

use App\Entity\Vehicule;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Vehicule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicule[]    findAll()
 * @method Vehicule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    /**
     * @return Vehicule[] Returns an array of Reservation objects
     */
    public function findSomething($date)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.date_mise_service = :date')
            ->setParameter('date', $date)
            ->orderBy('v.date_mise_service', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /***
     * @return Vehicule[]
     * 
     */

    public function findVehiculeInvolved($dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('v')
            ->where(' veh_reser.date_fin > :dateDebut AND veh_reser.date_debut < :dateDebut')
            ->orWhere('veh_reser.date_fin > :dateFin AND veh_reser.date_debut < :dateFin')
            ->orWhere('veh_reser.date_debut < :dateDebut AND  :dateFin < veh_reser.date_fin ')
            ->orWhere('veh_reser.date_debut > :dateDebut AND  :dateFin > veh_reser.date_fin ')
            ->leftJoin('v.reservations', 'veh_reser')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('v.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /***
     * @return Vehicule[]
     * 
     */

    public function findVehiculeDispoBetween($dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('v')
            ->where('   :dateFin < veh_reser.date_debut  AND :dateDebut < veh_reser.date_debut ')
            ->orWhere('veh_reser.date_fin < :dateDebut AND veh_reser.date_debut < :dateDebut ')
            ->leftJoin('v.reservations', 'veh_reser')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('v.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Vehicule[] Returns an array of Vehicule objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vehicule
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
