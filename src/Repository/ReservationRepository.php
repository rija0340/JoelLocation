<?php

namespace App\Repository;

use DateTime;
use DateTimeZone;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    private $userRepo;
    private $dateHelper;
    public function __construct(ManagerRegistry $registry, UserRepository $userRepo, DateHelper $dateHelper)
    {

        $this->userRepo = $userRepo;
        $this->dateHelper = $dateHelper;

        parent::__construct($registry, Reservation::class);
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findResasNonSoldes()
    {

        // find all reservations whose have not been payed completely
        $nouvelleResas =  $this->findNouvelleReservations();
        $resasNonSoldes = [];
        foreach ($nouvelleResas as $resa) {
            if ($resa->getSommePaiements() < $resa->getPrix()) {
                array_push($resasNonSoldes, $resa);
            }
        }
        return $resasNonSoldes;
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findNouvelleReservations()
    {
        return $this->createQueryBuilder('r')
            ->where(' :dateNow < r.date_debut')
            ->andWhere('r.code_reservation = :code')
            ->andWhere('r.canceled = FALSE AND r.archived = FALSE AND r.reported = FALSE')
            ->setParameter('dateNow', $this->dateHelper->dateNow())
            ->setParameter('code', 'devisTransformé')
            ->orderBy('r.date_fin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     * Reservation not canceled, not arcihide
     */
    public function findResasPlanGen()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.code_reservation = :code')
            ->orWhere('r.code_reservation = :code2')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")
            ->setParameter('code', 'devisTransformé')
            ->setParameter('code2', 'stopSale')
            ->orderBy('r.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationsByMarqueAndModele($modele)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.code_reservation = :code')
            ->andWhere('r.vehicule.modele = :modele')
            ->andWhere('r.canceled = FALSE AND r.archived = FALSE AND r.reported = FALSE')
            ->setParameter('modele', $modele)
            ->setParameter('code', 'devisTransformé')
            ->orderBy('r.date_fin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReportedReservations()
    {
        return $this->createQueryBuilder('r')
            ->where('r.code_reservation = :code')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL AND r.reported = TRUE")
            ->setParameter('code', 'devisTransformé')
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationEffectuers($client, $date)
    {
        return $this->createQueryBuilder('r')
            ->where('r.client = :client AND r.date_fin < :date')
            ->andWhere('r.code_reservation = :code')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")

            ->setParameter('client', $client)
            ->setParameter('date', $date)
            ->setParameter('code', 'devisTransformé')
            ->orderBy('r.date_fin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationsTermines()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.date_fin < :date')
            ->andWhere('r.code_reservation = :code')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")

            ->setParameter('date', $this->dateHelper->dateNow())
            ->setParameter('code', 'devisTransformé')
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
            ->andWhere('r.code_reservation = :code')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")

            ->setParameter('client', $client)
            ->setParameter('date', $date)
            ->setParameter('code', 'devisTransformé')
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
            ->andWhere('r.code_reservation = :code')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")

            ->setParameter('client', $client)
            ->setParameter('date', $date)
            ->setParameter('code', 'devisTransformé')
            ->orderBy('r.date_debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationsAttenteDateDebut($client)
    {

        return $this->createQueryBuilder('r')
            ->andWhere('r.client = :client AND r.date_debut > :date')
            ->andWhere("r.code_reservation = 'devisTransformé' OR r.code_reservation = 'reservationDirect'")
            // ->andWhere("r.code_reservation = :code")
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")
            ->setParameter('date', $this->dateHelper->dateNow())
            // ->setParameter('code', 'reservationDirect')
            ->setParameter('client', $client)
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationIncludeDate($date)
    {
        return $this->createQueryBuilder('r')
            ->where(' r.date_fin >= :date')
            ->andWhere('r.date_debut <= :date')
            ->andWhere('r.code_reservation = :code')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")

            ->setParameter('code', 'devisTransformé')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationAndStopSalesIncludeDate($date)
    {
        return $this->createQueryBuilder('r')
            ->where(' r.date_fin >= :date')
            ->andWhere('r.date_debut <= :date')
            ->andWhere('r.code_reservation = :code OR r.code_reservation = :stopsale')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")

            ->setParameter('code', 'devisTransformé')
            ->setParameter('stopsale', 'stopSale')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }
    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationExcludeDate($date)
    {
        return $this->createQueryBuilder('r')
            ->where(' r.date_fin < :date')
            ->orWhere('r.date_debut > :date')
            ->andWhere('r.code_reservation = :code')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")

            ->setParameter('code', 'devisTransformé')
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
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE OR r.archived IS NULL")
            ->setParameter('code', 'stopSale')
            ->getQuery()
            ->getResult();
    }

    //tous les reservations qui doivent être dans le planning
    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationsSansStopSales()
    {
        return $this->createQueryBuilder('r')
            ->where(' r.code_reservation != :code ')
            ->setParameter('code', 'stopSale')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE AND r.reported = FALSE OR r.archived IS NULL")
            // ->setParameter('date', $this->dateHelper->dateNow())
            ->orderBy('r.date_reservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationsSansStopSalesBetweenDates($dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('r')
            ->where(' r.code_reservation != :code ')
            ->andWhere(' r.date_debut > :dateDebut AND r.date_fin < :dateFin ')
            ->setParameter('code', 'stopSale')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('code', 'stopSale')
            ->andWhere("r.canceled = FALSE OR r.canceled IS NULL AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")
            // ->setParameter('date', $this->dateHelper->dateNow())
            ->orderBy('r.date_reservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     * la date de début ou la date de retour est inclus dans l'intervalle de recheche
     */
    public function findByOneORTwoDatesIncludedBetween($dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('r')
            ->where('  :dateDebut < r.date_debut AND r.date_debut < :dateFin AND r.date_fin > :dateFin AND r.code_reservation != :code ')
            ->orWhere('   r.date_debut < :dateDebut AND r.date_debut < :dateFin  AND  r.date_fin< :dateFin AND r.code_reservation != :code ')
            ->orWhere('  :dateDebut < r.date_debut AND r.date_fin < :dateFin AND r.code_reservation != :code ')
            ->setParameter('code', 'stopSale')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")
            // ->setParameter('date', $this->dateHelper->dateNow())
            ->orderBy('r.date_reservation', 'DESC')
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return Reservation[] Returns an array of Reservation objects
    //  */
    // public function findReservationIncludeDates($dateDebut, $dateFin)
    // {
    //     return $this->createQueryBuilder('r')
    //         // ->where(' r.date_debut < :dateDebut AND r.date_fin > :dateDebut AND r.date_debut < :dateFin AND  r.date_fin < :dateFin ')
    //         // ->orWhere(' r.date_debut > :dateDebut AND r.date_fin > :dateDebut AND r.date_debut < :dateFin AND  r.date_fin < :dateFin ')
    //         // ->orWhere(' r.date_debut > :dateDebut AND r.date_fin < :dateDebut AND r.date_debut > :dateFin AND  r.date_fin < :dateFin ')
    //         // ->orWhere(' r.date_debut < :dateDebut AND r.date_fin > :dateDebut AND r.date_debut < :dateFin AND  r.date_fin > :dateFin ')
    //         ->where("r.date_debut > :dateDebut AND r.date_debut < :dateFin AND r.date_fin > :dateFin AND r.date_fin > :dateDebut")
    //         ->orWhere("r.date_debut < :dateDebut AND r.date_debut < :dateFin AND r.date_fin > :dateDebut AND r.date_fin > :dateFin")
    //         ->orWhere("r.date_debut < :dateDebut AND r.date_debut < :dateFin AND r.date_fin > :dateDebut AND r.date_fin < :dateFin ")
    //         ->orWhere("r.date_debut > :dateDebut AND r.date_debut < :dateFin AND r.date_fin > :dateDebut AND r.date_fin < :dateFin ")
    //         ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL AND r.type IS NULL ")

    //         // ->andWhere(' r.date_fin > :dateFin AND r.date_debut < :dateFin')
    //         ->setParameter('dateDebut', $dateDebut)
    //         ->setParameter('dateFin', $dateFin)
    //         ->getQuery()
    //         ->getResult();
    // }

    /**
     * Cette fonction return les reservation qui peuvent être inclu dans ces date 
     * comparaison de la date debut parametre si inclus dans les dates debut et fin d'une reservation existantes 
     * meme chose pour la date fin du parametre 
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationIncludeDates($dateDebut, $dateFin, $vehicule = null)
    {
        $qb =  $this->createQueryBuilder('r')
            ->where("(r.date_debut BETWEEN :dateDebut AND :dateFin) OR (r.date_fin BETWEEN :dateDebut AND :dateFin) OR (r.date_debut <= :dateDebut AND r.date_fin >= :dateFin)")
            ->andWhere("(r.canceled = FALSE OR r.canceled IS NULL)")
            ->andWhere("r.archived = FALSE")
            ->andWhere("(r.reported = FALSE OR r.reported IS NULL)")
            ->andWhere("r.type IS NULL")
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);
        if ($vehicule !== null) {
            $qb->andWhere('r.vehicule = :vehicule')
                ->setParameter('vehicule', $vehicule);
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationExludeDates($dateDebut, $dateFin)

    {
        return $this->createQueryBuilder('r')
            ->where('   :dateFin < r.date_debut  AND :dateDebut < r.date_debut ')
            ->orWhere('r.date_fin < :dateDebut AND r.date_debut < :dateDebut ')
            ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

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
            ->andWhere(' r.code_reservation != :code')
            ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

            ->setParameter('vehicule', $vehicule)
            ->setParameter('code', 'stopSale')
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
            ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

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
            ->andWhere(' r.code_reservation != :code')
            ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

            ->setParameter('vehicule', $vehicule)
            ->setParameter('code', 'stopSale')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findByName($keyword)
    {
        return $this->createQueryBuilder('r')
            ->andWhere(' r.client.nom LIKE :keyword')
            ->setParameter('keyword', $keyword)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findPlanningJournaliers($date)
    {
        $date = $date->format('Y-m-d');
        return $this->createQueryBuilder('r')
            ->where("DATE_FORMAT(r.date_debut,'%Y-%m-%d') = :date AND r.code_reservation != :code ")
            ->orWhere("DATE_FORMAT(r.date_fin,'%Y-%m-%d') = :date AND r.code_reservation != :code")
            ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

            ->setParameter('date', $date)
            ->setParameter('code', 'stopSale')
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findRechercheSimple($client_nom)
    {
        return $this->createQueryBuilder('r')
            ->where("r.code_reservation != :code")
            ->andWhere(' r.client.nom LIKE :keyword OR r.client.prenom LIKE :keyword')
            ->setParameter('code', 'stopSale')
            ->getQuery()
            ->getResult();
    }
    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findRechercheIM($vehicule, $date)
    {
        return $this->createQueryBuilder('r')
            ->where(' r.date_fin > :date AND r.date_debut < :date')
            ->andWhere("r.vehicule = :vehicule")
            ->setParameter('date', $date)
            ->setParameter('vehicule', $vehicule)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findDateDepartIncludedBetwn($debutPeriode, $finPeriode, $vehicule, $typeTarif)
    {

        $superAdmin = $this->userRepo->findSuperAdmin();

        if ($vehicule == null && $typeTarif == null) {
            return $this->createQueryBuilder('r')
                ->where(' :debutPeriode < r.date_debut AND r.date_debut < :finPeriode AND r.code_reservation != :code')
                ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

                ->setParameter('debutPeriode', $debutPeriode)
                ->setParameter('finPeriode', $finPeriode)
                // ->setParameter('client', $superAdmin)
                ->setParameter('code', "stopSale")
                ->getQuery()
                ->getResult();
        } else {
            if ($vehicule != null && $typeTarif == null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_debut AND r.date_debut < :finPeriode AND r.vehicule = :vehicule AND r.code_reservation != :code')
                    ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('vehicule', $vehicule)
                    ->setParameter('code', "stopSale")
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
            if ($vehicule == null && $typeTarif != null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_debut AND r.date_debut < :finPeriode AND r.reference LIKE :typeTarif AND r.code_reservation != :code')
                    ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('typeTarif', $typeTarif)
                    ->setParameter('code', 'stopSale')
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
            if ($vehicule != null && $typeTarif != null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_debut AND r.date_debut < :finPeriode AND r.reference LIKE :typeTarif AND r.vehicule = :vehicule AND r.code_reservation != :code')
                    ->andWhere("r.canceled = FALSE OR  r.canceled IS NULL  AND r.archived = FALSE AND r.reported = FALSE OR r.reported IS NULL")

                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('typeTarif', $typeTarif)
                    ->setParameter('vehicule', $vehicule)
                    ->setParameter('code', 'stopSale')
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
        }
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findDateRetourIncludedBetwn($debutPeriode, $finPeriode, $vehicule, $typeTarif)
    {
        $superAdmin = $this->userRepo->findSuperAdmin();

        if ($vehicule == null && $typeTarif == null) {
            return $this->createQueryBuilder('r')
                ->where(' :debutPeriode < r.date_fin AND r.date_fin < :finPeriode AND r.code_reservation != :code')
                ->setParameter('debutPeriode', $debutPeriode)
                ->setParameter('finPeriode', $finPeriode)
                ->setParameter('code', 'stopSale')
                // ->setParameter('client', $superAdmin)
                ->getQuery()
                ->getResult();
        } else {
            if ($vehicule != null && $typeTarif == null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_fin AND r.date_fin < :finPeriode AND r.vehicule = :vehicule ANDr.code_reservation != :code')
                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('vehicule', $vehicule)
                    ->setParameter('code', 'stopSale')
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
            if ($vehicule == null && $typeTarif != null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_fin AND r.date_fin < :finPeriode AND r.reference LIKE :typeTarif AND r.code_reservation != :code')
                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('typeTarif', $typeTarif)
                    ->setParameter('code', 'stopSale')
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
            if ($vehicule != null && $typeTarif != null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_fin AND r.date_fin < :finPeriode AND r.reference LIKE :typeTarif AND r.vehicule = :vehicule AND r.code_reservation != :code')
                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('typeTarif', $typeTarif)
                    ->setParameter('vehicule', $vehicule)
                    ->setParameter('code', 'stopSale')
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
        }
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findDateResIncludedBetwn($debutPeriode, $finPeriode, $vehicule, $typeTarif)
    {
        $superAdmin = $this->userRepo->findSuperAdmin();

        if ($typeTarif == 'WEB') {
            $typeTarif = 'WEB%';
        } else {
            $typeTarif = 'CPT%';
        }

        if ($vehicule == null && $typeTarif == null) {
            return $this->createQueryBuilder('r')
                ->where(' :debutPeriode < r.date_reservation AND r.date_reservation < :finPeriode AND r.code_reservation != :code')
                ->setParameter('debutPeriode', $debutPeriode)
                ->setParameter('finPeriode', $finPeriode)
                ->setParameter('code', 'stopSale')
                // ->setParameter('client', $superAdmin)
                ->getQuery()
                ->getResult();
        } else {
            if ($vehicule != null && $typeTarif == null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_reservation AND r.date_reservation < :finPeriode AND r.vehicule = :vehicule AND r.code_reservation != :code')
                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('vehicule', $vehicule)
                    ->setParameter('code', 'stopSale')
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
            if ($vehicule == null && $typeTarif != null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_reservation AND r.date_reservation < :finPeriode AND r.reference LIKE :typeTarif AND r.code_reservation != :code')
                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('typeTarif', $typeTarif)
                    ->setParameter('code', 'stopSale')
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
            if ($vehicule != null && $typeTarif != null) {
                return $this->createQueryBuilder('r')
                    ->where(' :debutPeriode < r.date_reservation AND r.date_reservation < :finPeriode AND r.reference LIKE :typeTarif AND r.vehicule = :vehicule AND r.code_reservation != :code')
                    ->setParameter('debutPeriode', $debutPeriode)
                    ->setParameter('finPeriode', $finPeriode)
                    ->setParameter('typeTarif', $typeTarif)
                    ->setParameter('vehicule', $vehicule)
                    ->setParameter('code', 'stopSale')
                    // ->setParameter('client', $superAdmin)
                    ->getQuery()
                    ->getResult();
            }
        }
    }


    /**
     * @return Reservation[] Returns an array of Reservation objects
     */

    public function findAppelPaiement()
    {
        $reservations =  $this->findReservationsSansStopSales();
        $array = [];
        foreach ($reservations as $reservation) {
            if ($reservation->getPrix() > $reservation->getSommePaiements()) {
                array_push($array, $reservation);
            }
        }
        return $array;
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
