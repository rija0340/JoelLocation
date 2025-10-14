<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Snappy\Pdf;
use App\Entity\Devis;
use App\Entity\Reservation;
use App\Entity\AnnulationReservation;
use App\Repository\DevisRepository;
use App\Repository\GarantieRepository;
use App\Repository\OptionsRepository;
use App\Service\DateHelper;
use App\Repository\ReservationRepository;
use App\Service\ReservationHelper;
use App\Service\TarifsHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\AnnulationReservationRepository;

class PdfController extends AbstractController
{

    private $datehelper;
    private $reservationRepo;
    private $devisRepo;
    private $optionsRepo;
    private $garantieRepo;
    private $reservationHelper;
    private $tarifsHelper;
    private $annulationReservationRepo;
    public function __construct(
        ReservationRepository $reservationRepo,
        AnnulationReservationRepository $annulationReservationRepo,
        DateHelper $datehelper,
        DevisRepository $devisRepo,
        GarantieRepository $garantieRepo,
        OptionsRepository $optionsRepo,
        ReservationHelper $reservationHelper,
        TarifsHelper $tarifsHelper
    ) {
        $this->devisRepo = $devisRepo;
        $this->datehelper = $datehelper;
        $this->reservationRepo = $reservationRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantieRepo = $garantieRepo;
        $this->reservationHelper = $reservationHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->annulationReservationRepo = $annulationReservationRepo;
    }


    /**
     * @Route("/generer/devis-pdf/{hashedId}", name="devis_pdf", methods={"GET"})
     */
    public function devisPDF(Pdf $knpSnappyPdf,  $hashedId)
    {
        $devis  = $this->devisRepo->findByHashedId($hashedId);
        //get reservation using hashed 

        // Configure Dompdf according to your needs7
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // logo joellocation
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;

        // en tete joellocation
        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;

        $html = $this->renderView('admin/reservation/pdf/devis_pdf.html.twig', [

            'logo' => $logo_src,
            'entete' => $entete_src,
            'devis' => $devis,
            'prixTotalTTC' => $devis->getPrix(),
            'taxeRate' => $this->tarifsHelper->getTaxe(),
            'tarifVehiculeTTC' => $devis->getTarifVehicule(),
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire()

        ]);

        /* return new PdfResponse(
                $knpSnappyPdf->getOutputFromHtml($html),
                'file.pdf'
            ); */

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("devis_" . $devis->getNumero() . ".pdf", [
            "Attachment" => false,
        ]);
    }

    /**
     * @Route("generer/contrat-pdf/{hashedId}", name="contrat_pdf", methods={"GET"})
     */
    public function pdfcontrat(Pdf $knpSnappyPdf, $hashedId)
    {

        //get reservation using hashed 
        $reservation  = $this->reservationRepo->findByHashedId($hashedId);

        // Configure Dompdf according to your needs7
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // logo joellocation
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;
        // picture vehicule
        $vehicule = $this->getParameter('logo') . '/contrat/picture_contrat.PNG';
        $vehicule_data = base64_encode(file_get_contents($vehicule));
        $vehicule_src = 'data:image/png;base64,' . $vehicule_data;

        // en tete joellocation
        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;

        $options = $reservation->getOptions();
        $allOptions = $this->optionsRepo->findAll();

        //construction de tableau pour options
        $allOptions = [];
        $options = $reservation->getOptions();
        foreach ($options as $option) {
            $allOptions[$option->getAppelation()]['appelation'] = $option->getAppelation();
            $allOptions[$option->getAppelation()]['prix'] = $option->getPrix();
        }

        //construction de tableu de tableaux pour garanties
        $allGaranties = [];
        $garanties = $reservation->getGaranties();
        foreach ($garanties as $garantie) {
            $allGaranties[$garantie->getAppelation()]['appelation'] = $garantie->getAppelation();
            $allGaranties[$garantie->getAppelation()]['prix'] = $garantie->getPrix();
        }

        //total a payer  = prix de la réservation - somme paiements déjà effectués
        $sommePaiements =  $reservation->getSommePaiements();

        // $restePayerTTC = $this->reservationHelper->getPrixTTC($restePayerHT);


        //frais supplémentaires
        $totalFraisSupplHT = 0;
        foreach ($reservation->getFraisSupplResas() as $fraisSuppl) {
            $totalFraisSupplHT += $fraisSuppl->getTotalHT();
        }

        $totalFraisSupplTTC = $this->tarifsHelper->calculTarifTTCfromHT($totalFraisSupplHT);

        $totalTTC = $reservation->getPrix() + $totalFraisSupplTTC;
        $restePayerTTC = ($reservation->getPrix()  + $totalFraisSupplTTC)   - $sommePaiements;

        $html = $this->renderView('admin/reservation/pdf/contrat_pdf.html.twig', [

            'logo' => $logo_src,
            'entete' => $entete_src,
            'reservation' => $reservation,
            'devis' => $this->devisRepo->find(intval($reservation->getNumDevis())),
            'vehicule' => $vehicule_src,
            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties,
            'sommePaiements' => $sommePaiements,
            'totalTTC' => $totalTTC,
            'restePayerTTC' => $restePayerTTC,
            'totalFraisSupplTTC' => $totalFraisSupplTTC,
            'numContrat' => $this->getNumFacture($reservation, "CO")


        ]);

        /* return new PdfResponse(
                $knpSnappyPdf->getOutputFromHtml($html),
                'file.pdf'
            ); */

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("contrat_" . $reservation->getReference() . ".pdf", [
            "Attachment" => true,
        ]);
    }


