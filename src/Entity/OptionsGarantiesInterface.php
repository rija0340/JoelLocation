<?php

namespace App\Entity;

interface OptionsGarantiesInterface
{
    public function getOptions();
    public function getGaranties();
    public function getConducteur();
    public function setPrixGaranties(?float $prix);
    public function setPrixOptions(?float $prix);
    public function getTarifVehicule();
    public function getPrixGaranties();
    public function getPrixOptions();
}
