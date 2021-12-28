<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;
use App\Service\DateHelper;
use App\Service\ReservationHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Timezone;

class ChiffreAffaireController extends AbstractController
{

    private $vehiculeRepo;
    private $reservationRepo;
    private $reservationHelper;
    private $dateHelper;
    public function __construct(DateHelper $dateHelper, ReservationRepository $reservationRepo, ReservationHelper $reservationHelper, VehiculeRepository $vehiculeRepo)
    {
        $this->vehiculeRepo = $vehiculeRepo;
        $this->reservationRepo = $reservationRepo;
        $this->reservationHelper = $reservationHelper;
        $this->dateHelper = $dateHelper;
    }
    /**
     * @Route("backoffice/chiffre-affaire", name="chiffre_affaire")
     */
    public function index(): Response
    {

        return $this->render('admin/chiffre_affaire/index.html.twig');
    }

    /**
     * @Route("/backoffice/chiffre-affaire-par-vehicule/", name="chiffre_affaire_par_vehicule", methods={"GET","POST"})
     */
    public function chiffreAffaire(Request $request)
    {
        $dateDebut = $request->query->get('dateDebut');
        $dateFin = $request->query->get('dateFin');

        //à utiliser pour date js avec time zone
        // $dateDebut = \DateTime::createFromFormat('D M d Y H:i:s e+', $dateDebut);
        // $dateFin = \DateTime::createFromFormat('D M d Y H:i:s e+', $dateFin);

        $dateDebut = new \DateTime($dateDebut);
        $dateFin = new \DateTime($dateFin);

        //on va trouver tous les véhicules impliqués dans les réservations et 
        //essayer d'avoir tous les statistiques les concernant

        $vehicules =  $this->reservationHelper->getVehiculesInvolved($this->reservationRepo->findByOneORTwoDatesIncludedBetween($dateDebut, $dateFin));

        $data = [];
        $data2 = [];

        foreach ($vehicules as $vehicule) {
            $somme_CPT = 0;
            $somme_WEB = 0;
            foreach ($this->reservationRepo->findByOneORTwoDatesIncludedBetween($dateDebut, $dateFin) as $res) {
                if ($res->getVehicule() == $vehicule) {
                    //categoriser les réservations selon nature (CPT ou WEB)
                    if ($res->getModeReservation()->getLibelle() == 'WEB') {
                        //caculer durée d'une réservation 
                        if ($dateDebut < $res->getDateDebut() && $res->getDateDebut() < $dateFin && $res->getDateFin() > $dateFin) {
                            $somme_WEB = $somme_WEB +  ($this->dateHelper->calculDuree($res->getDateDebut(), $dateFin) * ($res->getPrix() / $this->dateHelper->calculDuree($res->getDateDebut(), $res->getDateFin())));
                        }
                        if ($dateDebut < $res->getDateFin() &&  $res->getDateFin() < $dateFin && $res->getDateDebut() < $dateDebut) {
                            $somme_WEB = $somme_WEB +  ($this->dateHelper->calculDuree($dateDebut, $res->getDateFin()) * ($res->getPrix() / $this->dateHelper->calculDuree($res->getDateDebut(), $res->getDateFin())));
                        }
                        if ($dateDebut < $res->getDateDebut() && $res->getDateFin() < $dateFin) {
                            $somme_WEB = $somme_WEB + $res->getPrix();
                        }
                    } else {
                        if ($dateDebut < $res->getDateDebut() && $res->getDateDebut() < $dateFin && $res->getDateFin() > $dateFin) {
                            $somme_CPT = $somme_CPT +  ($this->dateHelper->calculDuree($res->getDateDebut(), $dateFin) * ($res->getPrix() / $this->dateHelper->calculDuree($res->getDateDebut(), $res->getDateFin())));
                        }
                        if ($dateDebut < $res->getDateFin() &&  $res->getDateFin() < $dateFin && $res->getDateDebut() < $dateDebut) {
                            $somme_CPT = $somme_CPT +  ($this->dateHelper->calculDuree($dateDebut, $res->getDateFin()) * ($res->getPrix() / $this->dateHelper->calculDuree($res->getDateDebut(), $res->getDateFin())));
                        }
                        if ($dateDebut < $res->getDateDebut() && $res->getDateFin() < $dateFin) {
                            $somme_CPT = $somme_CPT + $res->getPrix();
                        }
                    }
                }
            }

            $data['vehicule'] = $vehicule->getMarque() . " " . $vehicule->getModele() . " " . $vehicule->getImmatriculation();
            $data['web'] = number_format($somme_WEB, 2, ',', ' ');
            $data['ca'] = number_format($somme_WEB + $somme_CPT, 2, ',', ' ');
            $data['cpt'] = number_format($somme_CPT, 2, ',', ' ');
            array_push($data2, $data);
        }


        return new JsonResponse($data2);
    }

    /**
     * @param $reservations
     * @return somme du prix des reservations fait depuis l'espace client
     */

    public function getWEBReservations($reservations)
    {
        $somme = 0;
        foreach ($reservations as $reservation) {
            if ($reservation->getModeReservation()->getLibelle() == "WEB") {
                $somme = $somme + $reservation->getPrix();
            }
        }
        return $somme;
    }


    /**
     * @param $reservations
     * @return somme du prix des reservations faites depuis espace admin
     */

    public function getCPTReservations($reservations)
    {
        $somme = 0;
        foreach ($reservations as $reservation) {
            if ($reservation->getModeReservation()->getLibelle() == "CPT") {
                $somme = $somme + $reservation->getPrix();
            }
        }
        return $somme;
    }

    /**
     * @param $reservations
     * @return somme du prix des reservations faites depuis espace admin
     */

    public function getChiffreAffaire($reservations)
    {
        $somme = 0;
        foreach ($reservations as $reservation) {
            $somme = $somme + $reservation->getPrix();
        }
        return $somme;
    }
}
