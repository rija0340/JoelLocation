<?php

namespace App\Service;

use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;


class TarifsHelper
{

    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $tarifsRepo;
    private $dateHelper;

    public function __construct(DateHelper $dateHelper, TarifsRepository $tarifsRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
    {

        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->tarifsRepo = $tarifsRepo;
        $this->dateHelper = $dateHelper;
    }

    function calculTarifTotal($tarifVehicule, $options, $garanties)
    {

        if ($options != []) {
            $optionsPrix = $this->sommeTarifsOptions($options);
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

            if ($duree > 15 && $duree <= 30) $tarifVehicule = $tarif->getTrenteJours();
        }

        return $tarifVehicule;
    }


    function sommeTarifsOptions($options)
    {
        if ($options != null) {
            $prix = 0;
            foreach ($options as  $option) {

                $prix = $prix  + $option->getPrix();
            }
            return $prix;
        } else {
            return 0;
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
        }else{
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
}
