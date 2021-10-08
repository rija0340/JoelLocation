<?php



namespace App\Classe;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Reservation
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


    public function addTarif($tarif)
    {

        $data = $this->session->get('reservation', []);
        $data['tarif'] = $tarif;
        $this->session->set('reservation', $data);
    }

    public  function getReservation()
    {
        return $this->session->get('reservation');
    }


    public  function getDateDepart()
    {
        return $this->session->get('reservation')['dateDepart'];
    }
    public  function getAgenceDepart()
    {
        return $this->session->get('reservation')['agenceDepart'];
    }

    public  function getAgenceRetour()
    {
        return $this->session->get('reservation')['agenceRetour'];
    }
    public  function getDateRetour()
    {
        return $this->session->get('reservation')['dateRetour'];
    }

    public  function getLieuSejour()
    {
        return $this->session->get('reservation')['lieuSejour'];
    }
    public  function getTypeVehiculle()
    {
        return $this->session->get('reservation')['typeVehicule'];
    }
    public  function getGaranties()
    {
        return $this->session->get('reservation')['garanties'];
    }
    public  function getOptions()
    {
        return $this->session->get('reservation')['options'];
    }
    public  function getVehicule()
    {
        return $this->session->get('reservation')['vehicule'];
    }
    public  function getClient()
    {
        return $this->session->get('reservation')['client'];
    }
    //tarif saisie par l'admin (optionel)
    public  function getTarif()
    {
        return $this->session->get('reservation')['tarif'];
    }


    public function removeReservation()
    {
        return $this->session->remove('reservation');
    }
}
