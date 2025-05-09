<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Snappy\Pdf;
use App\Entity\Devis;
use App\Entity\Reservation;
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

class PdfController extends AbstractController
{

    private $datehelper;
    private $reservationRepo;
    private $devisRepo;
    private $optionsRepo;
    private $garantieRepo;
    private $reservationHelper;
    private $tarifsHelper;
    public function __construct(
        ReservationRepository $reservationRepo,
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
}
