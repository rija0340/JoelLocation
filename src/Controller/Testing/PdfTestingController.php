<?php

namespace App\Controller\Testing;

use App\Entity\Devis;
use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Vehicule;
use App\Entity\Marque;
use App\Entity\Modele;
use App\Entity\AnnulationReservation;
use App\Service\PdfGenerationService;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/testing/pdf")
 * @IsGranted("ROLE_SUPER_ADMIN")
 */
class PdfTestingController extends AbstractController
{
    private $pdfGenerationService;
    private $tarifsHelper;
    private $dateHelper;

    public function __construct(
        PdfGenerationService $pdfGenerationService,
        TarifsHelper $tarifsHelper,
        DateHelper $dateHelper
    ) {
        $this->pdfGenerationService = $pdfGenerationService;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @Route("/", name="testing_pdf_index")
     */
    public function index(): Response
    {
        $templates = [
            'devis' => [
                'id' => 'devis',
                'name' => 'Devis',
                'description' => 'Template pour les devis clients',
                'icon' => 'fa-file-text-o',
                'color' => 'bg-info'
            ],
            'contrat' => [
                'id' => 'contrat',
                'name' => 'Contrat',
                'description' => 'Contrat de location',
                'icon' => 'fa-file-text',
                'color' => 'bg-primary'
            ],
            'facture' => [
                'id' => 'facture',
                'name' => 'Facture',
                'description' => 'Facture de réservation',
                'icon' => 'fa-money',
                'color' => 'bg-success'
            ],
            'avoir' => [
                'id' => 'avoir',
                'name' => 'Avoir',
                'description' => 'Avoir sur facture',
                'icon' => 'fa-undo',
                'color' => 'bg-warning'
            ],
            'facture_devis' => [
                'id' => 'facture_devis',
                'name' => 'Facture Devis',
                'description' => 'Facture basée sur un devis',
                'icon' => 'fa-file-text',
                'color' => 'bg-secondary'
            ]
        ];

        return $this->render('admin/testing/pdf/index.html.twig', [
            'templates' => $templates
        ]);
    }

    /**
     * @Route("/preview/{type}", name="testing_pdf_preview")
     */
    public function preview(string $type): Response
    {
        switch ($type) {
            case 'devis':
                return $this->previewDevis();
            case 'contrat':
                return $this->previewContrat();
            case 'facture':
                return $this->previewFacture();
            case 'avoir':
                return $this->previewAvoir();
            case 'facture_devis':
                return $this->previewFactureDevis();
            default:
                throw $this->createNotFoundException('Template type not found');
        }
    }

    private function previewDevis(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $devis = $this->createMockDevis();

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

    private function previewContrat(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $imageData['vehicule'] = $this->pdfGenerationService->loadVehicleImage();

        $reservation = $this->createMockReservation();

        // Create a mock devis for the contract
        $devis = $this->createMockDevis();

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
            'devis' => null,
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

    private function previewFacture(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $reservation = $this->createMockReservation();

        $createdAt = $this->dateHelper->dateNow();
        $devis = $this->createMockDevis();

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

    private function previewAvoir(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $reservation = $this->createMockReservation();
        $createdAt = $this->dateHelper->dateNow();
        $devis = $this->createMockDevis();

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
        $annulationEntity = new AnnulationReservation();
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

    private function previewFactureDevis(): Response
    {
        $imageData = $this->pdfGenerationService->loadImageData();
        $devis = $this->createMockDevis();

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

    // Helper methods to create mock data
    private function createMockDevis()
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

    private function createMockReservation(): Reservation
    {
        $reservation = new Reservation();

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

    private function createMockClient(): User
    {
        $user = new User();
        $user->setId(1);
        $user->setNom('Test');
        $user->setPrenom('Client');
        $user->setMail('test@client.com');
        return $user;
    }

    private function createMockVehicule(): Vehicule
    {
        $vehicule = new Vehicule();
        $marque = new Marque();
        $modele = new Modele();
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
