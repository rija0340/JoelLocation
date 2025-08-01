<?php

namespace App\Controller\Client;

use App\Classe\Payment\Event\PaymentSuccessEvent;
use App\Entity\Paiement;
use App\Form\PaiementType;
use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Classe\Payment\PaymentService;
use App\Classe\ValidationReservationClientSession;
use App\Entity\Reservation;
use App\Repository\DevisRepository;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/espaceclient/payment")
 */
class PaymentController extends AbstractController
{

    private $paymentService;
    private $devisRepo;
    private $eventDispatcher;
    private $validationReservationClientSession;
    private $flashy;
    private $reservationRepo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PaymentService $paymentService,
        DevisRepository $devisRepo,
        ValidationReservationClientSession $validationReservationClientSession,
        FlashyNotifier $flashy,
        ReservationRepository $reservationRepo
    ) {
        $this->paymentService = $paymentService;
        $this->devisRepo = $devisRepo;
        $this->eventDispatcher = $eventDispatcher;
        $this->validationReservationClientSession = $validationReservationClientSession;
        $this->flashy = $flashy;
        $this->reservationRepo = $reservationRepo;
    }

    /**
     * @Route("/create-order", name="payment_create_order", methods={"POST"})
     */
    public function createOrder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $result = $this->paymentService->createOrder(
            $data['amount'],
            [
                'currency' => 'EUR',
                'devisId' => $data['devisId'],
                'description' => 'Réservation Joel Location',
                'return_url' => $this->generateUrl('payment_success', [], true),
                'cancel_url' => $this->generateUrl('payment_cancel', [], true),
            ],
            'paypal'
        );

        if ($result->isSuccessful()) {
            return $this->json([
                'id' => $result->getTransactionId(),
                'data' => $result->getResponseData()
            ]);
        }

        return $this->json(['error' => $result->getErrorMessage()], 400);
    }

    /**
     * @Route("/capture/{orderId}", name="payment_capture", methods={"POST"})
     */
    public function capturePayment(string $orderId): JsonResponse
    {
        $result = $this->paymentService->capturePayment($orderId, 'paypal');

        if ($result->isSuccessful()) {
            return $this->json([
                'id' => $result->getTransactionId(),
                'data' => $result->getResponseData()
            ]);
        }

        return $this->json(['error' => $result->getErrorMessage()], 400);
    }

    /**
     * @Route("/cancel", name="payment_cancel", methods={"GET"})
     */
    public function cancel(Request $request): Response
    {
        return $this->render('client2/payment/cancel.html.twig');
    }

    /**
     * @Route("/success", name="payment_success", methods={"POST"})
     */
    public function success(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        try {
            $refDevis = $data['paymentData']['purchase_units'][0]['reference_id'];
            $devis = $this->devisRepo->findOneBy(['numero' => $refDevis]);
            $paymentSuccessEvent = new PaymentSuccessEvent($devis, $data);
            $this->eventDispatcher->dispatch($paymentSuccessEvent, PaymentSuccessEvent::NAME);
            // Récupérer la réservation créée
            $reservation = $paymentSuccessEvent->getReservation();
            // Récupère le devis/réservation associé
            if (!$devis) {
                throw new \Exception("Devis non trouvé");
            }

            // Redirige avec un message de succès
            return $this->redirectToRoute('payment_confirmation', [
                'paymentRef' => $reservation->getStripeSessionId() //called stripeSessionId but this in general all payment id
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors du traitement du paiement: ' . $e->getMessage()
            ], 400);
        }
    }

    //create payement confirmation here 
    /**
     * @Route("/confirmation/{paymentRef}", name="payment_confirmation", methods={"GET"})
     */
    public function confirmationSuccess(Request $request, string $paymentRef): Response
    {

        $reservation = $this->reservationRepo->findOneBy(['stripeSessionId' => $paymentRef]);
        //securité en cas de session non valide
        if (!$reservation || $reservation->getClient() != $this->getUser()) {
            $this->flashy->error("La réservation n'existe pas");
            return $this->redirectToRoute('espaceClient_index');
        }
        //vider session validation paiement 
        $this->validationReservationClientSession->removeValidationSession();
        return $this->render('client2/payment/success.html.twig', [
            "reservation" => $reservation,
        ]);
    }

    /**
     * @Route("/debug-payment-flow", name="debug_payment_flow", methods={"GET"})
     */
    public function debugPaymentFlow(): Response
    {
        // Mock a devis to use for testing
        $devis = $this->devisRepo->findOneBy([], ['id' => 'DESC']);

        if (!$devis) {
            return $this->json(['error' => 'No devis found for testing']);
        }

        // Create mock PayPal response data
        $mockPaymentData = [
            'paypalOrderId' => 'TEST_ORDER_' . uniqid(),
            'transactionId' => 'TEST_TRANSACTION_' . uniqid(),
            'paymentData' => [
                'id' => 'TEST_PAYMENT_' . uniqid(),
                'purchase_units' => [
                    [
                        'reference_id' => $devis->getNumero(),
                        'payments' => [
                            'captures' => [
                                [
                                    'amount' => [
                                        'value' => $devis->getPrix()
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Create and dispatch the payment success event
        $paymentSuccessEvent = new PaymentSuccessEvent($devis, $mockPaymentData);
        $this->eventDispatcher->dispatch($paymentSuccessEvent, PaymentSuccessEvent::NAME);

        // Get the reservation created by the event
        $reservation = $paymentSuccessEvent->getReservation();

        if (!$reservation) {
            return $this->json(['error' => 'Reservation not created']);
        }

        // Return success with reservation details
        return $this->json([
            'success' => true,
            'reservation' => [
                'id' => $reservation->getId(),
                'reference' => $reservation->getReference(),
                'client' => $reservation->getClient()->getNom(),
                'vehicule' => $reservation->getVehicule()->getMarque() . ' ' . $reservation->getVehicule()->getModele()
            ]
        ]);
    }
}
