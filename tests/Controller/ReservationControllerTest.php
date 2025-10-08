<?php

namespace App\Tests\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Vehicule;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReservationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    // public function testAccessProtectedRouteUnauthenticated(): void
    // {
    //     $this->client->catchExceptions(false); // To see authentication errors properly
        
    //     $this->client->request('GET', '/backoffice/reservation/');
    //     $this->assertResponseRedirects(); // Should redirect to login
    //     $this->client->followRedirect();
    //     $this->assertRouteSame('app_login'); // Assuming login route name is 'app_login'
    // }

    public function testLoginAsAdmin(): void
    {
        // First ensure we're logged out
        $crawler = $this->client->request('GET', '/connexion');
        $this->assertResponseIsSuccessful();
        
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'rija0340@gmail.com',
            'password' => '34008927', // ENSURE THIS IS CORRECT—wrong pass would fail auth!
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect(); // Follows to /backoffice (may get slash redirect)

        // Follow again to resolve slash chain
        if ($this->client->getResponse()->isRedirect()) {
            $this->client->followRedirect();
        }

        $this->assertResponseIsSuccessful(); // Now on /backoffice/ dashboard, 200

        // Test protected route
        $this->client->request('GET', '/backoffice/reservation/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h2');
    }

    public function testIndex(): void
    {
        $this->loginAdminUser();
        
        $this->client->request('GET', '/backoffice/reservation/');

        $this->assertResponseIsSuccessful();
        // $this->assertSelectorExists('table'); // Table of reservations
        // $this->assertSelectorTextContains('h2', 'Liste des réservations'); // Page title
        $this->assertSelectorTextContains('title', 'JOEL LOCATION - Reservation index');
    }

    // public function testShow(): void
    // {
    //     $this->loginAdminUser();
        
    //     // Create a test reservation if none exists
    //     $reservation = $this->createTestReservation();
        
    //     $this->client->request('GET', '/backoffice/reservation/details/'.$reservation->getId());

    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorExists('div.x_panel'); // Reservation details panel
    //     $this->assertSelectorExists('h2'); // Page title exists
    // }

    // public function testEdit(): void
    // {
    //     $this->loginAdminUser();
        
    //     // Create a test reservation if none exists
    //     $reservation = $this->createTestReservation();
        
    //     // Test GET request to edit page
    //     $crawler = $this->client->request('GET', '/backoffice/reservation/'.$reservation->getId().'/edit');
        
    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorTextContains('h2', 'Modification de la réservation');

    //     // Test POST request to update reservation
    //     $form = $crawler->selectButton('Modifier')->form([
    //         'reservation[date_debut]' => '2023-01-01T10:00:00',
    //         'reservation[date_fin]' => '2023-01-05T10:00:00',
    //         'reservation[agenceDepart]' => 'Aéroport de Point-à-pitre',
    //         'reservation[agenceRetour]' => 'Aéroport de Point-à-pitre',
    //         'reservation[tarifVehicule]' => 150.00,
    //         'reservation[prixOptionsGaranties]' => 50.00,
    //         'reservation[prix]' => 200.00,
    //     ]);
        
    //     $this->client->submit($form);

    //     $this->assertResponseRedirects();
    //     $this->client->followRedirect();
    //     $this->assertResponseIsSuccessful();
    // }

    // public function testNew(): void
    // {
    //     $this->loginAdminUser();
        
    //     $crawler = $this->client->request('GET', '/backoffice/reservation/new');

    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorTextContains('h2', 'Nouvelle résérvation');

    //     // Test form submission
    //     $form = $crawler->selectButton('Enregistrer')->form([
    //         'reservation[date_debut]' => '2023-01-01T10:00:00',
    //         'reservation[date_fin]' => '2023-01-05T10:00:00',
    //         'reservation[agenceDepart]' => 'Aéroport de Point-à-pitre',
    //         'reservation[agenceRetour]' => 'Aéroport de Point-à-pitre',
    //         'reservation[tarifVehicule]' => 150.00,
    //         'reservation[prixOptionsGaranties]' => 50.00,
    //         'reservation[prix]' => 200.00,
    //         'reservation[vehicule]' => $this->createTestVehicule()->getId(), // Add vehicle ID
    //     ]);
        
    //     $this->client->submit($form);

    //     $this->assertResponseRedirects('/backoffice/reservation/'); // Redirect after creation
    // }

    // public function testDelete(): void
    // {
    //     $this->loginAdminUser();
        
    //     // Create a test reservation to delete
    //     $reservation = $this->createTestReservation();
    //     $id = $reservation->getId();
        
    //     // Submit DELETE request
    //     $this->client->request('DELETE', '/backoffice/reservation/'.$id);

    //     $this->assertResponseRedirects('/backoffice/reservation/');
        
    //     // Follow the redirect
    //     $this->client->followRedirect();
        
    //     // Verify the reservation was deleted
    //     $deletedReservation = $this->entityManager->find(Reservation::class, $id);
    //     $this->assertNull($deletedReservation);
    // }

    // public function testEditOptionsGaranties(): void
    // {
    //     $this->loginAdminUser();
        
    //     // Create a test reservation if none exists
    //     $reservation = $this->createTestReservation();
        
    //     $this->client->request('GET', '/backoffice/reservation/modifier/options-garanties/'.$reservation->getId());

    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorExists('form'); // Form for options and guarantees
    // }

    // public function testAddConducteur(): void
    // {
    //     $this->loginAdminUser();
        
    //     // Create a test reservation if none exists
    //     $reservation = $this->createTestReservation();
        
    //     // Test GET request to add conductor page
    //     $this->client->request('GET', '/backoffice/reservation/ajouter-conducteur/'.$reservation->getId());

    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorExists('form'); // Form for adding conductor
    // }

    // public function testArchive(): void
    // {
    //     $this->loginAdminUser();
        
    //     // Create a test reservation if none exists
    //     $reservation = $this->createTestReservation();
        
    //     $this->client->request('POST', '/backoffice/reservation/archiver/'.$reservation->getId());

    //     $this->assertResponseRedirects('/backoffice/reservation/');
    //     $this->client->followRedirect();
        
    //     // Verify reservation is archived
    //     $this->entityManager->refresh($reservation);
    //     $this->assertTrue($reservation->getArchived());
    // }

    // public function testAnnulation(): void
    // {
    //     $this->loginAdminUser();
        
    //     // Create a test reservation if none exists
    //     $reservation = $this->createTestReservation();
        
    //     $crawler = $this->client->request('GET', '/backoffice/reservation/details/'.$reservation->getId());
        
    //     // Find the annulation form or link
    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorExists('.modalAnnulation'); // Annulation modal should exist
    // }

    // public function testRetourAnticipe(): void
    // {
    //     $this->loginAdminUser();
        
    //     // Create a test reservation if none exists
    //     $reservation = $this->createTestReservation();
        
    //     $this->client->request('GET', '/backoffice/reservation/retour-anticipe/'.$reservation->getId());

    //     $this->assertResponseRedirects('/backoffice/reservation/details/'.$reservation->getId());
    // }

    // // public function testInfosClientEdit(): void
    // // {
    // //     $this->loginAdminUser();
        
    // //     // Create a test reservation if none exists
    // //     $reservation = $this->createTestReservation();
        
    // //     $crawler = $this->client->request('GET', '/backoffice/reservation/modifier/'.$reservation->getId().'/infos-client/');

    // //     $this->assertResponseIsSuccessful();
    // //     $this->assertSelectorTextContains('h2', 'Informations client');
    // // }

    /**
     * Helper method to login admin user for tests
     */
    private function loginAdminUser(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['mail' => 'rija0340@gmail.com']) ?: 
                $userRepository->findOneBy(['mail' => 'rija0340@gmail.com']) ?: 
                $userRepository->findOneBy([]); // fallback to any user
        
        if ($user) {
            $this->client->loginUser($user);
        } else {
            // If no user exists, try the login approach like in BackofficeAuthTest
            $crawler = $this->client->request('GET', '/connexion');
            $form = $crawler->selectButton('Se connecter')->form([
                'email' => 'rija0340@gmail.com',
                'password' => '34008927',
            ]);
            $this->client->submit($form);
            $this->client->followRedirect();
            if ($this->client->getResponse()->isRedirect()) {
                $this->client->followRedirect();
            }
        }
    }

    // /**
    //  * Helper method to create a test reservation
    //  */
    // private function createTestReservation(): Reservation
    // {
    //     $reservation = $this->entityManager->getRepository(Reservation::class)->findOneBy([]);
        
    //     if (!$reservation) {
    //         // Create a basic reservation for testing
    //         $user = $this->entityManager->getRepository(User::class)->findOneBy([]) ?: 
    //                 $this->createTestUser();
            
    //         $vehicule = $this->entityManager->getRepository(Vehicule::class)->findOneBy([]) ?: 
    //                     $this->createTestVehicule();
            
    //         $reservation = new Reservation();
    //         $reservation->setClient($user)
    //                    ->setDateReservation(new \DateTime())
    //                    ->setDateDebut(new \DateTime('tomorrow 10:00'))
    //                    ->setDateFin(new \DateTime('tomorrow 18:00'))
    //                    ->setAgenceDepart('Agence Depart')
    //                    ->setAgenceRetour('Agence Retour')
    //                    ->setVehicule($vehicule)
    //                    ->setPrix(100.00)
    //                    ->setReference('TEST001');
            
    //         $this->entityManager->persist($reservation);
    //         $this->entityManager->flush();
    //     }
        
    //     return $reservation;
    // }

    // /**
    //  * Helper method to create a test user
    //  */
    // private function createTestUser(): User
    // {
    //     $user = new User();
    //     $user->setNom('Test')
    //          ->setPrenom('User')
    //          ->setMail('test@example.com')
    //          ->setPassword('password'); // This might need proper encoding in real usage
        
    //     $this->entityManager->persist($user);
    //     $this->entityManager->flush();
        
    //     return $user;
    // }

    // /**
    //  * Helper method to create a test vehicle
    //  */
    // private function createTestVehicule(): Vehicule
    // {
    //     $vehicule = new Vehicule();
    //     $vehicule->setMarque('Test Marque')
    //             ->setModele('Test Modele')
    //             ->setImmatriculation('TEST123')
    //             ->setCategorie('Berline');
        
    //     $this->entityManager->persist($vehicule);
    //     $this->entityManager->flush();
        
    //     return $vehicule;
    // }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}