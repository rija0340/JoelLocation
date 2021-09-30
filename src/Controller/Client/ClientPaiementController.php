<?php

namespace App\Controller\Client;

use App\Entity\Devis;
use App\Entity\Paiement;
use App\Entity\Vehicule;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Entity\ModePaiement;
use App\Service\TarifsHelper;
use App\Controller\DevisController;
use App\Repository\DevisRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Controller\ReservationController;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientPaiementController extends AbstractController
{
    private  $devisController;
    private $reservRepo;
    private $reservController;
    private $garantiesRepo;
    private $optionsRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $vehiculeRepo;
    private $devisRepo;


    public function __construct(
        DevisController $devisController,
        ReservationRepository $reservRepo,
        ReservationController $reservController,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        VehiculeRepository $vehiculeRepo,
        DevisRepository $devisRepo

    ) {
        $this->devisController = $devisController;
        $this->reservRepo = $reservRepo;
        $this->reservController = $reservController;
        $this->garantiesRepo = $garantiesRepo;
        $this->optionsRepo = $optionsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->devisRepo = $devisRepo;
    }

    /**
     * @Route("/payement", name="payement", methods={"GET","POST"})
     */
    public function payement(Request $request)

    {

        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $montant = floatval($request->request->get("montant"));

        //id de la reservation
        $id = $request->request->get("id");

        //$reservation = $this->getDoctrine()->getRepository(Reservation::class)->findOneBy(["client" => $client], ["id" => "DESC"]);
        $devis = $this->getDoctrine()->getRepository(Devis::class)->findOneBy(["id" => $id]);
        $modePaiement = $this->getDoctrine()->getRepository(ModePaiement::class)->findOneBy(["id" => 1]);
        $vehicule = new Vehicule();
        if ($devis == null) {
            return $this->redirectToRoute('espaceClient_index');
        }
        $vehicule = $devis->getVehicule();
        //$caution = $vehicule->getCaution() * 100;
        // $net_a_payer = (($devis->getPrix() * $montant) / 100);
        $net_a_payer = $montant * 100;
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey('sk_test_51INCSpLWsPgEVX5UZKrH0YIs7H7PF8Boao1VcYHEks40it5a39h5KJzcwWxSWUIV6ODWkPS7txKsRyKeSfBknDFC00PAHEBwVP');

        // Token is created using Stripe Checkout or Elements!  
        // Get the payment token ID submitted by the form:
        //$token = $_POST['stripeToken'];
        $token = $request->request->get('stripeToken');
        $charge = \Stripe\Charge::create([
            'amount' => $net_a_payer,
            'currency' => 'eur',
            'source' => $token,
            'description' => 'payement avance pour le véhicule ' . $vehicule->getMarque() . ' ' . $vehicule->getModele() . ' à hauteur de ' . $montant . '% du tarif',
        ]);

        if ($charge->status == "succeeded") {

            $this->reserverDevis($devis);
            $reservation = $this->reservRepo->findOneBy(['numDevis' => $devis->getId()]);

            $paiement = new Paiement();
            $paiement->setReservation($reservation);
            $paiement->setModePaiement($modePaiement);
            $paiement->setUtilisateur($client);
            $paiement->setClient($client);
            $paiement->setMontant($net_a_payer);
            $paiement->setDatePaiement($this->dateHelper->dateNow());
            $paiement->setMotif('caution pour le véhicule ' . $vehicule->getMarque() . ' ' . $vehicule->getModele());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($paiement);
            $entityManager->flush();
            return $this->redirectToRoute('espaceClient_index');
        } else {
            echo "Erreur de paiement !";
        }
        //return $this->redirectToRoute('client');
    }
    public function reserverDevis(Devis $devis)
    {

        $devis->setTransformed(true);
        $devisManager =  $this->devisController->getDoctrine()->getManager();
        $devisManager->persist($devis);
        $devisManager->flush();

        $reservation = new Reservation();
        $reservation->setVehicule($devis->getVehicule());
        $reservation->setClient($devis->getClient());
        $reservation->setDateDebut($devis->getDateDepart());
        $reservation->setDateFin($devis->getDateRetour());
        $reservation->setAgenceDepart($devis->getAgenceDepart());
        $reservation->setAgenceRetour($devis->getAgenceRetour());
        $reservation->setNumDevis($devis->getId()); //reference numero devis reservé
        //boucle pour ajout options 
        foreach ($devis->getOptions() as $option) {
            $reservation->addOption($option);
        }

        //boucle pour ajout garantie 

        foreach ($devis->getGaranties() as $garantie) {
            $reservation->addGaranty($garantie);
        }

        $reservation->setPrix($devis->getPrix());
        $reservation->setDateReservation(new \DateTime('NOW'));
        $reservation->setCodeReservation('devisTransformé');
        // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
        $lastID = $this->reservRepo->findBy(array(), array('id' => 'DESC'), 1);
        if ($lastID == null) {
            $currentID = 1;
        } else {
            $currentID = $lastID[0]->getId() + 1;
        }

        $reservation->setRefRes("CPTGP", $currentID);

        $entityManager = $this->reservController->getDoctrine()->getManager();
        $entityManager->persist($reservation);
        $entityManager->flush();
        // dump($reservation);
        // die();
    }
}
