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

class PaiementSoldeController extends AbstractController
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

    //paiement sold du client (link dans espace client)
    /**
     * @Route("/espaceclient/paiement-stripe-solde/", name="paiement_sold", methods={"GET","POST"})
     */
    public function paiementStripeSolde(Request $request)
    {

        $id =  $request->request->get('reservation');
        $sommePaiement = $request->request->get('montantSolde');

        $reservation = $this->reservRepo->find($id);


        if (!$reservation || $reservation->getClient() != $this->getUser()) {
            $this->flashy->error("Le devis n'existe pas");
            return $this->redirectToRoute('espaceClient_index');
        }

        //au cas ou le client a déjà payé la réservation et le paiement est déjà complet (prevention d'une bug)
        if ($reservation->getPaiements() >= $reservation->getPrix()) {
            $this->flashy->error("Le paiement est déjà total pour cette réservation");
            return $this->redirectToRoute('espaceClient_index');
        }

        Stripe::setApiKey('sk_test_51JiGijGsAu4Sp9QQtyfjOoOQMb6kfGjE1z50X5vrW6nS7wLtK5y2HmodT3ByrI7tQl9dsvP69fkN4vVfH5676nDo00VgFOzXct');

        $YOUR_DOMAIN = 'http://127.0.0.1:8000';
        $checkout_session = Session::create([
            'customer_email' => $reservation->getClient()->getMail(),
            'payment_method_types' => ['card'],
            'line_items' => [[
                //pour plusieur produits, ajouter un autre tableau price_data
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Solde de la réservation N° ' . $reservation->getReference(),
                        'images' => [$YOUR_DOMAIN . "/uploads/vehicules/" . $reservation->getVehicule()->getImage()],
                        'description' => $reservation->getVehicule()->getMarque() . " " . $reservation->getVehicule()->getModele()
                    ],
                    'unit_amount' => $sommePaiement * 100
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/espaceclient/paiement-solde/succes/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/espaceclient/paiement-solde/echec/{CHECKOUT_SESSION_ID}',
        ]);
        //enregistrer en base de données sessionStripe, utile pour recuperer le devis plus tard
        $reservation->setStripeSessionId($checkout_session->id);
        $this->em->flush();

        // rediriger vers page de paiement hebergé sur stripe
        return $this->redirect($checkout_session->url);
    }

    /**
     * @Route("/espaceclient/paiement-solde/succes/{stripeSessionId}", name="payement_sold_success", methods={"GET","POST"})
     */
    public function payementSoldeSuccess(Request $request, $stripeSessionId)
    {
        $reservation = $this->reservRepo->findOneBy(['stripeSessionId' => $stripeSessionId]);

        // dd($reservation, $reservation->getClient(), $this->getUser());

        //securité en cas de session non valide
        if (!$reservation || $reservation->getClient() != $this->getUser()) {
            $this->flashy->error("La réservation n'existe pas");
            return $this->redirectToRoute('espaceClient_index');
        }

        $sommePaiement = $reservation->getPrix() - $reservation->getSommePaiements();
        if ($reservation->getPrix() ==  $reservation->getSommePaiements()) {
            $sommePaiement = $reservation->getSommePaiements();
        }

        //enregistrer paiement dans table paiement
        $paiement = new Paiement;

        $paiement->setMontant($sommePaiement);
        $paiement->setReservation($reservation);
        $paiement->setStripeSessionId($stripeSessionId);
        $paiement->setDatePaiement($this->dateHelper->dateNow());
        $paiement->setClient($this->reservRepo->findOneBy(['stripeSessionId' => $stripeSessionId])->getClient());
        $paiement->setMotif("Réservation");
        $paiement->setModePaiement($this->modePaiementRepo->findOneBy(['libelle' => 'CARTE BANCAIRE']));
        $this->em->persist($paiement);
        $this->em->flush();
        //envoi de mail client
        $contentMail = "Bonjour, Le solde concernant votre réservation numero " . $reservation->getReference() . "d'un montant de " . $sommePaiement . " € a été payé avec succès ";
        $this->mail->send($reservation->getClient()->getMail(), $reservation->getClient()->getNom(), "Confirmation payement de solde", $contentMail);

        //vider session validation paiement 
        $this->validationSession->removeValidationSession();
        return $this->render('client/paiement/solde/success.html.twig', [
            "reservation" => $reservation,
            "sommePaiement" => $sommePaiement
        ]);
    }

    /**
     * @Route("/espaceclient/paiement-solde/echec/{stripeSessionId}", name="payement_sold_fail", methods={"GET","POST"})
     */
    public function payementSoldeFail(Request $request, $stripeSessionId)
    {
        $reservation = $this->reservRepo->findOneBy(['stripeSessionId' => $stripeSessionId]);
        return $this->render('client/paiement/solde/fail.html.twig', [
            'reservation' => $reservation
        ]);
    }
}
