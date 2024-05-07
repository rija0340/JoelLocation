<?php

namespace App\Service;

use App\Entity\Options;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;


class TarifsHelper
/**
 * Helper class for calculating tariff prices.
 * 
 * Contains methods for:
 * - Calculating total tariff price based on vehicle, options, and warranties
 * - Getting vehicle tariff price based on rental dates 
 * - Summing option prices
 * - Summing warranty prices
 * - Calculating percentage discounts
 * - Converting between pre-tax and post-tax prices
 * 
 * Uses injected repositories and date helper to get necessary data.
 */
/**
 * Helper class for calculating tariff prices.
 * 
 * Contains methods for:
 * - Calculating total tariff price based on vehicle, options, and warranties
 * - Getting vehicle tariff price based on rental dates 
 * - Summing option prices
 * - Summing warranty prices
 * - Calculating percentage discounts
 * - Converting between pre-tax and post-tax prices
 * 
 * Uses injected repositories and date helper to get necessary data.
 */
/**
 * Helper class for calculating tariff prices.
 * 
 * Contains methods for:
 * - Getting vehicle, option, and warranty prices
 * - Calculating total price from components
 * - Applying percentage discounts
 * - Converting between pre-tax and post-tax prices
 * 
 * Uses injected repositories and date helper.
 */
{

    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $tarifsRepo;
    private $dateHelper;
    private $taxe;

    public function __construct(DateHelper $dateHelper, TarifsRepository $tarifsRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
    {

        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->tarifsRepo = $tarifsRepo;
        $this->dateHelper = $dateHelper;
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
     * calcul le tarif total d'une réservation, 
     * @params tarifVehicule, options, garanties
     * tous les tarifs sont en TTC
     */
    function calculTarifTotal($tarifVehicule, $options, $garanties, $hasConducteur)
    {

        if ($options != []) {
            $optionsPrix = $this->sommeTarifsOptions($options, $hasConducteur);
        } else {
            $optionsPrix = 0;
        }
        if ($garanties != []) {
            $garantiesPrix = $this->sommeTarifsGaranties($garanties);
        } else {
            $garantiesPrix = 0;
        }

        $tarifTotal = 0; //initialisation de $tarif
        if ($tarifVehicule != null) {
            $tarifTotal = $tarifVehicule + $optionsPrix + $garantiesPrix;
        } else {
            $tarifTotal = $optionsPrix + $garantiesPrix;
        }

        return $tarifTotal;
    }

    function calculTarifVehicule($dateDepart, $dateRetour, $vehicule)
    {
        $mois = $this->dateHelper->getMonthFullName($dateDepart);
        $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);
        // $tarif = $this->tarifsRepo->findTarifs($vehicule, $mois);
        $marque = $vehicule->getMarque();
        $modele = $vehicule->getModele();

        $tarif = $this->tarifsRepo->findOneBy(['marque' => $marque, 'modele' => $modele, 'mois' => $mois]);

        $tarifVehicule = 0;

        if (!is_null($tarif)) {

            if ($duree <= 3) $tarifVehicule = $tarif->getTroisJours();

            if ($duree > 3 && $duree <= 7) $tarifVehicule = $tarif->getSeptJours();

            if ($duree > 7 && $duree <= 15) $tarifVehicule = $tarif->getQuinzeJours();

            if ($duree > 15) $tarifVehicule = $tarif->getTrenteJours();
        }

        return $tarifVehicule;
    }


    function sommeTarifsOptions($options, $hasConducteur)
    {
        $prixConductSuppl = ($hasConducteur  == true) ? $this->getPrixConducteurSupplementaire() : 0;
        if ($options != null) {
            $prix = 0;
            foreach ($options as  $option) {

                $prix = $prix  + $option->getPrix();
            }
            return $prix + $prixConductSuppl;
        } else {
            return $prixConductSuppl;
        }
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
