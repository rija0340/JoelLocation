<?php


namespace App\Classe;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ReservationSession
{

    private $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function addDateDepart($dateDepart)
    {

        $data = $this->session->get('reservation', []);
        $data['dateDepart'] = $dateDepart;
        $this->session->set('reservation', $data);
    }

    public function addAgenceDepart($agenceDepart)
    {

        $data = $this->session->get('reservation', []);
        $data['agenceDepart'] = $agenceDepart;
        $this->session->set('reservation', $data);
    }

    public function addDateRetour($dateRetour)
    {

        $data = $this->session->get('reservation', []);
        $data['dateRetour'] = $dateRetour;
        $this->session->set('reservation', $data);
    }

    public function addAgenceRetour($agenceRetour)
    {

        $data = $this->session->get('reservation', []);
        $data['agenceRetour'] = $agenceRetour;
        $this->session->set('reservation', $data);
    }

    public function addTypeVehicule($typeVehicule)
    {

        $data = $this->session->get('reservation', []);
        $data['typeVehicule'] = $typeVehicule;
        $this->session->set('reservation', $data);
    }

    public function addLieuSejour($lieuSejour)
    {

        $data = $this->session->get('reservation', []);
        $data['lieuSejour'] = $lieuSejour;
        $this->session->set('reservation', $data);
    }

    public function addVehicule($vehicule)
    {

        $data = $this->session->get('reservation', []);
        $data['vehicule'] = $vehicule;
        $this->session->set('reservation', $data);
    }

    public function addOptions($options = [])
    {

        $data = $this->session->get('reservation', []);
        $data['options'] = $options;
        $this->session->set('reservation', $data);
    }

    public function addGaranties($garanties = [])
    {

        $data = $this->session->get('reservation', []);
        $data['garanties'] = $garanties;
        $this->session->set('reservation', $data);
    }

    public function addClient($client)
    {

        $data = $this->session->get('reservation', []);
        $data['client'] = $client;
        $this->session->set('reservation', $data);
    }

    public function addConducteur($conducteur)
    {

        $data = $this->session->get('reservation', []);
        $data['conducteur'] = $conducteur;
        $this->session->set('reservation', $data);
    }

    public function addTarifVehicule($tarifVehicule)
    {

        $data = $this->session->get('reservation', []);
        $data['tarifVehicule'] = $tarifVehicule;
        $this->session->set('reservation', $data);
    }

    public  function getReservation()
    {
        return $this->session->get('reservation');
    }


    public  function getDateDepart()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['dateDepart'];
        } else {
            return null;
        }
    }
    public  function getAgenceDepart()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['agenceDepart'];
        } else {
            return null;
        }
    }

    public  function getAgenceRetour()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['agenceRetour'];
        } else {
            return null;
        }
    }
    public  function getDateRetour()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['dateRetour'];
        } else {
            return null;
        }
    }

    public  function getLieuSejour()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['lieuSejour'];
        } else {
            return null;
        }
    }
    public  function getTypeVehiculle()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['typeVehicule'];
        } else {
            return null;
        }
    }
    public  function getGaranties()
    {
        if ($this->getReservation() != null && array_key_exists('options', $this->session->get('reservation'))) {
            return $this->session->get('reservation')['garanties'];
        } else {
            return null;
        }
    }
    public  function getOptions()
    {
        //choix de options est optionnel
        if ($this->getReservation() != null && array_key_exists('options', $this->session->get('reservation'))) {
            return $this->session->get('reservation')['options'];
        } else {
            return null;
        }
    }
    public  function getVehicule()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['vehicule'];
        } else {
            return null;
        }
    }
    public  function getClient()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['client'];
        } else {
            return null;
        }
    }
    //tarif saisie par l'admin (optionel)
    public  function getTarifVehicule()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['tarifVehicule'];
        } else {
            return null;
        }
    }

    public  function getConducteur()
    {
        if ($this->getReservation() != null) {
            return $this->session->get('reservation')['conducteur'];
        } else {
            return null;
        }
    }

    public function removeReservation()
    {

        return $this->session->remove('reservation');
    }
}
