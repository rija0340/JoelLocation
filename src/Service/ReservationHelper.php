<?php

namespace App\Service;

use App\Classe\Mailjet;
use App\Service\TarifsHelper;
use App\Repository\DevisRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReservationHelper
{

    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $tarifsRepo;
    private $dateHelper;
    private $reservationRepo;
    private $mailjet;
    private $devisRepo;
    private $site;
    private $router;
    private $tarifsHelper;

    public function __construct(
        ReservationRepository $reservationRepo,
        DateHelper $dateHelper,
        TarifsRepository $tarifsRepo,
        VehiculeRepository $vehiculeRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        Mailjet $mailjet,
        DevisRepository $devisRepo,
        Site $site,
        UrlGeneratorInterface $router,
        TarifsHelper $tarifsHelper
    ) {
        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->tarifsRepo = $tarifsRepo;
        $this->dateHelper = $dateHelper;
        $this->reservationRepo = $reservationRepo;
        $this->mailjet = $mailjet;
        $this->devisRepo = $devisRepo;
        $this->site = $site;
        $this->router = $router;
        $this->tarifsHelper = $tarifsHelper;
    }

    //paramètres : reservations qui sont inclus durant l'intervalle de date de début et date de fin 
    //cette fonction renvoi les véhicules disponibles qui ne sont pas occupées dans ces réservations
    public function getVehiculesDisponible($reservations)
    {
        $vehicules = $this->vehiculeRepo->findAllVehiculesWithoutVendu();
        //mettre toutes les véhicules reservées dans un tableau
        $vehiculesInvolved = [];
        foreach ($reservations as $res) {
            array_push($vehiculesInvolved, $res->getVehicule());
        }

        $vehiculesInvolved = array_unique($vehiculesInvolved);

        //detecter les véhicules reservé et retenir les autres qui sont disponible dans l'array $vehiculesDispobible
        $vehiculesDisponible = [];
        foreach ($vehicules as $veh) {
            if (in_array($veh, $vehiculesInvolved)) {
            } else {
                array_push($vehiculesDisponible, $veh);
            }
        }
        return $vehiculesDisponible;
    }

    public function vehiculeIsInvolved($reservations, $vehicule)
    {
        $vehiculesInvolved = [];
        foreach ($reservations as $res) {
            array_push($vehiculesInvolved, $res->getVehicule());
        }
        $vehiculesInvolved = array_unique($vehiculesInvolved);

        $result = false;
        foreach ($vehiculesInvolved as $veh) {
            if (in_array($vehicule, $vehiculesInvolved)) {
                $result =  true;
            } else {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @return array of associatives array
     */
    public function getPastReservations($vehiculesDisponible, $date)
    {
        //last reservations return an array and compare vehicules in 
        $pastReservations = [];
        $data = [];
        $listPastReservations = [];
        // boucler les vehicules dispobibles et prendres les reservations pour chaque véhicule
        foreach ($vehiculesDisponible as $vehicule) {
            $pastReservations = $this->reservationRepo->findLastReservations($vehicule, $date);
            if ($pastReservations != null) {
                $datesFin = [];
                foreach ($pastReservations as  $res) {
                    array_push($datesFin, $res->getDateFin());
                }
                $dateRetour = max($datesFin);
                array_push($listPastReservations, $this->reservationRepo->findOneBy(['vehicule' => $vehicule, 'date_fin' => $dateRetour]));
            }
        }
        return $listPastReservations;
    }

    /**
     * @return array of associatives array
     */
    public function getNextReservations($vehiculesDisponible, $date)
    {
        //last reservations return an array and compare vehicules in 
        $nextReservations = [];
        $data = [];
        $listNextReservations = [];
        foreach ($vehiculesDisponible as $vehicule) {

            $nextReservations = $this->reservationRepo->findNextReservations($vehicule, $date);

            if ($nextReservations != null) {
                $datesDepart = [];
                foreach ($nextReservations as  $res) {
                    array_push($datesDepart, $res->getDateDebut());
                }
                $datesDepart = min($datesDepart);
                array_push($listNextReservations, $this->reservationRepo->findOneBy(['vehicule' => $vehicule, 'date_debut' => $datesDepart]));
            }
        }

        return $listNextReservations;
    }
    /***
     * @param reservations
     * @return array of vehicules occupé
     */
    public function getVehiculesInvolved($reservations)
    {
        $vehicules = [];
        foreach ($reservations as $reservation) {
            array_push($vehicules, $reservation->getVehicule());
        }

        return array_unique($vehicules);
    }

    /** 
     * @return float total frais supplementaire
     */
    public function getTotalFraisTTC($reservation)
    {
        $somme = 0;
        foreach ($reservation->getFraisSupplResas() as $frais) {
            $somme = $somme + $frais->getTotalHT();
        }
        $prix = $this->tarifsHelper->calculTarifTTCfromHT($somme);

        return $prix;
    }

    /** 
     * @return float total frais supplementaire
     */
    public function getTotalFraisHT($reservation)
    {
        $somme = 0;
        foreach ($reservation->getFraisSupplResas() as $frais) {
            $somme = $somme + $frais->getTotalHT();
        }
        return $somme;
    }

    /** 
     * @return float total frais supplementaire
     */
    public function getTotalResaFraisTTC($reservation)
    {
        return $this->getTotalFraisTTC($reservation) + $reservation->getPrix();
    }

    /** 
     * @return float total prix ttc
     */
    public function getPrixResaTTC($reservation)
    {
        return $reservation->getPrix();
    }

    /** 
     * @return float en ttc d'un somme quelconque
     */
    public function getPrixTTC($prix)
    {
        $taxe = $this->tarifsHelper->getTaxe();
        return ($prix + ($prix * $taxe));
    }



    public function sendMailConfirmationReservation($reservation)
    {
        //lien pour telechargement devis

        $devis = $this->devisRepo->find($reservation->getNumDevis());
        $url = '/backoffice/devispdf/' . $devis->getId();
        $url = "https://joellocation.com" . $url;
        $linkDevis = "<a style='text-decoration: none; color: inherit;' href='" . $url . "'>Télécharger mon devis</a>";

        $this->mailjet->confirmationReservation(
            $reservation->getClient()->getPrenom() . ' ' . $reservation->getClient()->getNom(),
            $reservation->getClient()->getMail(),
            "Confirmation de réservation",
            $reservation->getDateReservation()->format('d/m/Y H:i'),
            $reservation->getReference(),
            $reservation->getVehicule()->getMarque() . ' ' . $reservation->getVehicule()->getModele(),
            $reservation->getDateDebut()->format('d/m/Y H:i'),
            $reservation->getDateFin()->format('d/m/Y H:i'),
            $reservation->getPrix(),
            $this->tarifsHelper->VingtCinqPourcent($reservation->getPrix()),
            $this->tarifsHelper->CinquantePourcent($reservation->getPrix()),
            $reservation->getPrix() - $this->tarifsHelper->VingtCinqPourcent($reservation->getPrix()),
            $linkDevis
        );
    }


    public function sendMailConfirmationDevis($devis, Request $request)
    {

        $baseUrl = $this->site->getBaseUrl($request);
        $devisLink   = $this->router->generate('devis_pdf', ['id' => $devis->getId()]);
        $validationDevisLink   = $this->router->generate('validation_step2', ['id' => $devis->getId()]);

        // $devisLink = '/backoffice/devispdf/' . $devis->getId();
        // $resaLink = '/espaceclient/validation/options-garanties/{id}' . $devis->getId();
        $devisLink = $baseUrl . $devisLink;
        $validationDevisLink = $baseUrl . $validationDevisLink;
        // $linkDevis = "<a style='text-decoration: none; color: inherit;' href='" . $devisLink . "'>Télécharger mon devis</a>";
        // $linkReservation = "<a style='text-decoration: none; color: inherit;' href='" . $resaLink . "'>JE RESERVE</a>";

        $fullName = $devis->getClient()->getPrenom() . " " . $devis->getClient()->getNom();
        $email = $devis->getClient()->getMail();
        $this->mailjet->confirmationDevis(
            $fullName,
            $email,
            "Confirmation de demande de devis",
            $this->dateHelper->frenchDate($devis->getDateCreation()),
            $devis->getNumero(),
            $devis->getVehicule()->getMarque() . " " . $devis->getVehicule()->getModele(),
            $this->dateHelper->frenchDate($devis->getDateDepart()) . " " . $this->dateHelper->frenchHour($devis->getDateDepart()),
            $this->dateHelper->frenchDate($devis->getDateRetour()) . " " . $this->dateHelper->frenchHour($devis->getDateRetour()),
            $devisLink,
            $validationDevisLink
            //            $this->dateHelper->frenchDate($devis->getDateRetour()->modify('+3 days'))
        );
    }
}
