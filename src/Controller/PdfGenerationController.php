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

}