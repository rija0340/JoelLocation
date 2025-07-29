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
use App\Service\SymfonyMailerHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ValidationDevisController extends AbstractController
{

    private $reservationRepo;
    private $flashy;
    private $devisRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $em;
    private $reserverDevis;
    private $symfonyMailerHelper;

    public function __construct(
        ReservationRepository $reservationRepo,
        DevisRepository $devisRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        FlashyNotifier $flashy,
        EntityManagerInterface $em,
        ReserverDevis $reserverDevis,
        SymfonyMailerHelper $symfonyMailerHelper


    ) {
        $this->reservationRepo = $reservationRepo;
        $this->devisRepo = $devisRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->flashy = $flashy;
        $this->em = $em;
        $this->reserverDevis = $reserverDevis;
        $this->symfonyMailerHelper = $symfonyMailerHelper;
    }

    /**
     * @Route("/espaceclient/validation/infos-client/{id}", name="validation_step3", methods={"GET","POST"})
     */
    public function step3infosClient(Request $request, Devis $devis): Response
    {
        $garanties = $request->query->get('garanties');
        if ($devis->getClient() != $this->getUser()) {
            return $this->redirectToRoute('espaceClient_index');
        }

        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $listeDevis = $this->devisRepo->findBy(['client' => $client]);

        $formClient = $this->createForm(ClientInfoType::class, $client);

        $formClient->handleRequest($request);

        if ($formClient->isSubmitted() && $formClient->isValid()) {

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

                $this->symfonyMailerHelper->sendDevis($request, $devis);

                return $this->redirectToRoute('client_reservations');
            } else {
                return $this->redirectToRoute('validation_step3', ['id' => $devis->getId()]);
            }
        }

        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule());
        $duree = $this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour());
        if ($devis->getClient() == $client) {
            return $this->render('client2/reservation/validation/step3infosClient.html.twig', [
                'devis' => $devis,
                'formClient' => $formClient->createView(),
                'tarifVehicule' => $tarifVehicule,
                'duree' => $duree,
                'prixConductSuppl' => $this->tarifsHelper->getPrixConducteurSupplementaire()
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