    /**
     * @Route("/generer/facture-pdf/{hashedId}", name="facture_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function facturePDF(Pdf $knpSnappyPdf,  $hashedId)
    {

        //get reservation using hashed 
        $reservation  = $this->reservationRepo->findByHashedId($hashedId);
        $sommeFraisTotalHT = 0;
        
        foreach ($reservation->getFraisSupplResas() as $resa) {
            $sommeFraisTotalHT = $sommeFraisTotalHT + $resa->getTotalHT();
        }

        // Configure Dompdf according to your needs7
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // logo joellocation
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;
        $createdAt = $this->datehelper->dateNow();
        $devis = $this->devisRepo->find(intval($reservation->getNumDevis()));

        // en tete joellocation
        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;
        //le prix frais supplémentaire est déja en HT
        // $prixFraisSupplHT =  $reservation->getFraisSupplResas();

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

        $html = $this->renderView('admin/reservation/pdf/facture_pdf.html.twig', [

            'logo' => $logo_src,
            'entete' => $entete_src,
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->getNumFacture($reservation, 'FA'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'sommeFraisTotalHT' => $sommeFraisTotalHT,
            'prixFraisSupplHT' => $prixFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $this->tarifsHelper->getTaxe(),
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire()


        ]);

        /* return new PdfResponse(
                $knpSnappyPdf->getOutputFromHtml($html),
                'file.pdf'
            ); */

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("facture_" . $reservation->getReference() . ".pdf", [
            "Attachment" => true,
        ]);
    }

    /**
     * @Route("/generer/avoir-pdf/{hashedId}", name="avoir_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function avoirPDF(Pdf $knpSnappyPdf,  $hashedId)
    {

        //get reservation using hashed 
        $reservation  = $this->reservationRepo->findByHashedId($hashedId);
        $annulationEntity = $this->annulationReservationRepo->findOneBy(['reservation' => $reservation]);
        $idAnnulation = $annulationEntity->getId();

        $sommeFraisTotalHT = 0;
        
        foreach ($reservation->getFraisSupplResas() as $resa) {
            $sommeFraisTotalHT = $sommeFraisTotalHT + $resa->getTotalHT();
        }

        // Configure Dompdf according to your needs7
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // logo joellocation
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;
        $createdAt = $this->datehelper->dateNow();
        $devis = $this->devisRepo->find(intval($reservation->getNumDevis()));

        // en tete joellocation
        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;
        //le prix frais supplémentaire est déja en HT
        // $prixFraisSupplHT =  $reservation->getFraisSupplResas();

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

        $html = $this->renderView('admin/reservation/pdf/avoir_pdf.html.twig', [

            'logo' => $logo_src,
            'entete' => $entete_src,
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->getNumFacture($reservation, 'AV'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'sommeFraisTotalHT' => $sommeFraisTotalHT,
            'prixFraisSupplHT' => $prixFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $this->tarifsHelper->getTaxe(),
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'montantAvoir'=> $annulationEntity->getMontantAvoir()

        ]);

        /* return new PdfResponse(
                $knpSnappyPdf->getOutputFromHtml($html),
                'file.pdf'
            ); */

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("avoir_" . $reservation->getReference() . ".pdf", [
            "Attachment" => true,
        ]);
    }

    /**
     * @Route("/generer/facture-devis-pdf/{id}", name="facture_devis_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function factureDevisPDF(Pdf $knpSnappyPdf, Devis $devis)
    {

        // Configure Dompdf according to your needs7
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // logo joellocation
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;
        $createdAt = $this->datehelper->dateNow();

        // en tete joellocation
        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;
        //le prix frais supplémentaire est déja en HT
        // $prixFraisSupplHT =  $reservation->getFraisSupplResas();


        $tarifVehiculeTTC = $devis->getTarifVehicule();

        $prixHT = $this->tarifsHelper->calculTarifHTfromTTC($devis->getPrix());
        $prixTaxe = $this->tarifsHelper->calculTaxeFromHT($prixHT);
        $prixUnitHT = $prixHT / $devis->getDuree();
        $prixTTC = $prixTaxe + $prixHT;

        $prixTotalHT = $prixHT;

        $prixTaxeTotal = $this->tarifsHelper->calculTaxeFromHT($prixTotalHT);
        $prixTotalTTC = $prixTotalHT + $prixTaxeTotal;

        $html = $this->renderView('admin/reservation/pdf/facture_devis_pdf.html.twig', [

            'logo' => $logo_src,
            'entete' => $entete_src,
            'devis' => $devis,
            'createdAt' => $createdAt,
            'numeroFacture' => $this->getNumFacture($devis, 'FA'),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
            'prixTaxeTotal' => $prixTaxeTotal,
            'prixUnitHT' => $prixUnitHT,
            'prixTotalTTC' => $prixTotalTTC,
            'prixTTC' => $prixTTC,
            'taxe' => $this->tarifsHelper->getTaxe(),
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire()


        ]);

        /* return new PdfResponse(
                $knpSnappyPdf->getOutputFromHtml($html),
                'file.pdf'
            ); */

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("facture_" . $devis->getNumero() . ".pdf", [
            "Attachment" => true,
        ]);
    }


    public function getNumFacture($reservation, $prefix)
    {
        $idResa = $reservation->getId();
        $createdAt = $this->datehelper->dateNow();
        $currentYear = $createdAt->format('Y');
        $currentYear = str_split($currentYear, 2);


        if ($idResa > 99) {
            $numeroFacture = $prefix . $currentYear[1] . '00' . $idResa;
        } elseif ($idResa > 999) {
            $numeroFacture = $prefix . $currentYear[1] . '0' . $idResa;
        } elseif ($idResa > 9999) {
            $numeroFacture = $prefix . $currentYear[1] . $idResa;
        } elseif ($idResa < 99 && $idResa > 10) {
            $numeroFacture = $prefix . $currentYear[1] . '000' . $idResa;
        } elseif ($idResa < 10) {
            $numeroFacture = $prefix . $currentYear[1] . '0000' . $idResa;
        }

        return $numeroFacture;
    }

    /**
     * @Route("/preview/devis-pdf", name="preview_devis_pdf", methods={"GET"})
     */
    public function previewDevisPDF(): Response
    {
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;

        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;

        $devis = $this->createMockDevisForPreview();
        
        return $this->render('admin/reservation/pdf/devis_pdf.html.twig', [
            'logo' => $logo_src,
            'entete' => $entete_src,
            'devis' => $devis,
            'prixTotalTTC' => $devis->getPrix(),
            'taxeRate' => $this->tarifsHelper->getTaxe(),
            'tarifVehiculeTTC' => $devis->getTarifVehicule(),
            'prixConductTTC' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'isPreview' => true
        ]);
    }

    /**
     * @Route("/preview/contrat-pdf", name="preview_contrat_pdf", methods={"GET"})
     */
    public function previewContratPDF(): Response
    {
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;
        
        $vehicule = $this->getParameter('logo') . '/contrat/picture_contrat.PNG';
        $vehicule_data = base64_encode(file_get_contents($vehicule));
        $vehicule_src = 'data:image/png;base64,' . $vehicule_data;

        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;

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
        $totalFraisSupplHT = 0;
        foreach ($reservation->getFraisSupplResas() as $fraisSuppl) {
            $totalFraisSupplHT += $fraisSuppl->getTotalHT();
        }

        $totalFraisSupplTTC = $this->tarifsHelper->calculTarifTTCfromHT($totalFraisSupplHT);

        $totalTTC = $reservation->getPrix() + $totalFraisSupplTTC;
        $sommePaiements = $reservation->getSommePaiements();
        $restePayerTTC = ($reservation->getPrix() + $totalFraisSupplTTC) - $sommePaiements;

        return $this->render('admin/reservation/pdf/contrat_pdf.html.twig', [
            'logo' => $logo_src,
            'entete' => $entete_src,
            'reservation' => $reservation,
            'devis' => $this->devisRepo->find(intval($reservation->getNumDevis())),
            'vehicule' => $vehicule_src,
            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties,
            'sommePaiements' => $sommePaiements,
            'totalTTC' => $totalTTC,
            'restePayerTTC' => $restePayerTTC,
            'totalFraisSupplTTC' => $totalFraisSupplTTC,
            'numContrat' => $this->getNumFacture($reservation, "CO"),
            'isPreview' => true
        ]);
    }

    /**
     * @Route("/preview/facture-pdf", name="preview_facture_pdf", methods={"GET"})
     */
    public function previewFacturePDF(): Response
    {
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;
        
        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;
        
        $reservation = $this->createMockReservationForPreview();
        
        $createdAt = $this->datehelper->dateNow();
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

        return $this->render('admin/reservation/pdf/facture_pdf.html.twig', [
            'logo' => $logo_src,
            'entete' => $entete_src,
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->getNumFacture($reservation, 'FA'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'sommeFraisTotalHT' => $sommeFraisTotalHT,
            'prixFraisSupplHT' => $prixFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
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
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;
        
        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;
        
        $reservation = $this->createMockReservationForPreview();
        $createdAt = $this->datehelper->dateNow();
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

        return $this->render('admin/reservation/pdf/avoir_pdf.html.twig', [
            'logo' => $logo_src,
            'entete' => $entete_src,
            'reservation' => $reservation,
            'createdAt' => $createdAt,
            'devis' => $devis,
            'numeroFacture' => $this->getNumFacture($reservation, 'AV'),
            'frais' => $reservation->getFraisSupplResas(),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'sommeFraisTotalHT' => $sommeFraisTotalHT,
            'prixFraisSupplHT' => $prixFraisSupplHT,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
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
        $logo = $this->getParameter('logo') . '/Joellocation-logo-resized.png';
        $logo_data = base64_encode(file_get_contents($logo));
        $logo_src = 'data:image/png;base64,' . $logo_data;
        
        $entete = $this->getParameter('logo') . '/pdf/entete-joellocation.PNG';
        $entete_data = base64_encode(file_get_contents($entete));
        $entete_src = 'data:image/png;base64,' . $entete_data;
        
        $devis = $this->createMockDevisForPreview();
        
        $createdAt = $this->datehelper->dateNow();

        $tarifVehiculeTTC = $devis->getTarifVehicule();

        $prixHT = $this->tarifsHelper->calculTarifHTfromTTC($devis->getPrix());
        $prixTaxe = $this->tarifsHelper->calculTaxeFromHT($prixHT);
        $prixUnitHT = $prixHT / $devis->getDuree();
        $prixTTC = $prixTaxe + $prixHT;

        $prixTotalHT = $prixHT;

        $prixTaxeTotal = $this->tarifsHelper->calculTaxeFromHT($prixTotalHT);
        $prixTotalTTC = $prixTotalHT + $prixTaxeTotal;

        return $this->render('admin/reservation/pdf/facture_devis_pdf.html.twig', [
            'logo' => $logo_src,
            'entete' => $entete_src,
            'devis' => $devis,
            'createdAt' => $createdAt,
            'numeroFacture' => $this->getNumFacture($devis, 'FA'),
            'tarifVehiculeTTC' => $tarifVehiculeTTC,
            'prixHT' => $prixHT,
            'prixTotalHT' => $prixTotalHT,
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

        return $this->render('pdf_preview/templates_list.html.twig', [
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
        
        // Create mock client
        $client = $this->createMockClient();
        $devis->setClient($client);
        
        // Create mock vehicle
        $vehicule = $this->createMockVehicule();
        $devis->setVehicule($vehicule);
        
        return $devis;
    }

    private function createMockReservationForPreview(): Reservation
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
