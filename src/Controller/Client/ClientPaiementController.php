<?php

namespace App\Controller\Client;

use App\Classe\Mail;
use Stripe\Stripe;
use App\Entity\Devis;
use App\Entity\Paiement;
use App\Entity\Vehicule;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Entity\ModePaiement;
use Stripe\Checkout\Session;
use App\Service\TarifsHelper;
use App\Classe\ReservationClient;
use App\Classe\ValidationReservationClientSession;
use App\Controller\DevisController;
use App\Repository\DevisRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Controller\ReservationController;
use App\Repository\ModePaiementRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use phpDocumentor\Reflection\Types\This;
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
    private $flashy;
    private $em;
    private $modePaiementRepo;
    private $validationSession;
    private $mail;


    public function __construct(
        DevisController $devisController,
        ReservationRepository $reservRepo,
        ReservationController $reservController,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        VehiculeRepository $vehiculeRepo,
        DevisRepository $devisRepo,
        FlashyNotifier $flashy,
        EntityManagerInterface $em,
        ModePaiementRepository $modePaiementRepo,
        ValidationReservationClientSession $validationSession,
        Mail $mail

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
        $this->flashy = $flashy;
        $this->em = $em;
        $this->modePaiementRepo = $modePaiementRepo;
        $this->validationSession = $validationSession;
        $this->mail = $mail;
    }

    //test de paiement par stripe (page de paiement hebergé sur site stripe)
    /**
     * @Route("/espaceclient/paiement-stripe/{refDevis}", name="paiementStripe", methods={"GET","POST"})
     */
    public function paiementStripe(Request $request, $refDevis)
    {

        $devis = $this->devisRepo->findOneBy(['numero' => $refDevis]);

        if (!$devis || $devis->getClient() != $this->getUser()) {
            $this->flashy->error("Le devis n'existe pas");
            return $this->redirectToRoute('espaceClient_index');
        }

        $modePaiement = $this->validationSession->getModePaiment();

        //sommepaiement en fonction de mode paiement choisis par le client
        if ($modePaiement == 25) {
            $sommePaiement = $this->tarifsHelper->VingtCinqPourcent($devis->getPrix());
        }
        if ($modePaiement == 50) {
            $sommePaiement = $this->tarifsHelper->CinquantePourcent($devis->getPrix());
        }
        if ($modePaiement == 100) {
            $sommePaiement = $devis->getPrix();
        }

        Stripe::setApiKey('sk_test_51JiGijGsAu4Sp9QQtyfjOoOQMb6kfGjE1z50X5vrW6nS7wLtK5y2HmodT3ByrI7tQl9dsvP69fkN4vVfH5676nDo00VgFOzXct');

        $YOUR_DOMAIN = 'http://127.0.0.1:8000';
        $checkout_session = Session::create([
            'customer_email' => $devis->getClient()->getMail(),
            'payment_method_types' => ['card'],
            'line_items' => [[
                //pour plusieur produits, ajouter un autre tableau price_data
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Réservation du ' . $devis->getDateDepart()->format('d/m/Y H:i') . " au " . $devis->getDateRetour()->format('d/m/Y H:i'),
                        'images' => [$YOUR_DOMAIN . "/uploads/vehicules" . $devis->getVehicule()->getImage()],
                        'description' => $devis->getVehicule()->getMarque() . " " . $devis->getVehicule()->getModele()
                    ],
                    'unit_amount' => $sommePaiement * 100
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/espaceclient/paiement-stripe/succes/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/espaceclient/paiement-stripe/echec/{CHECKOUT_SESSION_ID}',
        ]);
        //enregistrer en base de données sessionStripe, utile pour recuperer le devis plus tard
        $devis->setStripeSessionId($checkout_session->id);
        $this->em->flush();

        // rediriger vers page de paiement hebergé sur stripe
        return $this->redirect($checkout_session->url);
    }


    /**
     * @Route("/espaceclient/paiement-stripe/succes/{stripeSessionId}", name="paiementStripeSucces", methods={"GET","POST"})
     */
    public function paiementStripeSucces(Request $request, $stripeSessionId)
    {
        $devis = $this->devisRepo->findOneBy(['stripeSessionId' => $stripeSessionId]);


        //securité en cas de session non valide
        if (!$devis || $devis->getClient() != $this->getUser()) {
            $this->flashy->error("Le devis n'existe pas");
            return $this->redirectToRoute('espaceClient_index');
        }

        //indiquer dans la base de données que le devis a été reservé
        if (!$devis->getTransformed()) {
            $devis->setTransformed(true);
            $this->em->flush();
        }
        //enregistrement devis comme une réservation 
        $this->reserverDevis($devis, $stripeSessionId);


        //enregistrer paiement dans table paiement
        $paiement = new Paiement;

        //sommepaiement en fonction de mode paiement choisis par le client
        $modePaiement = $this->validationSession->getModePaiment();
        if ($modePaiement == 25) {
            $sommePaiement = $this->tarifsHelper->VingtCinqPourcent($devis->getPrix());
        }
        if ($modePaiement == 50) {
            $sommePaiement = $this->tarifsHelper->CinquantePourcent($devis->getPrix());
        }
        if ($modePaiement == 100) {
            $sommePaiement = $devis->getPrix();
        }
        $paiement->setMontant($sommePaiement);
        $paiement->setReservation($this->reservRepo->findOneBy(['stripeSessionId' => $stripeSessionId]));
        $paiement->setStripeSessionId($stripeSessionId);
        $paiement->setDatePaiement($this->dateHelper->dateNow());
        $paiement->setClient($this->reservRepo->findOneBy(['stripeSessionId' => $stripeSessionId])->getClient());
        $paiement->setMotif("Réservation");
        $paiement->setModePaiement($this->modePaiementRepo->findOneBy(['libelle' => 'CARTE BANCAIRE']));
        $this->em->persist($paiement);
        $this->em->flush();

        $reservation = $this->reservRepo->findOneBy(['stripeSessionId' => $stripeSessionId]);
        //envoi de mail client
        $contentMail = 'Votre réservation numero ' . $reservation->getReference() . 'a bien été payé';
        $this->mail->send($reservation->getClient()->getMail(), $reservation->getClient()->getNom(), "Confirmation payement", $contentMail);

        $this->flashy->success("Votre réservation a été effectué avec succès, un mail vous a été envoyé pour confirmation de votre paiement");
        return $this->redirectToRoute('client_reservations');
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
    public function reserverDevis(Devis $devis, $stripeSessionId)
    {

        $reservation = new Reservation();
        $reservation->setVehicule($devis->getVehicule());
        $reservation->setStripeSessionId($stripeSessionId);
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
        $reservation->setPrixOptions($this->tarifsHelper->sommeTarifsOptions($devis->getOptions()));
        $reservation->setPrixGaranties($this->tarifsHelper->sommeTarifsOptions($devis->getGaranties()));
        $reservation->setDuree($this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour()));
        $reservation->setTarifVehicule($this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule()));
        $reservation->setPrix($devis->getPrix());
        $reservation->setDateReservation($this->dateHelper->dateNow());
        $reservation->setCodeReservation('devisTransformé');
        // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
        $lastID = $this->reservRepo->findBy(array(), array('id' => 'DESC'), 1);
        if ($lastID == null) {
            $currentID = 1;
        } else {
            $currentID = $lastID[0]->getId() + 1;
        }

        $reservation->setRefRes("CPTGP", $currentID);

        $this->em->persist($reservation);
        $this->em->flush();
        // dump($reservation);
        // die();
    }
}
