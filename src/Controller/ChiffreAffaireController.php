<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;
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
    public function __construct(ReservationRepository $reservationRepo, ReservationHelper $reservationHelper, VehiculeRepository $vehiculeRepo)
    {
        $this->vehiculeRepo = $vehiculeRepo;
        $this->reservationRepo = $reservationRepo;
        $this->reservationHelper = $reservationHelper;
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

        dump($dateDebut, $dateFin);
        //on va trouver tous les véhicules impliqués dans les réservations et 
        //essayer d'avoir tous les statistiques les concernant
        $vehicules =  $this->reservationHelper->getVehiculesInvolved($this->reservationRepo->findReservationsSansStopSalesBetweenDates($dateDebut, $dateFin));

        $data = [];
        foreach ($vehicules as $key => $vehicule) {

            $data[$key]['vehicule'] = $vehicule->getMarque() . " " . $vehicule->getModele() . " " . $vehicule->getImmatriculation();
            $data[$key]['web'] = $this->getWEBReservations($vehicule->getReservations());
            $data[$key]['ca'] = $this->getChiffreAffaire($vehicule->getReservations());
            $data[$key]['cpt'] = $this->getCPTReservations($vehicule->getReservations());
        }
        return new JsonResponse($data);
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
