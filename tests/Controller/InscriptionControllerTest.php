<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class InscriptionControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testInscription(): void
    {
        // 1. Accéder à la page d'inscription
        $crawler = $this->client->request('GET', '/inscription');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créez votre compte');

        // 2. Remplir le formulaire avec des "fakes data"
        $email = 'fake_user_' . uniqid() . '@example.com';

        $form = $crawler->selectButton('Créer mon compte')->form([
            'client_register[nom]' => 'TestNom',
            'client_register[prenom]' => 'TestPrenom',
            'client_register[sexe]' => 'masculin',
            'client_register[dateNaissance]' => '1990-01-01',
            'client_register[lieuNaissance]' => 'Paris',
            'client_register[telephone]' => '0123456789',
            'client_register[portable]' => '0612345678',
            'client_register[adresse]' => '123 Rue de Test',
            'client_register[complementAdresse]' => 'Apt 1',
            'client_register[codePostal]' => '75001',
            'client_register[ville]' => 'Paris',
            'client_register[mail]' => $email,
            'client_register[numeroPermis]' => '1234567890',
            'client_register[datePermis]' => '2010-01-01',
            'client_register[villeDelivrancePermis]' => 'Paris',
            'client_register[password][first]' => 'Password123!',
            'client_register[password][second]' => 'Password123!',
        ]);

        // 3. Soumettre le formulaire
        $this->client->submit($form);

        // 4. Vérifier la redirection (succès ou erreur gérée)
        // Note: Notre fix récent redirige vers app_login avec un flash warning si l'email échoue,
        // ou vers app_login tout court si ça réussit (avec message validation envoyée normalement).
        // Dans l'environnement de test, l'envoi d'email peut ne pas échouer si le mailer est mocké ou null,
        // ou échouer si mal configuré. Nous vérifions simplement la redirection vers login.

        $this->assertResponseRedirects('/connexion');
        $this->client->followRedirect();

        // Vérifier qu'on est bien sur la page de login
        $this->assertSelectorExists('form'); // Formulaire de login

        // Vérifier optionnellement si l'utilisateur a été créé en base
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
        $this->assertNotNull($user, 'L\'utilisateur devrait avoir été créé en base de données.');
        $this->assertEquals('TestNom', $user->getNom());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
