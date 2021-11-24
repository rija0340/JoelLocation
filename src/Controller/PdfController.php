<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Snappy\Pdf;
use App\Entity\Devis;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PdfController extends AbstractController
{

    private $datehelper;
    private $reservationRepo;
    public function __construct(ReservationRepository $reservationRepo, DateHelper $datehelper)
    {
        $this->datehelper = $datehelper;
        $this->reservationRepo = $reservationRepo;
    }


    /**
     * @Route("/generer/devis-pdf/{id}", name="devis_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function devisPDF(Pdf $knpSnappyPdf, Devis $devis)
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


        // logo joellocation
        $footer = $this->getParameter('logo') . '/pdf/footer-joellocation.png';
        $footer_data = base64_encode(file_get_contents($footer));
        $footer_src = 'data:image/png;base64,' . $footer_data;

        $html = $this->renderView('admin/reservation/pdf/devis_pdf.html.twig', [

            'logo' => $logo_src,
            'devis' => $devis,
            'footer' =>  $footer_src

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
            "Attachment" => true,
        ]);
    }

    /**
     * @Route("generer/contrat-pdf/{id}", name="contrat_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function pdfcontrat(Pdf $knpSnappyPdf, Reservation $reservation)
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
        // picture vehicule
        $vehicule = $this->getParameter('logo') . '/contrat/picture_contrat.PNG';
        $vehicule_data = base64_encode(file_get_contents($vehicule));
        $vehicule_src = 'data:image/png;base64,' . $vehicule_data;

        $html = $this->renderView('admin/reservation/pdf/contrat_pdf.html.twig', [

            'logo' => $logo_src,
            'reservation' => $reservation,
            'vehicule' => $vehicule_src,

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
     * @Route("/generer/facture-pdf/{id}", name="facture_pdf", methods={"GET"},requirements={"id":"\d+"})
     */
    public function facturePDF(Pdf $knpSnappyPdf, Reservation $reservation)
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


        // logo joellocation
        $footer = $this->getParameter('logo') . '/pdf/footer-joellocation.png';
        $footer_data = base64_encode(file_get_contents($footer));
        $footer_src = 'data:image/png;base64,' . $footer_data;

        $html = $this->renderView('admin/reservation/pdf/facture_pdf.html.twig', [

            'logo' => $logo_src,
            'reservation' => $reservation,
            'footer' =>  $footer_src

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
}
