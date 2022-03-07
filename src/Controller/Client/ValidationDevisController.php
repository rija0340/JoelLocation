<?php

namespace App\Controller\Client;

use App\Entity\Devis;
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
use Doctrine\ORM\EntityManagerInterface;
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
    private $em;

    public function __construct(
        ReservationRepository $reservationRepo,
        DevisRepository $devisRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        VehiculeRepository $vehiculeRepo,
        FlashyNotifier $flashy,
        ValidationReservationClientSession $validationSession,
        EntityManagerInterface $em


    )
    {
        $this->reservationRepo = $reservationRepo;
        $this->devisRepo = $devisRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->optionsRepo = $optionsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->flashy = $flashy;
        $this->validationSession = $validationSession;
        $this->em = $em;
    }

    /**
     * @Route("/espaceclient/validation/options-garanties/{id}", name="validation_step2", methods={"GET","POST"})
     */
    public function step2OptionsGaranties(Request $request, Devis $devis): Response
    {

        // $devisID = $request->request->get('reservID');

        // if ($devisID == null) {
        //     $devisID = $request->request->get('devisID');
        // }

        // $devis = $this->devisRepo->find($devisID);
        if ($devis->getClient() != $this->getUser()) {
            $this->flashy->error("Le devis n'existe pas");
            return $this->redirectToRoute('espaceClient_index');
        }

        $garanties = $this->garantiesRepo->findAll();
        $options = $this->optionsRepo->findAll();

        $form = $this->createForm(ValidationOptionsGarantiesType::class, $devis);
        $form->handleRequest($request);

        //serializer options et garanties de devis
        $dataOptions = [];
        foreach ($devis->getOptions() as $key => $option) {
            $dataOptions[$key]['id'] = $option->getId();
            $dataOptions[$key]['appelation'] = $option->getAppelation();
            $dataOptions[$key]['description'] = $option->getDescription();
            $dataOptions[$key]['type'] = $option->getType();
            $dataOptions[$key]['prix'] = $option->getPrix();
        }

        $dataGaranties = [];
        foreach ($devis->getGaranties() as $key => $garantie) {
            $dataGaranties[$key]['id'] = $garantie->getId();
            $dataGaranties[$key]['appelation'] = $garantie->getAppelation();
            $dataGaranties[$key]['description'] = $garantie->getDescription();
            $dataGaranties[$key]['prix'] = $garantie->getPrix();
        }

        $allOptions = [];
        foreach ($this->optionsRepo->findAll() as $key => $option) {
            $allOptions[$key]['id'] = $option->getId();
            $allOptions[$key]['appelation'] = $option->getAppelation();
            $allOptions[$key]['description'] = $option->getDescription();
            $allOptions[$key]['prix'] = $option->getPrix();
            $allOptions[$key]['type'] = $option->getType();
        }


        $allGaranties = [];
        foreach ($this->garantiesRepo->findAll() as $key => $garantie) {
            $allGaranties[$key]['id'] = $garantie->getId();
            $allGaranties[$key]['appelation'] = $garantie->getAppelation();
            $allGaranties[$key]['description'] = $garantie->getDescription();
            $allGaranties[$key]['prix'] = $garantie->getPrix();
        }

        if ($request->get('editedOptionsGaranties') == "true") {

            $checkboxOptions = $request->get("checkboxOptions");
            $checkboxGaranties = $request->get("checkboxGaranties");

            if ($checkboxOptions != []) {
                // tous enlever et puis entrer tous les options
                foreach ($devis->getOptions() as $option) {
                    $devis->removeOption($option);
                }
                for ($i = 0; $i < count($checkboxOptions); $i++) {
                    $devis->addOption($this->optionsRepo->find($checkboxOptions[$i]));
                }
                $this->em->flush();
            } else {
                // si il y a des options, les enlever
                if (count($devis->getOptions()) > 0) {
                    foreach ($devis->getOptions() as $option) {
                        $devis->removeOption($option);
                    }
                }
                $this->em->flush();
            }

            if ($checkboxGaranties != []) {
                // tous enlever et puis entrer tous les garanties
                foreach ($devis->getGaranties() as $garantie) {
                    $devis->removeGaranty($garantie);
                }
                for ($i = 0; $i < count($checkboxGaranties); $i++) {
                    $devis->addGaranty($this->garantiesRepo->find($checkboxGaranties[$i]));
                }
                $this->em->flush();
            } else {
                // si il y a des garanties, les enlever
                if (count($devis->getGaranties()) > 0) {
                    foreach ($devis->getGaranties() as $garantie) {
                        $devis->removeGaranty($garantie);
                    }
                }
                $this->em->flush();
            }
            $devis->setPrixGaranties($this->tarifsHelper->sommeTarifsGaranties($devis->getGaranties()));
            $devis->setPrixOptions($this->tarifsHelper->sommeTarifsOptions($devis->getOptions()));
            $devis->setPrix($devis->getTarifVehicule() + $devis->getPrixOptions() + $devis->getPrixGaranties());

            $this->em->flush();
            return $this->redirectToRoute('validation_step3', ['id' => $devis->getId()]);
        }

        // if ($form->isSubmitted() && $form->isValid()) {

        //     $devis->setPrix($this->tarifsHelper->sommeTarifsGaranties($devis->getGaranties()) + $this->tarifsHelper->sommeTarifsOptions($devis->getOptions()) + $devis->getTarifVehicule());
        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->persist($devis);
        //     $entityManager->flush();

        //     return $this->redirectToRoute('validation_step3', ['devisID' => $devisID]);
        // }

        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule());
        $duree = $this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour());

        return $this->render('client/reservation/validation/step2OptionsGaranties.html.twig', [

            'garanties' => $garanties,
            'tarifVehicule' => $tarifVehicule,
            'duree' => $duree,
            'options' => $options,
            'devis' => $devis,
            'form' => $form->createView(),
            'dataOptions' => $dataOptions,
            'dataGaranties' => $dataGaranties,
            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties,
        ]);
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
//            $this->validationSession->addModePaiment();
            $devis->setPayementPercentage(intval($request->get('modePaiement')));
            $this->em->flush();

            $refDevis = $devis->getNumero();

            //si la reservation n'existe pas encore en passe au paiement
            if (count($this->reservationRepo->findBy(['numDevis' => $devis->getId()])) == 0) {
                //redirection vers un autre controller pour le paiement
                return $this->redirectToRoute('paiementStripe', ['refDevis' => $refDevis]);
            } else {
                return $this->redirectToRoute('validation_step3', ['id' => $devis->getId()]);
            }
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
