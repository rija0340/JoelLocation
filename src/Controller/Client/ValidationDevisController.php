<?php

namespace App\Controller\Client;

use App\Service\DateHelper;
use App\Form\ClientInfoType;
use App\Service\TarifsHelper;
use App\Repository\DevisRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use App\Form\ValidationOptionsGarantiesType;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Classe\ValidationReservationClientSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ValidationDevisController extends AbstractController
{

    private $reservationRepo;
    private $flashy;
    private $devisRepo;
    private $garantiesRepo;
    private $optionsRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $vehiculeRepo;
    private $validationSession;

    public function __construct(
        ReservationRepository $reservationRepo,
        DevisRepository $devisRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        VehiculeRepository $vehiculeRepo,
        FlashyNotifier $flashy,
        ValidationReservationClientSession $validationSession

    ) {
        $this->reservationRepo = $reservationRepo;
        $this->devisRepo = $devisRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->optionsRepo = $optionsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->flashy = $flashy;
        $this->validationSession = $validationSession;
    }

    /**
     * @Route("/espaceclient/validation/options-garanties", name="validation_step2", methods={"GET","POST"})
     */
    public function step2OptionsGaranties(Request $request): Response
    {

        $devisID = $request->request->get('reservID');

        if ($devisID == null) {
            $devisID = $request->request->get('devisID');
        }

        $devis = $this->devisRepo->find($devisID);
        if (!$devis || $devis->getClient() != $this->getUser()) {
            $this->flashy->error("Le devis n'existe pas");
            return $this->redirectToRoute('espaceClient_index');
        }

        $garanties = $this->garantiesRepo->findAll();
        $options = $this->optionsRepo->findAll();

        $form = $this->createForm(ValidationOptionsGarantiesType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $devis->setPrix($this->tarifsHelper->sommeTarifsGaranties($devis->getGaranties()) + $this->tarifsHelper->sommeTarifsOptions($devis->getOptions()) + $devis->getTarifVehicule());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($devis);
            $entityManager->flush();

            return $this->redirectToRoute('validation_step3', ['devisID' => $devisID]);
        }

        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule());
        $duree = $this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour());

        return $this->render('client/reservation/validation/step2OptionsGaranties.html.twig', [

            'garanties' => $garanties,
            'tarifVehicule' => $tarifVehicule,
            'duree' => $duree,
            'options' => $options,
            'devis' => $devis,
            'form' => $form->createView(),
            'devisID' => $devisID
        ]);
    }


    /**
     * @Route("/espaceclient/validation/infos-client/{devisID}", name="validation_step3", methods={"GET","POST"})
     */
    public function step3infosClient(Request $request, $devisID): Response
    {
        $garanties = $request->query->get('garanties');
        $devis = $this->devisRepo->find($devisID);
        if (!$devis || $devis->getClient() != $this->getUser()) {
            return $this->redirectToRoute('espaceClient_index');
        }
        // dd($devis->getGaranties());
        // for ($i = 0; $i < count($garanties); $i++) {
        // }

        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $listeDevis = $this->devisRepo->findBy(['client' => $client]);
        $devis = $this->devisRepo->find($devisID);

        $formClient = $this->createForm(ClientInfoType::class, $client);
        // dd($formClient->isSubmitted());
        $formClient->handleRequest($request);

        if ($formClient->isSubmitted() && $formClient->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();
            $this->validationSession->addModePaiment($request->request->get('modePaiement'));

            // return $this->redirectToRoute('step4paiement', ['devisID' => $devisID]);
            //tester stripe
            $refDevis = $devis->getNumero();
            //redirection vers un autre controller
            return $this->redirectToRoute('paiementStripe', ['refDevis' => $refDevis]);
        }

        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule());
        $duree = $this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour());
        if ($devis->getClient() == $client) {
            return $this->render('client/reservation/validation/step3infosClient.html.twig', [
                'devis' => $devis,
                'formClient' => $formClient->createView(),
                'tarifVehicule' => $tarifVehicule,
                'duree' => $duree
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
}
