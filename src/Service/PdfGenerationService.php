<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Snappy\Pdf;
use App\Entity\Devis;
use App\Entity\Reservation;
use App\Entity\AnnulationReservation;
use App\Repository\DevisRepository;
use App\Repository\GarantieRepository;
use App\Repository\OptionsRepository;
use App\Repository\ReservationRepository;
use App\Repository\AnnulationReservationRepository;

class PdfGenerationService
{
    private $datehelper;
    private $reservationRepo;
    private $devisRepo;
    private $optionsRepo;
    private $garantieRepo;
    private $reservationHelper;
    private $tarifsHelper;
    private $annulationReservationRepo;
    private $kernelProjectDir;
    private $twig;

    public function __construct(
        ReservationRepository $reservationRepo,
        AnnulationReservationRepository $annulationReservationRepo,
        DateHelper $datehelper,
        DevisRepository $devisRepo,
        GarantieRepository $garantieRepo,
        OptionsRepository $optionsRepo,
        ReservationHelper $reservationHelper,
        TarifsHelper $tarifsHelper,
        string $kernelProjectDir,
        \Twig\Environment $twig
    ) {
        $this->reservationRepo = $reservationRepo;
        $this->annulationReservationRepo = $annulationReservationRepo;
        $this->datehelper = $datehelper;
        $this->devisRepo = $devisRepo;
        $this->garantieRepo = $garantieRepo;
        $this->optionsRepo = $optionsRepo;
        $this->reservationHelper = $reservationHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->kernelProjectDir = $kernelProjectDir;
        $this->twig = $twig;
    }

    /**
     * Generate Devis PDF
     */
    public function generateDevisPdf(Pdf $knpSnappyPdf, $hashedId): string
    {
        $devis = $this->devisRepo->findByHashedId($hashedId);

        // Prepare PDF data
        $pdfData = $this->prepareDevisPdfData($devis);

        // Render HTML content
        $html = $pdfData['html'];

        // Generate PDF using KnpSnappy
        return $knpSnappyPdf->getOutputFromHtml($html);
    }

    /**
     * Generate Contrat PDF
     */
    public function generateContratPdf(Pdf $knpSnappyPdf, $hashedId): string
    {
        $reservation = $this->reservationRepo->findByHashedId($hashedId);

        // Prepare PDF data
        $pdfData = $this->prepareContratPdfData($reservation);

        // Render HTML content
        $html = $pdfData['html'];

        // Generate PDF using KnpSnappy
        return $knpSnappyPdf->getOutputFromHtml($html);
    }

    /**
     * Generate Facture PDF
     */
    public function generateFacturePdf(Pdf $knpSnappyPdf, $hashedId): string
    {
        $reservation = $this->reservationRepo->findByHashedId($hashedId);

        // Prepare PDF data
        $pdfData = $this->prepareFacturePdfData($reservation);

        // Render HTML content
        $html = $pdfData['html'];

        // Generate PDF using KnpSnappy
        return $knpSnappyPdf->getOutputFromHtml($html);
    }

    /**
     * Generate Avoir PDF
     */
    public function generateAvoirPdf(Pdf $knpSnappyPdf, $hashedId): string
    {
        $reservation = $this->reservationRepo->findByHashedId($hashedId);
        $annulationEntity = $this->annulationReservationRepo->findOneBy(['reservation' => $reservation]);

        // Prepare PDF data
        $pdfData = $this->prepareAvoirPdfData($reservation, $annulationEntity);

        // Render HTML content
        $html = $pdfData['html'];

        // Generate PDF using KnpSnappy
        return $knpSnappyPdf->getOutputFromHtml($html);
    }

    /**
     * Generate Facture Devis PDF
     */
    public function generateFactureDevisPdf(Pdf $knpSnappyPdf, Devis $devis): string
    {
        // Prepare PDF data
        $pdfData = $this->prepareFactureDevisPdfData($devis);

        // Render HTML content
        $html = $pdfData['html'];

        // Generate PDF using KnpSnappy
        return $knpSnappyPdf->getOutputFromHtml($html);
    }

    /**
     * Prepare data for Devis PDF
     */
    public function prepareDevisPdfData(Devis $devis): array
    {
        $imageData = $this->loadImageData();

        $taxRate = $this->tarifsHelper->getTaxe();
        $taxMultiplier = 1 + $taxRate;
        $tarifVehiculeTTC = $devis->getTarifVehicule();
        $prixConductTTC = $this->tarifsHelper->getPrixConducteurSupplementaire();
        $prixTotalTTC = $devis->getPrix();

        // Calculate values needed for the template
        $prixTotalHT = $prixTotalTTC / $taxMultiplier;
        $taxAmount = $prixTotalTTC - $prixTotalHT;
        $tarifVehiculeHT = $tarifVehiculeTTC / $taxMultiplier;
        $prixUnitVehiculeHT = $tarifVehiculeHT / $devis->getDuree();

        $prixConductHT = $prixConductTTC / $taxMultiplier;

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

        $html = $this->renderView('pdf_generation/devis.html.twig', [
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
            'isPreview' => false
        ]);

        return [
            'html' => $html,
            'filename' => 'devis_' . $devis->getNumero() . '.pdf',
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'devis' => $devis
        ];
    }

