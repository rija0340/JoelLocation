<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\Reservation;
use App\Entity\User;
use App\Service\EmailManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller to test all email types
 * @Route("/email-testing")
 */
class EmailTestingController extends AbstractController
{
    private $emailManagerService;

    public function __construct(EmailManagerService $emailManagerService)
    {
        $this->emailManagerService = $emailManagerService;
    }

    /**
     * @Route("/", name="email_testing_index")
     */
    public function index(): Response
    {
        return $this->render('email_testing/index.html.twig');
    }

    /**
     * @Route("/test-devis", name="email_testing_devis")
     */
    public function testDevis(Request $request): Response
    {
        // Create a mock devis for testing
        $devis = $this->createMockDevis();
        
        try {
            $this->emailManagerService->sendDevis($request, $devis);
            $this->addFlash('success', 'Email devis sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send devis email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('email_testing_index');
    }

    /**
     * @Route("/test-contrat", name="email_testing_contrat")
     */
    public function testContrat(Request $request): Response
    {
        // Create a mock reservation for testing
        $reservation = $this->createMockReservation();
        
        try {
            $this->emailManagerService->sendContrat($request, $reservation);
            $this->addFlash('success', 'Email contrat sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send contrat email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('email_testing_index');
    }

    /**
     * @Route("/test-facture", name="email_testing_facture")
     */
    public function testFacture(Request $request): Response
    {
        // Create a mock reservation for testing
        $reservation = $this->createMockReservation();
        
        try {
            $this->emailManagerService->sendFacture($request, $reservation);
            $this->addFlash('success', 'Email facture sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send facture email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('email_testing_index');
    }

    /**
     * @Route("/test-validation-inscription", name="email_testing_validation_inscription")
     */
    public function testValidationInscription(): Response
    {
        // Create a mock user for testing
        $user = $this->createMockUser();
        $token = 'test-token-' . uniqid();
        
        try {
            $this->emailManagerService->sendValidationInscription($user, $token);
            $this->addFlash('success', 'Email validation inscription sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send validation inscription email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('email_testing_index');
    }

    /**
     * @Route("/test-contact", name="email_testing_contact")
     */
    public function testContact(): Response
    {
        // Create mock contact data for testing
        $data = [
            'nom' => 'Test User',
            'emailClient' => 'rakotoarinelinarija@gmail.com',
            'telephone' => '0123456789',
            'message' => 'This is a test message from email testing controller.',
            'sujet' => 'Test Contact Email'
        ];
        
        try {
            $this->emailManagerService->sendContact($data);
            $this->addFlash('success', 'Email contact sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send contact email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('email_testing_index');
    }

    /**
     * @Route("/test-paiement-confirmation", name="email_testing_paiement_confirmation")
     */
    public function testPaiementConfirmation(): Response
    {
        // Create a mock reservation for testing
        $reservation = $this->createMockReservation();
        $montant = 150.50;
        
        try {
            $this->emailManagerService->sendPaiementConfirmation($reservation, $montant);
            $this->addFlash('success', 'Email paiement confirmation sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send paiement confirmation email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('email_testing_index');
    }

    /**
     * @Route("/test-appel-paiement", name="email_testing_appel_paiement")
     */
    public function testAppelPaiement(): Response
    {
        // Create a mock reservation for testing
        $reservation = $this->createMockReservation();
        
        try {
            $this->emailManagerService->sendAppelPaiement($reservation);
            $this->addFlash('success', 'Email appel paiement sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send appel paiement email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('email_testing_index');
    }

    /**
     * @Route("/test-all", name="email_testing_all")
     */
    public function testAllEmails(Request $request): Response
    {
        $results = [];
        
        // Test all email types
        try {
            // Test devis email
            $devis = $this->createMockDevis();
            $this->emailManagerService->sendDevis($request, $devis);
            $results[] = ['type' => 'Devis', 'status' => 'success', 'message' => 'Email devis sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Devis', 'status' => 'error', 'message' => 'Failed to send devis email: ' . $e->getMessage()];
        }
        
        try {
            // Test contrat email
            $reservation = $this->createMockReservation();
            $this->emailManagerService->sendContrat($request, $reservation);
            $results[] = ['type' => 'Contrat', 'status' => 'success', 'message' => 'Email contrat sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Contrat', 'status' => 'error', 'message' => 'Failed to send contrat email: ' . $e->getMessage()];
        }
        
        try {
            // Test facture email
            $reservation = $this->createMockReservation();
            $this->emailManagerService->sendFacture($request, $reservation);
            $results[] = ['type' => 'Facture', 'status' => 'success', 'message' => 'Email facture sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Facture', 'status' => 'error', 'message' => 'Failed to send facture email: ' . $e->getMessage()];
        }
        
        try {
            // Test validation inscription email
            $user = $this->createMockUser();
            $token = 'test-token-' . uniqid();
            $this->emailManagerService->sendValidationInscription($user, $token);
            $results[] = ['type' => 'Validation Inscription', 'status' => 'success', 'message' => 'Email validation inscription sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Validation Inscription', 'status' => 'error', 'message' => 'Failed to send validation inscription email: ' . $e->getMessage()];
        }
        
        try {
            // Test contact email
            $data = [
                'nom' => 'Test User',
                'emailClient' => 'rakotoarinelinarija@gmail.com',
                'telephone' => '0123456789',
                'message' => 'This is a test message from email testing controller.',
                'sujet' => 'Test Contact Email'
            ];
            $this->emailManagerService->sendContact($data);
            $results[] = ['type' => 'Contact', 'status' => 'success', 'message' => 'Email contact sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Contact', 'status' => 'error', 'message' => 'Failed to send contact email: ' . $e->getMessage()];
        }
        
        try {
            // Test paiement confirmation email
            $reservation = $this->createMockReservation();
            $montant = 150.50;
            $this->emailManagerService->sendPaiementConfirmation($reservation, $montant);
            $results[] = ['type' => 'Paiement Confirmation', 'status' => 'success', 'message' => 'Email paiement confirmation sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Paiement Confirmation', 'status' => 'error', 'message' => 'Failed to send paiement confirmation email: ' . $e->getMessage()];
        }
        
        try {
            // Test appel paiement email
            $reservation = $this->createMockReservation();
            $this->emailManagerService->sendAppelPaiement($reservation);
            $results[] = ['type' => 'Appel Paiement', 'status' => 'success', 'message' => 'Email appel paiement sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Appel Paiement', 'status' => 'error', 'message' => 'Failed to send appel paiement email: ' . $e->getMessage()];
        }
        
        return $this->render('email_testing/results.html.twig', [
            'results' => $results
        ]);
    }

    // /**
    //  * Creates a mock Reservation entity for testing
    //  */
    // private function createMockReservation(): Reservation
    // {
    //     $reservation = new Reservation();
        
    //     // Set basic properties
    //     $reservation->setId(1);
    //     $reservation->setReference('TEST-RESA-001');
    //     $reservation->setDateDebut(new \DateTime());
    //     $reservation->setDateFin((new \DateTime())->modify('+3 days'));
    //     $reservation->setDateReservation(new \DateTime());
    //     $reservation->setPrix(500.00);
    //     $reservation->setSommePaiements(200.00);
        
    //     // Create mock client
    //     $client = $this->createMockClient();
    //     $reservation->setClient($client);
        
    //     // Create mock vehicle
    //     $vehicule = $this->createMockVehicule();
    //     $reservation->setVehicule($vehicule);
        
    //     return $reservation;
    // }

    /**
     * Creates a mock User entity for testing
     */
    private function createMockUser(): User
    {
        $user = new User();
        $user->setId(1);
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setMail('rakotoarinelinarija@gmail.com');
        return $user;
    }

    /**
     * Creates a mock client for testing
     */
    private function createMockClient(): User
    {
        return $this->createMockUser();
    }

    /**
     * Creates a mock vehicle for testing
     */
    private function createMockVehicule(): \App\Entity\Vehicule
    {
        $vehicule = new \App\Entity\Vehicule();
        $marque = new \App\Entity\Marque();
        $modele = new \App\Entity\Modele();
        $marque->setLibelle('Peugeot');
        $modele->setLibelle('208');
        // $vehicule->setId(1);
        $vehicule->setMarque($marque);
        $vehicule->setModele($modele);
        return $vehicule;
    }

    /**
     * Creates a mock Devis entity for testing
     */
    private function createMockDevis()
    {
        $devis = new Devis();
        
        // Set basic properties
        // $devis->setId(1);
        $devis->setNumero('TEST-001');
        $devis->setDateDepart(new \DateTime());
        $devis->setDateRetour((new \DateTime())->modify('+3 days'));
        
        // Create mock client
        $client = $this->createMockClient();
        $devis->setClient($client);
        
        // Create mock vehicle
        $vehicule = $this->createMockVehicule();
        $devis->setVehicule($vehicule);
        
        return $devis;
    }

    /**
     * Creates a mock Reservation entity for testing
     */
    private function createMockReservation(): Reservation
    {
        $reservation = new Reservation();
        
        // Set basic properties
        // $reservation->setId(1);
        $reservation->setReference('TEST-RESA-001');
        $reservation->setDateDebut(new \DateTime());
        $reservation->setDateFin((new \DateTime())->modify('+3 days'));
        $reservation->setDateReservation(new \DateTime());
        $reservation->setPrix(500.00);
        $reservation->setSommePaiements(200.00);
        
        // Create mock client
        $client = $this->createMockClient();
        $reservation->setClient($client);
        
        // Create mock vehicle
        $vehicule = $this->createMockVehicule();
        $reservation->setVehicule($vehicule);
        
        return $reservation;
    }

}