<?php

namespace App\Service;

use App\Repository\TarifsRepository;
use App\Repository\TarifsV2Repository;
use App\Service\PricingModeService;

class TarifsHelper
{
    private $tarifsRepo;
    private $tarifsV2Repo;
    private $dateHelper;
    private $pricingModeService;
    private $taxe;

    public function __construct(
        DateHelper $dateHelper, 
        TarifsRepository $tarifsRepo, 
        TarifsV2Repository $tarifsV2Repo,
        PricingModeService $pricingModeService
    )
    {
        $this->tarifsRepo = $tarifsRepo;
        $this->tarifsV2Repo = $tarifsV2Repo;
        $this->dateHelper = $dateHelper;
        $this->pricingModeService = $pricingModeService;
        $this->taxe = 0.085; //TVA = 8.5%
    }


    function getPrixConducteurSupplementaire()
    {
        return 50;
    }

    function getTaxe()
    {
        return $this->taxe;
    }

    /**
     * Check if V2 pricing is active
     */
    function isV2Active(): bool
    {
        return $this->pricingModeService->isV2Active();
    }

    /**
     * calcul le tarif total d'une réservation,
     * @params tarifVehicule, options, garanties
     * tous les tarifs sont en TTC
     */
    function calculTarifTotal($tarifVehicule, $options, $garanties, $hasConducteur)
    {
        // Calcul du prix des options (avec ou sans conducteur supplémentaire)
        $optionsPrix = $this->sommeTarifsOptions($options, $hasConducteur);

        // Calcul du prix des garanties
        $garantiesPrix = $this->sommeTarifsGaranties($garanties);

        // Calcul total : tarif véhicule + options + garanties
        $tarifTotal = 0;
        if ($tarifVehicule !== null) {
            $tarifTotal += $tarifVehicule;
        }

        $tarifTotal += $optionsPrix + $garantiesPrix;

        return $tarifTotal;
    }


    function calculTarifVehicule($dateDepart, $dateRetour, $vehicule)
    {
        // Check which pricing model is active
        if ($this->isV2Active()) {
            return $this->calculTarifVehiculeV2($dateDepart, $dateRetour, $vehicule);
        }

        // V1: Original bracket system
        $marque = $vehicule->getMarque();
        $modele = $vehicule->getModele();
        $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);

        // Si la durée est <= 30 jours, utiliser la logique simple (mois de départ uniquement)
        if ($duree <= 30) {
            $mois = $this->dateHelper->getMonthFullName($dateDepart);
            $tarif = $this->tarifsRepo->findOneBy(['marque' => $marque, 'modele' => $modele, 'mois' => $mois]);

            $tarifVehicule = 0;

            if (!is_null($tarif)) {
                if ($duree <= 3) $tarifVehicule = $tarif->getTroisJours();
                elseif ($duree > 3 && $duree <= 7) $tarifVehicule = $tarif->getSeptJours();
                elseif ($duree > 7 && $duree <= 15) $tarifVehicule = $tarif->getQuinzeJours();
                elseif ($duree > 15) $tarifVehicule = $tarif->getTrenteJours();
            }

            return $tarifVehicule;
        }

