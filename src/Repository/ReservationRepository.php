<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationEffectuers($client, $date)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.client = :client AND r.date_fin < :date')
            ->setParameter('client', $client)
            ->setParameter('date', $date)
            ->orderBy('r.date_fin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationEncours($client, $date)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.client = :client AND r.date_fin > :date AND r.date_debut < :date')
            ->setParameter('client', $client)
            ->setParameter('date', $date)
            ->orderBy('r.date_debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationEnAttente($client, $date)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.client = :client AND r.date_debut > :date')
            ->setParameter('client', $client)
            ->setParameter('date', $date)
            ->orderBy('r.date_debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationIncludeDate($date)
    {
        return $this->createQueryBuilder('r')
            ->andWhere(' r.date_fin > :date')
            ->andWhere('r.date_debut < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findStopSales()
    {
        return $this->createQueryBuilder('r')
            ->andWhere(' r.code_reservation = :code')
            ->setParameter('code', 'stopSale')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationIncludeDates($dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('r')
            ->where(' r.date_fin > :dateDebut AND r.date_debut < :dateDebut')
            ->orWhere('r.date_fin > :dateFin AND r.date_debut < :dateFin')
            ->orWhere('r.date_debut < :dateDebut AND  :dateFin < r.date_fin ')
            ->orWhere('r.date_debut > :dateDebut AND  :dateFin > r.date_fin ')
            // ->andWhere(' r.date_fin > :dateFin AND r.date_debut < :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationExludeDates($dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('r')
            ->where('   :dateFin < r.date_debut  AND :dateDebut < r.date_debut ')
            ->orWhere('r.date_fin < :dateDebut AND r.date_debut < :dateDebut ')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     * 
     */
    public function findLastReservations($vehicule, $date)
    {
        return $this->createQueryBuilder('r')
            ->andWhere(' r.vehicule = :vehicule')
            ->andWhere('r.date_fin < :date')
            ->setParameter('vehicule', $vehicule)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     * 
     */
    public function findLastReservationsV($vehicule)
    {
        return $this->createQueryBuilder('r')
            ->andWhere(' r.vehicule = :vehicule')
            ->setParameter('vehicule', $vehicule)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findNextReservations($vehicule, $date)
    {
        return $this->createQueryBuilder('r')
            ->andWhere(' r.vehicule = :vehicule')
            ->andWhere('r.date_debut > :date')
            ->setParameter('vehicule', $vehicule)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }




    // /**
    //  * @return Reservation[] Returns an array of Reservation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
