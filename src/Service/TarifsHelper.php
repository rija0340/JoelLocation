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

    function calculTarifTotal($tarifVehicule, $siege, $garantie)
    {

        if (!is_object($siege) && !is_object($garantie)) {

            if (!is_float($siege)) {
                $siegePrix = $this->optionsRepo->find(intval($siege))->getPrix();
            } else {
                $siegePrix = $siege;
            }
            if (!is_float($garantie)) {
                $garantiePrix = $this->garantiesRepo->find(intval($garantie))->getPrix();
            } else {
                $garantiePrix = $garantie;
            }
        } else {
            $siegePrix = $siege->getPrix();
            $garantiePrix = $garantie->getPrix();
        }

        $tarifTotal = 0; //initialisation de $tarif
        if ($tarifVehicule != null) {
            $tarifTotal = $tarifVehicule + $siegePrix + $garantiePrix;
        } else {
            $tarifTotal = $siegePrix + $garantiePrix;
        }

        return $tarifTotal;
    }

    function calculTarifVehicule($dateDepart, $dateRetour, $vehicule)
    {
        $mois = $this->dateHelper->getMonthName($dateDepart);
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
}
