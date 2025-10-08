<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class BackofficeAuthTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAccessProtectedRouteUnauthenticated(): void
    {
        $this->client->request('GET', '/backoffice/reservation/');
        $this->assertResponseRedirects(); // Redirects to login
        $this->client->followRedirect();
        $this->assertRouteSame('app_login'); // Assuming login route name is 'app_login'
        $this->assertResponseIsSuccessful();
    }

    public function testLoginAsAdmin(): void
    {
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

    // public function testLoginWithInvalidCredentials(): void
    // {
    //     $crawler = $this->client->request('GET', '/connexion');

    //     $this->assertResponseIsSuccessful();

    //     $form = $crawler->selectButton('Se connecter')->form([
    //         'email' => 'rija0340@gmail.com',
    //         'password' => 'wrong_password',
    //     ]);

    //     $this->client->submit($form);

    //     $this->assertResponseIsSuccessful(); // Stays on login with error (no redirect)
    //     $this->assertSelectorTextContains('div.alert-danger', 'Identifiants invalides'); // Adjust if exact message differs (e.g., 'Les identifiants d\'authentification ne sont pas valides.')
    // }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}