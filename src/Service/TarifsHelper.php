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

    function calculTarif($dateDepart, $dateRetour, $siege, $garantie, $vehicule)
    {

        if (!is_object($siege) && !is_object($garantie) && !is_object($vehicule)) {

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

            $vehicule = $this->vehiculeRepo->find(intval($vehicule));
        } else {
            $siegePrix = $siege->getPrix();
            $garantiePrix = $garantie->getPrix();
        }

        $mois = $this->dateHelper->getMonthName($dateDepart);
        $tarifs = $this->tarifsRepo->findTarifs($vehicule, $mois);
        $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);
        $tarif = 0; //initialisation de $tarif
        if (!is_null($tarifs)) {

            if ($duree <= 3) $tarif = $tarifs->getTroisJours();

            if ($duree > 3 && $duree <= 7) $tarif = $tarifs->getSeptJours();

            if ($duree > 7 && $duree <= 15) $tarif = $tarifs->getQuinzeJours();

            if ($duree > 15 && $duree <= 30) $tarif = $tarifs->getTrenteJours();
        }

        if ($tarif != null) {
            $prix = $tarif + $siegePrix + $garantiePrix;
        } else {
            $prix = $siegePrix + $garantiePrix;
        }

        return $prix;
    }
}
