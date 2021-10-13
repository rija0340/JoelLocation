<?php



namespace App\Classe;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ValidationReservationClientSession
{
    private $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function addModePaiment($mode)
    {

        $data = $this->session->get('validationReservationClient', []);
        $data['modePaiement'] = $mode;
        $this->session->set('validationReservationClient', $data);
    }

    //return array session 
    public  function getValidationSession()
    {
        return $this->session->get('validationReservationClient');
    }

    //return mode paiement
    public  function getModePaiment()
    {
        return $this->session->get('validationReservationClient')['modePaiement'];
    }


    //remove or delete session
    public function removeValidationSession()
    {
        return $this->session->remove('validationReservationClient');
    }
}