    /**
     * Prepare data for Contrat PDF
     */
    public function prepareContratPdfData(Reservation $reservation): array
    {
        $imageData = $this->loadImageData();
        $imageData['vehicule'] = $this->loadVehicleImage();

        $options = $reservation->getOptions();
        $allOptions = $this->optionsRepo->findAll();

        //construction de tableau pour options
        $allOptions = [];
        $options = $reservation->getOptions();
        foreach ($options as $option) {
            $allOptions[$option->getAppelation()]['appelation'] = $option->getAppelation();
            $allOptions[$option->getAppelation()]['prix'] = $option->getPrix();
        }

        //construction de tableau de tableaux pour garanties
        $allGaranties = [];
        $garanties = $reservation->getGaranties();
        foreach ($garanties as $garantie) {
            $allGaranties[$garantie->getAppelation()]['appelation'] = $garantie->getAppelation();
            $allGaranties[$garantie->getAppelation()]['prix'] = $garantie->getPrix();
        }

        //total a payer  = prix de la réservation - somme paiements déjà effectués
        $sommePaiements = $reservation->getSommePaiements();

        //frais supplémentaires
        $totalFraisSupplTTC = 0;
        foreach ($reservation->getFraisSupplResas() as $fraisSuppl) {
            $totalFraisSupplTTC += $fraisSuppl->getTotalTTC();
        }

        $totalTTC = $reservation->getPrix() + $totalFraisSupplTTC;
        $restePayerTTC = ($reservation->getPrix() + $totalFraisSupplTTC) - $sommePaiements;

        // Calculate tax values
        $taxRate = $this->tarifsHelper->getTaxe();
        $totalHT = $totalTTC / (1 + $taxRate);
        $taxAmount = $totalTTC - $totalHT;

        // Get contract signatures
        $clientSignature = null;
        $adminSignature = null;
        $clientCheckoutSignature = null;
        $adminCheckoutSignature = null;

        $contracts = $reservation->getContracts();
        $latestContract = null;

        // Find latest contract
        if (count($contracts) > 0) {
            foreach ($contracts as $contract) {
                if (!$latestContract || $contract->getCreatedAt() > $latestContract->getCreatedAt()) {
                    $latestContract = $contract;
                }
            }
        }

        if ($latestContract) {
            foreach ($latestContract->getSignatures() as $signature) {
                $signatureType = $signature->getSignatureType();
                $documentType = $signature->getDocumentType();
                $signatureImage = $signature->getSignatureImage();

                if (!$signatureImage) {
                    continue;
                }

                $img = $signatureImage;
                if (strpos($img, 'data:image') === false) {
                    $img = 'data:image/png;base64,' . $img;
                }

                // Signatures de départ (contract)
                if ($documentType === 'contract' || $documentType === 'checkin') {
                    if ($signatureType === 'client') {
                        $clientSignature = $img;
                    } elseif ($signatureType === 'admin') {
                        $adminSignature = $img;
                    }
                }
                // Signatures de checkout (remise)
                elseif ($documentType === 'checkout') {
                    if ($signatureType === 'client') {
                        $clientCheckoutSignature = $img;
                    } elseif ($signatureType === 'admin') {
                        $adminCheckoutSignature = $img;
                    }
                }
            }
        }

        $html = $this->renderView('pdf_generation/contrat.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'devis' => $this->devisRepo->find(intval($reservation->getNumDevis())),
            'vehicule' => $imageData['vehicule'],
            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties,
            'sommePaiements' => $sommePaiements,
            'totalTTC' => $totalTTC,
            'totalHT' => $totalHT,
            'taxAmount' => $taxAmount,
            'taxRate' => $taxRate,
            'restePayerTTC' => $restePayerTTC,
            'totalFraisSupplTTC' => $totalFraisSupplTTC,
            'numContrat' => $this->getNumFacture($reservation, "CO"),
            'clientSignature' => $clientSignature,
            'adminSignature' => $adminSignature,
            'clientCheckoutSignature' => $clientCheckoutSignature,
            'adminCheckoutSignature' => $adminCheckoutSignature,
            'isPreview' => false
        ]);

