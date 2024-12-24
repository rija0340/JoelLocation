<?php

namespace App\Service;

use App\Classe\Mailjet;
use App\Classe\ReservationSession;
use App\Entity\Devis;
use App\Entity\DevisOption;
use App\Service\TarifsHelper;
use App\Repository\DevisRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use App\Entity\OptionsGarantiesInterface;
use App\Repository\DevisOptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReservationHelper
{

    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $dateHelper;
    private $reservationRepo;
    private $tarifsHelper;
    private $reservationSession;
    private $userRepo;

    public function __construct(
        ReservationRepository $reservationRepo,
        DateHelper $dateHelper,
        VehiculeRepository $vehiculeRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        TarifsHelper $tarifsHelper,
        ReservationSession $reservationSession,
        UserRepository $userRepo
    ) {
        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->dateHelper = $dateHelper;
        $this->reservationRepo = $reservationRepo;
        $this->tarifsHelper = $tarifsHelper;
        $resaSession = $reservationSession;
        $userRepo = $userRepo;
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



    public function getOptionsFromRequest($request)
    {
        $optionsFromRequest = [];
        if (!is_null($request->get('siege_2')) && $request->get('siege_2') != "0") {
            $optionsFromRequest["2"] = [$this->optionsRepo->find(2), intval($request->get('siege_2'))];
        }
        if (!is_null($request->get('siege_3')) && $request->get('siege_3') != "0") {
            $optionsFromRequest["3"] =  [$this->optionsRepo->find(3), intval($request->get('siege_3'))];
        }
        if ($request->get('checkboxOptions') != null) {
            //rehausse gratuit id = 4  dans bdd 
            foreach ($request->get('checkboxOptions') as  $value) {
                $optionsFromRequest[$value] = [$this->optionsRepo->find(intval($value)), 1];
            }
        }

        return $optionsFromRequest;
    }
    /**
     * Sauvegarde les options sélectionnées pour un devis ou une réservation dans la base de données.
     *
     * @param OptionsGarantiesInterface $entity L'entité Devis ou Reservation à laquelle les options sont associées
     * @param array $optionsFromRequest Tableau d'options sélectionnées, où chaque option est un tableau [Option, Quantité]
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine
     *
     * @return OptionsGarantiesInterface L'entité mise à jour avec les options sauvegardées
     */
    public function saveDevisOptions($entity, $optionsArray, $em)
    {
        foreach ($optionsArray as $option) {
            $optId = $option[0]->getId();
            $optEntity =  $this->optionsRepo->find(intval($optId));
            $devisOption = new DevisOption();
            $devisOption->setOpt($optEntity);
            // check if entity is instance of devis 
            if ($entity instanceof Devis) {
                $devisOption->setDevis($entity);
            } else {
                $devisOption->setReservation($entity);
            }
            $devisOption->setQuantity(intval($option[1]));
            $em->persist($devisOption);
            // Store entity to track later
            $devisOptions[] = $devisOption;
        }
        $em->flush();

        return $entity;
    }

    // public function sendMailConfirmationDevis($devis, Request $request)
    // {

    //     $baseUrl = $this->site->getBaseUrl($request);
    //     $devisLink   = $this->router->generate('devis_pdf', ['id' => $devis->getId()]);
    //     $validationDevisLink   = $this->router->generate('validation_step2', ['id' => $devis->getId()]);

    //     // $devisLink = '/backoffice/devispdf/' . $devis->getId();
    //     // $resaLink = '/espaceclient/validation/options-garanties/{id}' . $devis->getId();
    //     $devisLink = $baseUrl . $devisLink;
    //     $validationDevisLink = $baseUrl . $validationDevisLink;
    //     // $linkDevis = "<a style='text-decoration: none; color: inherit;' href='" . $devisLink . "'>Télécharger mon devis</a>";
    //     // $linkReservation = "<a style='text-decoration: none; color: inherit;' href='" . $resaLink . "'>JE RESERVE</a>";

    //     $fullName = $devis->getClient()->getPrenom() . " " . $devis->getClient()->getNom();
    //     $email = $devis->getClient()->getMail();
    //     $this->mailjet->confirmationDevis(
    //         $fullName,
    //         $email,
    //         "Confirmation de demande de devis",
    //         $this->dateHelper->frenchDate($devis->getDateCreation()),
    //         $devis->getNumero(),
    //         $devis->getVehicule()->getMarque() . " " . $devis->getVehicule()->getModele(),
    //         $this->dateHelper->frenchDate($devis->getDateDepart()) . " " . $this->dateHelper->frenchHour($devis->getDateDepart()),
    //         $this->dateHelper->frenchDate($devis->getDateRetour()) . " " . $this->dateHelper->frenchHour($devis->getDateRetour()),
    //         $devisLink,
    //         $validationDevisLink
    //         //            $this->dateHelper->frenchDate($devis->getDateRetour()->modify('+3 days'))
    //     );
    // }

    public function getOptionsGarantiesAllAndData(OptionsGarantiesInterface $entity)
    {
        $dataOptions = [];
        // foreach ($entity->getOptions() as $key => $option) {
        foreach ($entity->getDevisOptions() as $key => $opt) {
            $option = $opt->getOpt();
            $dataOptions[$key]['id'] = $option->getId();
            $dataOptions[$key]['appelation'] = $option->getAppelation();
            $dataOptions[$key]['description'] = $option->getDescription();
            $dataOptions[$key]['type'] = $option->getType();
            $dataOptions[$key]['prix'] = $option->getPrix();
        }
        $dataGaranties = [];
        foreach ($entity->getGaranties() as $key => $garantie) {
            $dataGaranties[$key]['id'] = $garantie->getId();
            $dataGaranties[$key]['appelation'] = $garantie->getAppelation();
            $dataGaranties[$key]['description'] = $garantie->getDescription();
            $dataGaranties[$key]['prix'] = $garantie->getPrix();
        }

        $allOptions = [];
        foreach ($this->optionsRepo->findAll() as $key => $option) {
            $allOptions[$key]['id'] = $option->getId();
            $allOptions[$key]['appelation'] = $option->getAppelation();
            $allOptions[$key]['description'] = $option->getDescription();
            $allOptions[$key]['prix'] = $option->getPrix();
            $allOptions[$key]['type'] = $option->getType();
        }


        $allGaranties = [];
        foreach ($this->garantiesRepo->findAll() as $key => $garantie) {
            $allGaranties[$key]['id'] = $garantie->getId();
            $allGaranties[$key]['appelation'] = $garantie->getAppelation();
            $allGaranties[$key]['description'] = $garantie->getDescription();
            $allGaranties[$key]['prix'] = $garantie->getPrix();
        }

        return [
            'dataOptions' => $dataOptions,
            'dataGaranties' => $dataGaranties,
            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties
        ];
    }

    public function createDevisFromResaSession($resaSession)
    {

        $dateDepart = $resaSession->getDateDepart();
        $dateRetour = $resaSession->getDateRetour();

        $devis = new Devis();
        $devis->setDownloadId(uniqid());
        if (!is_null($dateDepart)) {
            $devis->setDateDepart($resaSession->getDateDepart());
        }
        if (!is_null($dateRetour)) {
            $devis->setDateRetour($resaSession->getDateRetour());
        }
        if (!is_null($resaSession->getAgenceDepart())) {
            $devis->setAgenceDepart($resaSession->getAgenceDepart());
        }
        if (!is_null($resaSession->getAgenceRetour())) {
            $devis->setAgenceRetour($resaSession->getAgenceRetour());
        }
        if (!is_null($resaSession->getVehicule())) {
            $vehicule = $this->vehiculeRepo->find($resaSession->getVehicule());
            $devis->setVehicule($vehicule);
        }
        if (!is_null($dateDepart) && !is_null($dateRetour)) {
            $devis->setDuree($this->dateHelper->calculDuree($dateDepart, $dateRetour));
        }

        if ($this->garantiesObjectsFromSession($resaSession) != null) {
            foreach ($this->garantiesObjectsFromSession($resaSession) as $garantie) {
                $devis->addGaranty($garantie);
            }
        }
        $devis->setLieuSejour($resaSession->getLieuSejour());
        $devis->setDateCreation($this->dateHelper->dateNow());

        //si l'admin a entrée un autre tarif dans étape 2, alors on considère ce tarif
        if ($resaSession->getTarifVehicule()) {
            $tarifVehicule = $resaSession->getTarifVehicule();
        } else {
            if (!is_null($resaSession->getVehicule()) && !is_null($dateDepart) && !is_null($dateRetour)) {
                $tarifVehicule  = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
            } else {
                $tarifVehicule = 0;
            }
        }
        $devis  = $this->setOptionsFromSessionToDevis($resaSession, $devis);
        $devis->setTarifVehicule($tarifVehicule);

        $options = $resaSession->getOptions();

        $hasConducteur = $resaSession->getConducteur() == "true"  ? true : false;
        $devis->setConducteur($hasConducteur);
        $tarifTotal = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $options, $devis->getGaranties(), $hasConducteur);
        $devis->setPrix($tarifTotal);
        $devis->setTransformed(false);
        $devis->setPrixOptions($this->tarifsHelper->sommeTarifsOptions($options, $devis->getConducteur()));
        $devis->setPrixGaranties($this->tarifsHelper->sommeTarifsGaranties($this->garantiesObjectsFromSession($resaSession)));
        return  $devis;
    }

    //return an array of objects of options or null
    // public function optionsObjectsFromSession($resaSession)
    // {
    //     //on met dans un tableau les objets corresponans aux options cochés
    //     $optionsObjects = [];
    //     if ($resaSession->getOptions() != null) {
    //         foreach ($resaSession->getOptions() as $opt) {
    //             array_push($optionsObjects, $this->optionsRepo->find($opt));
    //         }
    //         return $optionsObjects;
    //     } else {
    //         return null;
    //     }
    // }


    public function setOptionsFromSessionToDevis($resaSession, $devis)
    {
        //on met dans un tableau les objets corresponans aux options cochés
        if (!empty($resaSession->getOptions())) {
            foreach ($resaSession->getOptions() as $key => $opt) {

                $devisOption = new DevisOption();
                $devisOption->setOpt($opt[0]);
                $devisOption->setQuantity($opt[1]);
                $devis->addDevisOption($devisOption);
            }
        }
        return $devis;
    }

    //return an array of objects of garanties
    public function garantiesObjectsFromSession($resaSession)
    {
        //on met dans un tableau les objets corresponans aux garanties cochés
        $garantiesObjects = [];
        if ($resaSession->getGaranties() != null) {
            foreach ($resaSession->getGaranties() as $gar) {
                array_push($garantiesObjects, $this->garantiesRepo->find($gar));
            }
            return $garantiesObjects;
        } else {
            return null;
        }
    }

    /**
     * Get array of garantie IDs from devis
     * @param Devis $devis
     * @return array
     */
    public function getGarantiesIds($devis): array
    {
        $garantieIds = [];
        foreach ($devis->getGaranties() as $garantie) {
            $garantieIds[] = $garantie->getId();
        }
        return $garantieIds;
    }

    /**
     * Get array of garantie IDs from devis
     * @param Devis $devis
     * @return array
     */
    public function getOptionsIds($devis): array
    {
        $optionIds = [];
        foreach ($devis->getOptions() as $option) {
            $optionIds[] = $option->getId();
        }
        return $optionIds;
    }
}
