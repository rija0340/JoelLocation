<?php

namespace App\Controller\Client;

use App\Classe\ReserverDevis;
use App\Entity\Devis;
use App\Service\DateHelper;
use App\Form\ClientInfoType;
use App\Service\TarifsHelper;
use App\Repository\DevisRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EmailManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ValidationDevisController extends AbstractController
{

    private $reservationRepo;
    private $flashy;
    private $devisRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $em;
    private $reserverDevis;
    private $emailManagerService;

    public function __construct(
        ReservationRepository $reservationRepo,
        DevisRepository $devisRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        FlashyNotifier $flashy,
        EntityManagerInterface $em,
        ReserverDevis $reserverDevis,
        EmailManagerService $emailManagerService


    ) {
        $this->reservationRepo = $reservationRepo;
        $this->devisRepo = $devisRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->flashy = $flashy;
        $this->em = $em;
        $this->reserverDevis = $reserverDevis;
        $this->emailManagerService = $emailManagerService;
    }

    /**
     * @Route("/espaceclient/validation/infos-client/{id}", name="validation_step3", methods={"GET","POST"})
     */
    public function step3infosClient(Request $request, Devis $devis,ParameterBagInterface $params): Response
    {
        $garanties = $request->query->get('garanties');
        
        // For guest bookings, we don't check if the devis belongs to the current user
        // since guests don't have accounts yet
        
        $client = $this->getUser();
        // Check if this is a guest booking
        $isGuestBooking = ($client === null);
        
        // For guest bookings, we'll create a temporary user object to populate the form with
        if ($isGuestBooking) {
            $client = new \App\Entity\User(); // Create a temporary user object for the form
            $listeDevis = []; // No devis to list for guest
        } else {
            $listeDevis = $this->devisRepo->findBy(['client' => $client]);
        }

        $formClient = $this->createForm(ClientInfoType::class, $client);

        $formClient->handleRequest($request);

        if ($formClient->isSubmitted() && $formClient->isValid()) {
            if ($isGuestBooking) {
                // Store the user data in session for later use during registration
                $session = $request->getSession();
                $session->set('guest_reservation_data', [
                    'nom' => $client->getNom(),
                    'prenom' => $client->getPrenom(),
                    'telephone' => $client->getTelephone(),
                    'mail' => $client->getMail(),
                    'adresse' => $client->getAdresse(),
                    'codePostal' => $client->getCodePostal(),
                    'ville' => $client->getVille(),
                    'pays' => $client->getPays(),
                ]);
                
                // For guest bookings, redirect to registration instead of saving directly
                // Pass the devis id in the route so we can link the reservation after registration
                return $this->redirectToRoute('app_register_guest', ['devis_id' => $devis->getId()]);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($client);
                $entityManager->flush();
                //store the value of the paiement
                // $devis->setPayementPercentage(intval($request->get('modePaiement')));
                $this->em->flush();

                $refDevis = $devis->getNumero();

                //si la reservation n'existe pas encore en passe au paiement
                if (count($this->reservationRepo->findBy(['numDevis' => $devis->getId()])) == 0) {
                    // redirection vers un autre controller pour le paiement
                    return $this->redirectToRoute('paiementStripe', ['refDevis' => $refDevis]);

                    $this->reserverDevis->reserver($devis, "null", true);
                    $this->flashy->success("Devis transformé en réservation");

                    $this->emailManagerService->sendDevis($request, $devis);

                    return $this->redirectToRoute('client_reservations');
                } else {
                    return $this->redirectToRoute('validation_step3', ['id' => $devis->getId()]);
                }
            }
        }

        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule());
        $duree = $this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour());
        
        // For guest booking, we skip the client comparison check
        if ($isGuestBooking || $devis->getClient() == $client) {
            return $this->render('client2/reservation/validation/step3infosClient.html.twig', [
                'devis' => $devis,
                'formClient' => $formClient->createView(),
                'tarifVehicule' => $tarifVehicule,
                'duree' => $duree,
                'prixConductSuppl' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
                'isGuestBooking' => $isGuestBooking
            ]);
        } else {
            return $this->render('client/reservation/validation/error.html.twig');
        }
    }

    /**
     * @Route("/espaceclient/paiement/{devisID}", name="step4paiement", methods={"GET","POST"})
     */
    public function step4paiement(Request $request, $devisID): Response
    {

        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $listeDevis = $this->devisRepo->findBy(['client' => $client]);
        $devis = $this->devisRepo->find($devisID);

        if ($devis->getClient() == $client) {
            return $this->render('client/reservation/validation/step4paiement.html.twig', [
                'devis' => $devis,
            ]);
        } else {
            return $this->render('client/reservation/validation/error.html.twig');
        }
    }

    /**
     * @Route("/espaceclient/envoi-RIB/{devisID}", name="step4envoiRIB", methods={"GET","POST"})
     */
    public function step4envoiRIB(Request $request, $devisID): Response
    {

        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }

        dd('envoyer RIB Joellocation par email');
        // $listeDevis = $this->devisRepo->findBy(['client' => $client]);
        // $devis = $this->devisRepo->find($devisID);

        // if ($devis->getClient() == $client) {
        //     return $this->render('client/reservation/validation/step4paiement.html.twig', [
        //         'devis' => $devis,
        //     ]);
        // } else {
        //     return $this->render('client/reservation/validation/error.html.twig');
        // }
    }

    //envoi RIB par email -> 
}
