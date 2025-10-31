<?php

namespace App\Controller\Admin\Testing;

use App\Entity\Devis;
use App\Entity\Reservation;
use App\Entity\User;
use App\Service\EmailService;
use App\Service\EmailManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller to test all email types
 * @Route("/admin/testing/email")
 */
class EmailTestingController extends AbstractController
{
    private $emailManagerService;
    private $emailService;
    private $recepient_mail = 'contact@joellocation.com';

    public function __construct(EmailManagerService $emailManagerService,EmailService $emailService)
    {
        $this->emailManagerService = $emailManagerService;
        $this->emailService = $emailService;
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
     * @Route("/test-avoir", name="email_testing_avoir")
     */
    public function testAvoir(Request $request): Response
    {
        // Create a mock reservation for testing
        $reservation = $this->createMockReservation();
        
        try {
            $this->emailManagerService->sendAvoir($request, $reservation,200);
            $this->addFlash('success', 'Email avoir sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send avoir email: ' . $e->getMessage());
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
            'emailClient' => $this->recepient_mail,
            'telephone' => '0123456789',
            'message' => 'This is a test message from email testing controller.',
            'sujet' => 'Test Contact Email'
        ];
        
        try {
            $response = $this->emailService->send(
                $this->recepient_mail, // to
                $data['sujet'], // subject
                'admin/templates_email/formulaire_contact.html.twig', // template
                $data // context
            );
            $this->addFlash('success', 'Email contact sent successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send contact email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('email_testing_index');
    }

    /**
     * @Route("/test-contact-form", name="email_testing_contact_form")
     */
    public function testContactForm(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Get form data
            $nom = $request->request->get('nom', 'Test User');
            $email = $request->request->get('email', $this->recepient_mail);
            $telephone = $request->request->get('telephone', '0123456789');
            $adresse = $request->request->get('adresse', '123 Rue de la Paix, 75000 Paris');
            $objet = $request->request->get('objet', 'Test Contact Email');
            $message = $request->request->get('message', 'This is a test message from email testing controller.');

            // Prepare contact data
            $data = [
                'nom' => $nom,
                'emailClient' => $email,
                'telephone' => $telephone,
                'adresse' => $adresse,
                'objet' => $objet,
                'message' => $message
            ];

            try {
                $response = $this->emailService->send(
                    $this->recepient_mail, // to
                    $data['objet'], // subject
                    'admin/templates_email/formulaire_contact.html.twig', // template
                    $data // context
                );
                $this->addFlash('success', 'Contact form email sent successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to send contact form email: ' . $e->getMessage());
            }

            return $this->redirectToRoute('email_testing_contact_form');
        }

        return $this->render('email_testing/contact_form.html.twig');
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
     * @Route("/view-templates", name="email_testing_templates_list")
     */
    public function listEmailTemplates(): Response
    {
        $templates = [
            'devis' => 'Devis',
            'contrat' => 'Contrat',
            'facture' => 'Facture',
            'avoir' => 'Avoir',
            'validation_inscription' => 'Validation Inscription',
            'formulaire_contact' => 'Formulaire Contact',
            'confirmation_paiement' => 'Confirmation Paiement',
            'appel_paiement' => 'Appel Paiement',
            'base_email' => 'Base Email'
        ];

        return $this->render('email_testing/templates_list.html.twig', [
            'templates' => $templates
        ]);
    }

    /**
     * @Route("/view-template/{type}", name="email_testing_view_template")
     */
    public function viewTemplate(string $type): Response
    {
        $templateMap = [
            'devis' => 'admin/templates_email/devis.html.twig',
            'contrat' => 'admin/templates_email/contrat.html.twig',
            'facture' => 'admin/templates_email/facture.html.twig',
            'avoir' => 'admin/templates_email/avoir.html.twig',
            'validation_inscription' => 'admin/templates_email/validation_inscription.html.twig',
            'formulaire_contact' => 'admin/templates_email/formulaire_contact.html.twig',
            'confirmation_paiement' => 'admin/templates_email/confirmation_paiement.html.twig',
            'appel_paiement' => 'admin/templates_email/appel_paiement.html.twig',
            'base_email' => 'admin/templates_email/base_email.html.twig'
        ];

        if (!isset($templateMap[$type])) {
            throw $this->createNotFoundException('Email template not found.');
        }

        // Mock data for each template type
        $mockData = $this->getMockTemplateData($type);

        return $this->render($templateMap[$type], array_merge([
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'phone' => '0123456789',
            'phone_number1' => '0123456788',
            'phone_number2' => '0123456789',
            'website_url' => 'https://joellocation.com',
            'facebook_url' => 'https://facebook.com/joellocation',
            'instagram_url' => 'https://instagram.com/joellocation',
            'youtube_url' => 'https://youtube.com/joellocation'
        ], $mockData));
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
        
        sleep(60); // 1-minute delay between emails
        
        try {
            // Test contrat email
            $reservation = $this->createMockReservation();
            $this->emailManagerService->sendContrat($request, $reservation);
            $results[] = ['type' => 'Contrat', 'status' => 'success', 'message' => 'Email contrat sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Contrat', 'status' => 'error', 'message' => 'Failed to send contrat email: ' . $e->getMessage()];
        }
        
        sleep(60); // 1-minute delay between emails
        
        try {
            // Test facture email
            $reservation = $this->createMockReservation();
            $this->emailManagerService->sendFacture($request, $reservation);
            $results[] = ['type' => 'Facture', 'status' => 'success', 'message' => 'Email facture sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Facture', 'status' => 'error', 'message' => 'Failed to send facture email: ' . $e->getMessage()];
        }
        
        sleep(60); // 1-minute delay between emails
        
        try {
            // Test avoir email
            $reservation = $this->createMockReservation();
            $this->emailManagerService->sendAvoir($request, $reservation, 200);
            $results[] = ['type' => 'Avoir', 'status' => 'success', 'message' => 'Email avoir sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Avoir', 'status' => 'error', 'message' => 'Failed to send avoir email: ' . $e->getMessage()];
        }
        
        sleep(60); // 1-minute delay between emails
        
        try {
            // Test validation inscription email
            $user = $this->createMockUser();
            $token = 'test-token-' . uniqid();
            $this->emailManagerService->sendValidationInscription($user, $token);
            $results[] = ['type' => 'Validation Inscription', 'status' => 'success', 'message' => 'Email validation inscription sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Validation Inscription', 'status' => 'error', 'message' => 'Failed to send validation inscription email: ' . $e->getMessage()];
        }
        
        sleep(60); // 1-minute delay between emails
        
        try {
            // Test contact email
            $data = [
                'nom' => 'Test User',
                'emailClient' => $this->recepient_mail,
                'telephone' => '0123456789',
                'message' => 'This is a test message from email testing controller.',
                'sujet' => 'Test Contact Email'
            ];
            $this->emailManagerService->sendContact($data);
            $results[] = ['type' => 'Contact', 'status' => 'success', 'message' => 'Email contact sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Contact', 'status' => 'error', 'message' => 'Failed to send contact email: ' . $e->getMessage()];
        }
        
        sleep(60); // 1-minute delay between emails
        
        try {
            // Test paiement confirmation email
            $reservation = $this->createMockReservation();
            $montant = 150.50;
            $this->emailManagerService->sendPaiementConfirmation($reservation, $montant);
            $results[] = ['type' => 'Paiement Confirmation', 'status' => 'success', 'message' => 'Email paiement confirmation sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Paiement Confirmation', 'status' => 'error', 'message' => 'Failed to send paiement confirmation email: ' . $e->getMessage()];
        }
        
        sleep(60); // 1-minute delay between emails
        
        try {
            // Test appel paiement email
            $reservation = $this->createMockReservation();
            $this->emailManagerService->sendAppelPaiement($reservation);
            $results[] = ['type' => 'Appel Paiement', 'status' => 'success', 'message' => 'Email appel paiement sent successfully'];
        } catch (\Exception $e) {
            $results[] = ['type' => 'Appel Paiement', 'status' => 'error', 'message' => 'Failed to send appel paiement email: ' . $e->getMessage()];
        }
        
        sleep(60); // 1-minute delay between emails
        
        try {
            // Test base email (send a simple test email)
            $baseEmailResult = $this->emailService->send(
                $this->recepient_mail,
                'Test Base Email',
                'admin/templates_email/base_email.html.twig',
                [
                    'name' => 'Test User',
                    'content' => 'This is a test of the base email template.'
                ]
            );
            if ($baseEmailResult) {
                $results[] = ['type' => 'Base Email', 'status' => 'success', 'message' => 'Base email sent successfully'];
            } else {
                $results[] = ['type' => 'Base Email', 'status' => 'error', 'message' => 'Failed to send base email'];
            }
        } catch (\Exception $e) {
            $results[] = ['type' => 'Base Email', 'status' => 'error', 'message' => 'Failed to send base email: ' . $e->getMessage()];
        }
        
        return $this->render('email_testing/results.html.twig', [
            'results' => $results
        ]);
    }

    private function getMockTemplateData(string $type): array
    {
        $mockData = [];
        $reservation = $this->createMockReservation();
        $devis = $this->createMockDevis();
        $user = $this->createMockUser();

        switch ($type) {
            case 'devis':
                $mockData = [
                    'devis' => $devis,
                    'devisLink' => 'https://example.com/devis',
                    'details_client' => 'Client: John Doe<br>Adresse: 123 Rue Exemple, 75000 Paris<br>Téléphone: 0123456789<br>Email: client@example.com',
                    'details_facture' => 'Numéro: DEV-001<br>Date: ' . date('d/m/Y') . '<br>Total: 250.00 €'
                ];
                break;
            case 'contrat':
                $mockData = [
                    'reservation' => $reservation,
                    'contratLink' => 'https://example.com/contrat',
                    'details_client' => 'Client: John Doe<br>Adresse: 123 Rue Exemple, 75000 Paris<br>Téléphone: 0123456789<br>Email: client@example.com',
                    'details_facture' => 'Numéro: RES-001<br>Date: ' . date('d/m/Y') . '<br>Total: 500.00 €'
                ];
                break;
            case 'facture':
                $mockData = [
                    'reservation' => $reservation,
                    'factureLink' => 'https://example.com/facture',
                    'details_client' => 'Client: John Doe<br>Adresse: 123 Rue Exemple, 75000 Paris<br>Téléphone: 0123456789<br>Email: client@example.com',
                    'details_facture' => 'Numéro: FA-2400001<br>Date: ' . date('d/m/Y') . '<br>Total: 500.00 €',
                    'montant' => 500.00
                ];
                break;
            case 'avoir':
                $mockData = [
                    'reservation' => $reservation,
                    'avoirLink' => 'https://example.com/avoir',
                    'details_client' => 'Client: John Doe<br>Adresse: 123 Rue Exemple, 75000 Paris<br>Téléphone: 0123456789<br>Email: client@example.com',
                    'details_facture' => 'Numéro: AV-2400001<br>Date: ' . date('d/m/Y') . '<br>Total: 200.00 €',
                    'montant' => 200.00,
                    'annulation' => $this->createMockAnnulationReservation($reservation)
                ];
                break;
            case 'validation_inscription':
                $mockData = [
                    'user' => $user,
                    'token' => 'validation-token-123',
                    'validationLink' => 'https://example.com/validate-account',
                ];
                break;
            case 'formulaire_contact':
                $mockData = [
                    'data' => [
                        'nom' => 'Test User',
                        'emailClient' => 'test@client.com',
                        'telephone' => '0123456789',
                        'message' => 'This is a test message',
                        'sujet' => 'Test Subject'
                    ]
                ];
                break;
            case 'confirmation_paiement':
                $mockData = [
                    'reservation' => $reservation,
                    'montant' => 150.50,
                    'referencePaiement' => 'PMT-001-2024',
                    'datePaiement' => date('d/m/Y H:i:s')
                ];
                break;
            case 'appel_paiement':
                $mockData = [
                    'reservation' => $reservation,
                    'montantRestant' => 300.00,
                    'factureLink' => 'https://example.com/facture'
                ];
                break;
        }

        return $mockData;
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
        $user->setMail($this->recepient_mail);
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
        
        // Use reflection to set ID for the vehicle since it doesn't have a setId method
        $reflection = new \ReflectionClass($vehicule);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($vehicule, 1);
        
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
        // Use reflection to set ID for the devis since it doesn't have a setId method
        $reflection = new \ReflectionClass($devis);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($devis, 1);
        
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
        // Use reflection to set ID for the reservation since it doesn't have a setId method
        $reflection = new \ReflectionClass($reservation);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($reservation, 1);
        
        $reservation->setReference('TEST-RESA-001');
        $reservation->setDateDebut(new \DateTime());
        $reservation->setDateFin((new \DateTime())->modify('+3 days'));
        $reservation->setDateReservation(new \DateTime());
        $reservation->setPrix(500.00);
        $reservation->setDuree(3);
        // $reservation->setSommePaiements(200.00);
        
        // Create mock client
        $client = $this->createMockClient();
        $reservation->setClient($client);
        
        // Create mock vehicle
        $vehicule = $this->createMockVehicule();
        $reservation->setVehicule($vehicule);
        
        return $reservation;
    }

    /**
     * Creates a mock AnnulationReservation entity for testing
     */
    private function createMockAnnulationReservation(?Reservation $reservation = null): \App\Entity\AnnulationReservation
    {
        $annulation = new \App\Entity\AnnulationReservation();
        
        // Use reflection to set ID for the annulation since it doesn't have a setId method
        $reflection = new \ReflectionClass($annulation);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($annulation, 1);
        
        $annulation->setCreatedAt(new \DateTime());
        $annulation->setMotif('Test annulation motif');
        $annulation->setType('avoir');
        $annulation->setMontantAvoir(200.00);
        
        if ($reservation) {
            $annulation->setReservation($reservation);
        }
        
        return $annulation;
    }

}