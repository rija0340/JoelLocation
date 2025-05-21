<?php

namespace App\Service;

use App\Repository\TarifsRepository;

class TarifsHelper
{
    private $tarifsRepo;
    private $dateHelper;
    private $taxe;

    public function __construct(DateHelper $dateHelper, TarifsRepository $tarifsRepo)
    {

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
