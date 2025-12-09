<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Service\PdfGenerationService;
use App\Service\DateHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\DevisRepository;
use App\Repository\ReservationRepository;
use App\Repository\AnnulationReservationRepository;
use App\Service\TarifsHelper;

class PdfGenerationController extends AbstractController
{
    private $pdfGenerationService;
    private $devisRepo;
    private $reservationRepo;
    private $annulationReservationRepo;
    private $tarifsHelper;
    private $dateHelper;

    public function __construct(
        TarifsHelper $tarifsHelper,
        PdfGenerationService $pdfGenerationService,
        DevisRepository $devisRepo,
        ReservationRepository $reservationRepo,
        AnnulationReservationRepository $annulationReservationRepository,
        DateHelper $dateHelper
    ) {
        $this->pdfGenerationService = $pdfGenerationService;
        $this->devisRepo = $devisRepo;
        $this->reservationRepo = $reservationRepo;
        $this->annulationReservationRepo = $annulationReservationRepository;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @Route("/generer/devis-pdf/{hashedId}", name="devis_pdf", methods={"GET"})
     */
    public function devisPDF($hashedId): Response
    {
        $devis = $this->devisRepo->findByHashedId($hashedId);
        $pdfData = $this->pdfGenerationService->prepareDevisPdfData($devis);

        return $this->pdfGenerationService->generatePdfStream(
            $pdfData['html'],
            $pdfData['filename'],
            false
        );
    }

    /**
     * @Route("generer/contrat-pdf/{hashedId}", name="contrat_pdf", methods={"GET"})
     */
    public function pdfcontrat($hashedId): Response
    {
        $reservation = $this->reservationRepo->findByHashedId($hashedId);
        $pdfData = $this->pdfGenerationService->prepareContratPdfData($reservation);

        return $this->pdfGenerationService->generatePdfStream(
            $pdfData['html'],
            $pdfData['filename'],
            true
        );
    }

    /**
     * @Route("/generer/facture-pdf/{hashedId}", name="facture_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function facturePDF($hashedId): Response
    {
        $reservation = $this->reservationRepo->findByHashedId($hashedId);
        $pdfData = $this->pdfGenerationService->prepareFacturePdfData($reservation);

        return $this->pdfGenerationService->generatePdfStream(
            $pdfData['html'],
            $pdfData['filename'],
            true
        );
    }

    /**
     * @Route("/generer/avoir-pdf/{hashedId}", name="avoir_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function avoirPDF($hashedId): Response
    {
        $reservation = $this->reservationRepo->findByHashedId($hashedId);
        $annulationEntity = $this->annulationReservationRepo->findOneBy(['reservation' => $reservation]);

        $pdfData = $this->pdfGenerationService->prepareAvoirPdfData($reservation, $annulationEntity);

        return $this->pdfGenerationService->generatePdfStream(
            $pdfData['html'],
            $pdfData['filename'],
            true
        );
    }

    /**
     * @Route("/generer/facture-devis-pdf/{id}", name="facture_devis_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function factureDevisPDF(Devis $devis): Response
    {
        $pdfData = $this->pdfGenerationService->prepareFactureDevisPdfData($devis);

        return $this->pdfGenerationService->generatePdfStream(
            $pdfData['html'],
            $pdfData['filename'],
            true
        );
    }

    /**
     * @Route("/preview/devis-pdf", name="preview_devis_pdf", methods={"GET"})
     */
    public function previewDevisPDF(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $devis = $this->createMockDevisForPreview();

        // Calculate values needed for the template
        $taxRate = $this->tarifsHelper->getTaxe();
        $taxMultiplier = 1 + $taxRate;

        $tarifVehiculeTTC = $devis->getTarifVehicule();
        $tarifVehiculeHT = $tarifVehiculeTTC / $taxMultiplier;

        $duree = $devis->getDuree();
        $prixUnitVehiculeHT = ($duree > 0) ? ($tarifVehiculeHT / $duree) : 0;

        $prixConductTTC = $this->tarifsHelper->getPrixConducteurSupplementaire();
        $prixConductHT = $prixConductTTC / $taxMultiplier;

        $prixTotalTTC = $devis->getPrix();
        $prixTotalHT = $prixTotalTTC / $taxMultiplier;
        $taxAmount = $prixTotalTTC - $prixTotalHT;

        // Calculate HT prices for options
        $optionsHT = [];
        foreach ($devis->getDevisOptions() as $option) {
            $optionsHT[$option->getId()] = [
                'ht_price' => ($option->getOpt()->getPrix() * $option->getQuantity()) / $taxMultiplier
            ];
        }

        // Calculate HT prices for garanties
        $garantiesHT = [];
        foreach ($devis->getGaranties() as $garantie) {
            $garantiesHT[$garantie->getId()] = [
                'ht_price' => $garantie->getPrix() / $taxMultiplier
            ];
        }

        return $this->render('pdf_generation/devis.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'devis' => $devis,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTotalHT' => $prixTotalHT,
            'taxAmount' => $taxAmount,
            'taxRate' => $taxRate,
            'taxMultiplier' => $taxMultiplier,
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'tarifVehiculeHT' => $tarifVehiculeHT,
            'prixUnitVehiculeHT' => $prixUnitVehiculeHT,
            'prixConductTTC' => $prixConductTTC,
            'prixConductHT' => $prixConductHT,
            'optionsHT' => $optionsHT,
            'garantiesHT' => $garantiesHT,
            'isPreview' => true
        ]);
    }