        // Pour les durées > 30 jours : calcul mois par mois
        return $this->calculTarifMultiMois($dateDepart, $dateRetour, $marque, $modele);
    }

    /**
     * V2: Calculate vehicle pricing with custom day ranges
     */
    function calculTarifVehiculeV2($dateDepart, $dateRetour, $vehicule)
    {
        $marque = $vehicule->getMarque();
        $modele = $vehicule->getModele();
        $duree = $this->dateHelper->calculDureeInclusif($dateDepart, $dateRetour);

        // If duration <= 30 days, use single month lookup
        if ($duree <= 30) {
            $mois = $this->dateHelper->getMonthFullName($dateDepart);
            $tarifV2 = $this->tarifsV2Repo->findOneByMarqueModeleMois($marque, $modele, $mois);

            if ($tarifV2) {
                $prix = $tarifV2->getPrixForDays($duree);
                return $prix !== null ? $prix : 0;
            }

            return 0;
        }

        // For multi-month: calculate month by month
        return $this->calculTarifMultiMoisV2($dateDepart, $dateRetour, $marque, $modele);
    }

    /**
     * Calcule le tarif pour les réservations multi-mois
     * Règle métier : découper par mois civil et appliquer le bracket de tarif pour chaque mois
     * 
     * @param \DateTime $dateDepart
     * @param \DateTime $dateRetour
     * @param Marque $marque
     * @param Modele $modele
     * @return float
     */
    private function calculTarifMultiMois($dateDepart, $dateRetour, $marque, $modele)
    {
        $tarifTotal = 0;
        $dateCourante = clone $dateDepart;

        while ($dateCourante < $dateRetour) {
            // Récupérer le nom du mois courant
            $mois = $this->dateHelper->getMonthFullName($dateCourante);
            
            // Récupérer le tarif pour ce mois
            $tarif = $this->tarifsRepo->findOneBy([
                'marque' => $marque, 
                'modele' => $modele, 
                'mois' => $mois
            ]);

            if (!is_null($tarif)) {
                // Calculer la fin du mois courant
                $finDuMois = new \DateTime($dateCourante->format('Y-m-t'));
                
                // Déterminer la date de fin pour cette itération (fin du mois ou date de retour)
                $dateFinPeriode = ($finDuMois < $dateRetour) ? $finDuMois : $dateRetour;
                
                // Calculer le nombre de jours dans cette période
                $joursDansPeriode = $this->dateHelper->calculDuree($dateCourante, $dateFinPeriode);

                // Appliquer le bracket de tarif selon le nombre de jours
                if ($joursDansPeriode <= 3) {
                    $tarifTotal += $tarif->getTroisJours();
                } elseif ($joursDansPeriode > 3 && $joursDansPeriode <= 7) {
                    $tarifTotal += $tarif->getSeptJours();
                } elseif ($joursDansPeriode > 7 && $joursDansPeriode <= 15) {
                    $tarifTotal += $tarif->getQuinzeJours();
                } elseif ($joursDansPeriode > 15) {
                    $tarifTotal += $tarif->getTrenteJours();
                }
            }

            // Passer au mois suivant (1er jour du mois suivant)
            $dateCourante = new \DateTime($finDuMois->format('Y-m-d') . ' +1 day');
        }

        return $tarifTotal;
    }

    /**
     * V2: Calcule le tarif pour les réservations multi-mois avec custom day ranges
     *
     * @param \DateTime $dateDepart
     * @param \DateTime $dateRetour
     * @param Marque $marque
     * @param Modele $modele
     * @return float
     */
    private function calculTarifMultiMoisV2($dateDepart, $dateRetour, $marque, $modele)
    {
        $tarifTotal = 0;
        $dateCourante = clone $dateDepart;

        while ($dateCourante < $dateRetour) {
            // Récupérer le nom du mois courant
            $mois = $this->dateHelper->getMonthFullName($dateCourante);

            // Récupérer le tarif V2 pour ce mois
            $tarifV2 = $this->tarifsV2Repo->findOneByMarqueModeleMois($marque, $modele, $mois);

            if (!is_null($tarifV2)) {
                // Calculer la fin du mois courant
                $finDuMois = new \DateTime($dateCourante->format('Y-m-t'));

                // Déterminer la date de fin pour cette itération (fin du mois ou date de retour)
                $dateFinPeriode = ($finDuMois < $dateRetour) ? $finDuMois : $dateRetour;

                // Calculer le nombre de jours dans cette période
                $joursDansPeriode = $this->dateHelper->calculDureeInclusif($dateCourante, $dateFinPeriode);

                // Appliquer le tarif selon les custom day ranges
                $prix = $tarifV2->getPrixForDays($joursDansPeriode);
                if ($prix !== null) {
                    $tarifTotal += $prix;
                }
            }

            // Passer au mois suivant (1er jour du mois suivant)
            $dateCourante = new \DateTime($finDuMois->format('Y-m-d') . ' +1 day');
        }

        return $tarifTotal;
    }


    function sommeTarifsOptions($options, $hasConducteur)
    {
        $prixConductSuppl = ($hasConducteur  == true) ? $this->getPrixConducteurSupplementaire() : 0;

        $price = 0;
        if (is_array($options)) {
            foreach ($options as  $opt) {
                $price  = $price   + ($opt[0]->getPrix() * $opt[1]);
            }
        }
        return $price + $prixConductSuppl;
    }


    function sommeTarifsGaranties($garanties)
    {
        if ($garanties != null) {
            $prix = 0;
            foreach ($garanties as $garantie) {
                $prix = $prix + $garantie->getPrix();
            }
            return $prix;
        } else {
            return 0;
        }
    }

    function CinquantePourcent($tarif)
    {

        $value = (50 * $tarif) / 100;

        return $value;
    }
    function VingtCinqPourcent($tarif)
    {

        $value = (25 * $tarif) / 100;

        return $value;
    }

    //function pour calculer le prix ttc
    function calculTarifTTCfromHT($tarifHT)
    {
        $prixTTC = $tarifHT * (1 + $this->taxe);
        return $prixTTC;
    }

    function calculTarifHTfromTTC($tarifTTC)
    {
        $prixHT = $tarifTTC / (1 + $this->taxe);
        return $prixHT;
    }

    function calculTaxeFromHT($tarifHT)
    {

        return $tarifHT * $this->taxe;
    }
}
