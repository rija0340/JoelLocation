<?php

namespace App\Service;


class VehiculeHelper
{
    public function isVehiculeVendu($vehicule)
    {
        $vehiculeOptions =  $vehicule->getOptions();
        $isVendu = false;
        if (!is_null($vehiculeOptions)) {
            if (array_key_exists("vendu", $vehiculeOptions)) {
                if ($vehiculeOptions['vendu'] == 1) {
                    $isVendu = true;
                }
            }
        }
        return  $isVendu;
    }

    public function getDateVente($vehicule)
    {
        $vehiculeOptions =  $vehicule->getOptions();
        if (!is_null($vehiculeOptions)) {
            if (array_key_exists("dateVente", $vehiculeOptions)) {

                return $vehiculeOptions['dateVente'];
            }
        }
        return null;
    }
}