    /**
     * @Route("/preview/contrat-pdf", name="preview_contrat_pdf", methods={"GET"})
     */
    public function previewContratPDF(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $imageData['vehicule'] = $this->pdfGenerationService->loadVehicleImage();

        $reservation = $this->createMockReservationForPreview();

        // construction de tableau pour options
        $allOptions = [];
        foreach ($reservation->getOptions() as $option) {
            $allOptions[$option->getAppelation()]['appelation'] = $option->getAppelation();
            $allOptions[$option->getAppelation()]['prix'] = $option->getPrix();
        }

        // construction de tableau de tableaux pour garanties
        $allGaranties = [];
        foreach ($reservation->getGaranties() as $garantie) {
            $allGaranties[$garantie->getAppelation()]['appelation'] = $garantie->getAppelation();
            $allGaranties[$garantie->getAppelation()]['prix'] = $garantie->getPrix();
        }

        // frais supplémentaires
        $totalFraisSupplTTC = 0;
        foreach ($reservation->getFraisSupplResas() as $fraisSuppl) {
            $totalFraisSupplTTC += $fraisSuppl->getTotalTTC();
        }

        $totalTTC = $reservation->getPrix() + $totalFraisSupplTTC;
        $sommePaiements = $reservation->getSommePaiements();
        $restePayerTTC = ($reservation->getPrix() + $totalFraisSupplTTC) - $sommePaiements;

        return $this->render('pdf_generation/contrat.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'devis' => $this->devisRepo->find(intval($reservation->getNumDevis())),
            'vehicule' => $imageData['vehicule'],
            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties,
            'sommePaiements' => $sommePaiements,
            'totalTTC' => $totalTTC,
            'restePayerTTC' => $restePayerTTC,
            'totalFraisSupplTTC' => $totalFraisSupplTTC,
            'numContrat' => $this->pdfGenerationService->getNumFacture($reservation, "CO"),
            'isPreview' => true
        ]);
    }

    /**
     * @Route("/preview/facture-pdf", name="preview_facture_pdf", methods={"GET"})
     */
    public function previewFacturePDF(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $reservation = $this->createMockReservationForPreview();

        $createdAt = $this->dateHelper->dateNow();
        $devis = $this->devisRepo->find(intval($reservation->getNumDevis()));

        $sommeFraisTotalHT = 0;
        foreach ($reservation->getFraisSupplResas() as $resa) {
            $sommeFraisTotalHT += $resa->getTotalHT();
        }

        $prixFraisSupplHT = 0;
        foreach ($reservation->getFraisSupplResas() as $key => $value) {
            $prixFraisSupplHT += $value->getTotalHT();
        }

        $tarifVehiculeTTC = $reservation->getTarifVehicule();

        $prixHT = $this->tarifsHelper->calculTarifHTfromTTC($reservation->getPrix());
        $prixTaxe = $this->tarifsHelper->calculTaxeFromHT($prixHT);
        $prixUnitHT = $prixHT / $reservation->getDuree();
        $prixTTC = $prixTaxe + $prixHT;

        $prixTotalHT = $prixHT + $sommeFraisTotalHT;

        $prixTaxeTotal = $this->tarifsHelper->calculTaxeFromHT($prixTotalHT);
        $prixTotalTTC = $prixTotalHT + $prixTaxeTotal;

        return $this->render('pdf_generation/facture.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->pdfGenerationService->getNumFacture($reservation, 'FA'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'sommeFraisTotalHT' => $sommeFraisTotalHT,
            'prixFraisSupplHT' => $prixFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxe' => $prixTaxe,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $this->tarifsHelper->getTaxe(),
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'isPreview' => true
        ]);
    }

    /**
     * @Route("/preview/avoir-pdf", name="preview_avoir_pdf", methods={"GET"})
     */
    public function previewAvoirPDF(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $reservation = $this->createMockReservationForPreview();
        $createdAt = $this->dateHelper->dateNow();
        $devis = $this->devisRepo->find(intval($reservation->getNumDevis()));

        $sommeFraisTotalHT = 0;
        foreach ($reservation->getFraisSupplResas() as $resa) {
            $sommeFraisTotalHT += $resa->getTotalHT();
        }

        $prixFraisSupplHT = 0;
        foreach ($reservation->getFraisSupplResas() as $key => $value) {
            $prixFraisSupplHT += $value->getTotalHT();
        }

        $tarifVehiculeTTC = $reservation->getTarifVehicule();

        $prixHT = $this->tarifsHelper->calculTarifHTfromTTC($reservation->getPrix());
        $prixTaxe = $this->tarifsHelper->calculTaxeFromHT($prixHT);

        $durree = $reservation->getDuree();

        $prixUnitHT = $prixHT / $durree;
        $prixTTC = $prixTaxe + $prixHT;

        $prixTotalHT = $prixHT + $sommeFraisTotalHT;

        $prixTaxeTotal = $this->tarifsHelper->calculTaxeFromHT($prixTotalHT);
        $prixTotalTTC = $prixTotalHT + $prixTaxeTotal;

        // Create mock annulation entity
        $annulationEntity = new \App\Entity\AnnulationReservation();
        $reflection = new \ReflectionClass($annulationEntity);
        $property = $reflection->getProperty('montantAvoir');
        $property->setAccessible(true);
        $property->setValue($annulationEntity, 200.00); // Set a mock value

        return $this->render('pdf_generation/avoir.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->pdfGenerationService->getNumFacture($reservation, 'AV'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'sommeFraisTotalHT' => $sommeFraisTotalHT,
            'prixFraisSupplHT' => $prixFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxe' => $prixTaxe,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $this->tarifsHelper->getTaxe(),
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'montantAvoir' => $annulationEntity->getMontantAvoir(),
            'isPreview' => true
        ]);
    }

    /**
     * @Route("/preview/facture-devis-pdf", name="preview_facture_devis_pdf", methods={"GET"})
     */
    public function previewFactureDevisPDF(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $devis = $this->createMockDevisForPreview();

        $createdAt = $this->dateHelper->dateNow();

        $tarifVehiculeTTC = $devis->getTarifVehicule();

        $prixHT = $this->tarifsHelper->calculTarifHTfromTTC($devis->getPrix());
        $prixTaxe = $this->tarifsHelper->calculTaxeFromHT($prixHT);
        $prixUnitHT = $prixHT / $devis->getDuree();
        $prixTTC = $prixTaxe + $prixHT;

        $prixTotalHT = $prixHT;

        $prixTaxeTotal = $this->tarifsHelper->calculTaxeFromHT($prixTotalHT);
        $prixTotalTTC = $prixTotalHT + $prixTaxeTotal;

        return $this->render('pdf_generation/facture_devis.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'devis' => $devis,
            'createdAt' => $createdAt,
            'numeroFacture' => $this->pdfGenerationService->getNumFacture($devis, 'FA'),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxe' => $prixTaxe,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $this->tarifsHelper->getTaxe(),
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'isPreview' => true
        ]);
    }

    /**
     * @Route("/preview/pdf-templates", name="preview_pdf_templates_list", methods={"GET"})
     */
    public function listPdfTemplates(): Response
    {
        $templates = [
            'devis' => [
                'name' => 'Devis',
                'route' => 'preview_devis_pdf',
                'description' => 'Preview devis template'
            ],
            'contrat' => [
                'name' => 'Contrat',
                'route' => 'preview_contrat_pdf',
                'description' => 'Preview contrat template'
            ],
            'facture' => [
                'name' => 'Facture',
                'route' => 'preview_facture_pdf',
                'description' => 'Preview facture template'
            ],
            'avoir' => [
                'name' => 'Avoir',
                'route' => 'preview_avoir_pdf',
                'description' => 'Preview avoir template'
            ],
            'facture_devis' => [
                'name' => 'Facture Devis',
                'route' => 'preview_facture_devis_pdf',
                'description' => 'Preview facture for devis template'
            ]
        ];

        return $this->render('pdf_generation/templates_list.html.twig', [
            'templates' => $templates
        ]);
    }

    // Helper methods to create mock data
    private function createMockDevisForPreview()
    {
        $devis = new Devis();

        // Use reflection to set ID for the devis since it doesn't have a setId method
        $reflection = new \ReflectionClass($devis);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($devis, 1);

        $devis->setNumero('DEV-001');
        $devis->setDateDepart(new \DateTime());
        $devis->setDateRetour((new \DateTime())->modify('+3 days'));
        $devis->setPrix(250.00);
        $devis->setTarifVehicule(200.00);
        $devis->setDuree(3);

        // Create mock client
        $client = $this->createMockClient();
        $devis->setClient($client);

        // Create mock vehicle
        $vehicule = $this->createMockVehicule();
        $devis->setVehicule($vehicule);

        return $devis;
    }

    private function createMockReservationForPreview(): \App\Entity\Reservation
    {
        $reservation = new \App\Entity\Reservation();

        // Use reflection to set ID for the reservation since it doesn't have a setId method
        $reflection = new \ReflectionClass($reservation);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($reservation, 1);

        $reservation->setReference('RES-001');
        $reservation->setDateDebut(new \DateTime());
        $reservation->setDateFin((new \DateTime())->modify('+3 days'));
        $reservation->setDateReservation(new \DateTime());
        $reservation->setPrix(500.00);
        $reservation->setTarifVehicule(400.00);
        $reservation->setNumDevis(1);
        $reservation->setDuree(3);

        // Create mock client
        $client = $this->createMockClient();
        $reservation->setClient($client);

        // Create mock vehicle
        $vehicule = $this->createMockVehicule();
        $reservation->setVehicule($vehicule);

        // Use reflection to set sommePaiements directly since it's calculated and we don't have a setter
        $reflection = new \ReflectionClass($reservation);
        $property = $reflection->getProperty('sommePaiements');
        $property->setAccessible(true);
        $property->setValue($reservation, 200.00); // Set a mock value

        return $reservation;
    }

    private function createMockClient(): \App\Entity\User
    {
        $user = new \App\Entity\User();
        $user->setId(1);
        $user->setNom('Test');
        $user->setPrenom('Client');
        $user->setMail('test@client.com');
        return $user;
    }

    private function createMockVehicule(): \App\Entity\Vehicule
    {
        $vehicule = new \App\Entity\Vehicule();
        $marque = new \App\Entity\Marque();
        $modele = new \App\Entity\Modele();
        $marque->setLibelle('Peugeot');
        $modele->setLibelle('208');

        // Use reflection to set ID for the vehicle since it doesn't have a setId method
        $reflection = new \ReflectionClass($vehicule);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($vehicule, 1);

        $vehicule->setMarque($marque);
        $vehicule->setModele($modele);
        return $vehicule;
    }
}