        return [
            'html' => $html,
            'filename' => 'contrat_' . $reservation->getReference() . '.pdf',
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'vehicule' => $imageData['vehicule'],
            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties,
            'sommePaiements' => $sommePaiements,
            'totalTTC' => $totalTTC,
            'totalHT' => $totalHT,
            'taxAmount' => $taxAmount,
            'taxRate' => $taxRate,
            'restePayerTTC' => $restePayerTTC,
            'totalFraisSupplTTC' => $totalFraisSupplTTC,
            'numContrat' => $this->getNumFacture($reservation, "CO"),
            'clientSignature' => $clientSignature,
            'adminSignature' => $adminSignature,
            'clientCheckoutSignature' => $clientCheckoutSignature,
            'adminCheckoutSignature' => $adminCheckoutSignature
        ];
    }

    /**
     * Prepare data for Facture PDF
     */
    public function prepareFacturePdfData(Reservation $reservation): array
    {
        $imageData = $this->loadImageData();
        $createdAt = $this->datehelper->dateNow();
        $devis = $this->devisRepo->find(intval($reservation->getNumDevis()));

        // Les frais supplémentaires sont stockés en TTC, on calcule aussi le HT
        $totalFraisSupplTTC = 0;
        $totalFraisSupplHT = 0;
        foreach ($reservation->getFraisSupplResas() as $frais) {
            $totalFraisSupplTTC += $frais->getTotalTTC();
            $totalFraisSupplHT += $frais->getTotalHT();
        }

        $tarifVehiculeTTC = $reservation->getTarifVehicule();

        // Prix de la réservation (sans les frais suppl.) - calcul HT à partir du TTC
        $prixHT = $this->tarifsHelper->calculTarifHTfromTTC($reservation->getPrix());
        $prixTaxe = $this->tarifsHelper->calculTaxeFromHT($prixHT);
        $prixUnitHT = $prixHT / $reservation->getDuree();
        $prixTTC = $reservation->getPrix();

        // Prix total = réservation HT + frais suppl. HT
        $prixTotalHT = $prixHT + $totalFraisSupplHT;
        $prixTaxeTotal = $this->tarifsHelper->calculTaxeFromHT($prixTotalHT);
        $prixTotalTTC = $reservation->getPrix() + $totalFraisSupplTTC;

        // Calculate tax rate
        $taxRate = $this->tarifsHelper->getTaxe();

        $html = $this->renderView('pdf_generation/facture.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->getNumFacture($reservation, 'FA'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'tarifVehiculeHT' => $prixHT, // Same as prixHT since it's just the vehicle part
            'totalFraisSupplTTC' => $totalFraisSupplTTC,
            'totalFraisSupplHT' => $totalFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxe' => $prixTaxe,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $taxRate,
            'taxMultiplier' => 1 + $taxRate,
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'isPreview' => false
        ]);

        return [
            'html' => $html,
            'filename' => 'facture_' . $reservation->getReference() . '.pdf',
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->getNumFacture($reservation, 'FA'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'tarifVehiculeHT' => $prixHT,
            'totalFraisSupplTTC' => $totalFraisSupplTTC,
            'totalFraisSupplHT' => $totalFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxe' => $prixTaxe,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $taxRate,
            'taxMultiplier' => 1 + $taxRate,
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire()
        ];
    }

    /**
     * Prepare data for Avoir PDF
     */
    public function prepareAvoirPdfData(Reservation $reservation, AnnulationReservation $annulationEntity): array
    {
        $imageData = $this->loadImageData();
        $createdAt = $this->datehelper->dateNow();
        $devis = $this->devisRepo->find(intval($reservation->getNumDevis()));

        // Les frais supplémentaires sont stockés en TTC, on calcule aussi le HT
        $prixFraisSupplHT = 0;
        $totalFraisSupplTTC = 0;
        foreach ($reservation->getFraisSupplResas() as $frais) {
            $prixFraisSupplHT += $frais->getTotalHT();
            $totalFraisSupplTTC += $frais->getTotalTTC();
        }

        $tarifVehiculeTTC = $reservation->getTarifVehicule();

        // Prix de la réservation (sans les frais suppl.) - calcul HT à partir du TTC
        $prixHT = $this->tarifsHelper->calculTarifHTfromTTC($reservation->getPrix());
        $prixTaxe = $this->tarifsHelper->calculTaxeFromHT($prixHT);

        $duree = $reservation->getDuree();

        $prixUnitHT = $prixHT / $duree;
        $prixTTC = $reservation->getPrix();

        // Prix total = réservation HT + frais suppl. HT
        $prixTotalHT = $prixHT + $prixFraisSupplHT;
        $prixTaxeTotal = $this->tarifsHelper->calculTaxeFromHT($prixTotalHT);
        $prixTotalTTC = $reservation->getPrix() + $totalFraisSupplTTC;

        // Calculate tax rate
        $taxRate = $this->tarifsHelper->getTaxe();

        $html = $this->renderView('pdf_generation/avoir.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->getNumFacture($reservation, 'AV'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'tarifVehiculeHT' => $prixHT, // Store separately for template use
            'sommeFraisTotalHT' => $prixFraisSupplHT,
            'prixFraisSupplHT' => $prixFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxe' => $prixTaxe,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $taxRate,
            'taxMultiplier' => 1 + $taxRate,
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'montantAvoir' => $annulationEntity->getMontantAvoir(),
            'isPreview' => false
        ]);

        return [
            'html' => $html,
            'filename' => 'avoir_' . $reservation->getReference() . '.pdf',
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->getNumFacture($reservation, 'AV'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'tarifVehiculeHT' => $prixHT,
            'sommeFraisTotalHT' => $prixFraisSupplHT,
            'prixFraisSupplHT' => $prixFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxe' => $prixTaxe,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $taxRate,
            'taxMultiplier' => 1 + $taxRate,
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'montantAvoir' => $annulationEntity->getMontantAvoir()
        ];
    }

    /**
     * Prepare data for Facture Devis PDF
     */
    public function prepareFactureDevisPdfData(Devis $devis): array
    {
        $imageData = $this->loadImageData();
        $createdAt = $this->datehelper->dateNow();

        $tarifVehiculeTTC = $devis->getTarifVehicule();

        $prixHT = $this->tarifsHelper->calculTarifHTfromTTC($devis->getPrix());
        $prixTaxe = $this->tarifsHelper->calculTaxeFromHT($prixHT);
        $prixUnitHT = $prixHT / $devis->getDuree();
        $prixTTC = $prixTaxe + $prixHT;

        $prixTotalHT = $prixHT;

        $prixTaxeTotal = $this->tarifsHelper->calculTaxeFromHT($prixTotalHT);
        $prixTotalTTC = $prixTotalHT + $prixTaxeTotal;

        // Calculate tax rate
        $taxRate = $this->tarifsHelper->getTaxe();

        $html = $this->renderView('pdf_generation/facture_devis.html.twig', [
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'devis' => $devis,
            'createdAt' => $createdAt,
            'numeroFacture' => $this->getNumFacture($devis, 'FA'),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'tarifVehiculeHT' => $prixHT, // Store separately for template use
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxe' => $prixTaxe,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $taxRate,
            'taxMultiplier' => 1 + $taxRate,
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'isPreview' => false
        ]);

        return [
            'html' => $html,
            'filename' => 'facture_' . $devis->getNumero() . '.pdf',
            'logo' => $imageData['logo'],
            'entete' => $imageData['entete'],
            'devis' => $devis,
            'createdAt' => $createdAt,
            'numeroFacture' => $this->getNumFacture($devis, 'FA'),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'tarifVehiculeHT' => $prixHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $taxRate,
            'taxMultiplier' => 1 + $taxRate,
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire()
        ];
    }

    /**
     * Generate PDF stream using Dompdf
     */
    public function generatePdfStream(string $html, string $filename, bool $attachment = true): \Symfony\Component\HttpFoundation\Response
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        return $dompdf->stream($filename, [
            "Attachment" => $attachment,
        ]);
    }

    /**
     * Génère le numéro de facture/contrat/avoir
     * Format: PREFIXAA000XXX où AA = 2 derniers chiffres de l'année, XXX = ID paddé sur 6 chiffres
     */
    public function getNumFacture($entity, string $prefix): string
    {
        $idResa = $entity->getId();
        $createdAt = $this->datehelper->dateNow();
        $yearSuffix = $createdAt->format('y'); // 2 derniers chiffres de l'année

        // Padding de l'ID sur 6 chiffres
        $paddedId = str_pad((string) $idResa, 6, '0', STR_PAD_LEFT);

        return $prefix . $yearSuffix . $paddedId;
    }

    /**
     * Render view helper method
     */
    private function renderView(string $view, array $parameters = []): string
    {
        return $this->twig->render($view, $parameters);
    }

    /**
     * Load image data for PDF generation
     */
    public function loadImageData(): array
    {
        $logoPath = $this->kernelProjectDir . '/public/images';

        // logo joellocation
        $logo = $logoPath . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;

        // en tete joellocation
        $entete = $logoPath . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;

        return [
            'logo' => $logo_src,
            'entete' => $entete_src
        ];
    }

    /**
     * Load vehicle image data
     */
    public function loadVehicleImage(): string
    {
        $logoPath = $this->kernelProjectDir . '/public/images';
        $vehicule = $logoPath . '/contrat/picture_contrat.PNG';
        $vehicule_data = base64_encode(file_get_contents($vehicule));
        return 'data:image/png;base64,' . $vehicule_data;
    }
}