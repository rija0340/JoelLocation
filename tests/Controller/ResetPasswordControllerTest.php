<?php

namespace App\Tests\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ResetPasswordControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testResetPasswordFlow(): void
    {
        // 1. Préparer un utilisateur existant
        $email = 'reset_' . uniqid() . '@example.com';
        $user = new User();
        $user->setMail($email);
        $user->setPassword('old_password');
        $user->setNom('TestReset');
        $user->setPrenom('User');
        $user->setRoles(['ROLE_CLIENT']);
        // Ajouter les champs requis par l'entité User (adapté selon votre entité)
        // D'après les tests précédents, il semble que nom/prenom/mail suffisent ou d'autres champs non nullable ?
        // Regardons InscriptionControllerTest.php que j'ai créé : il utilise ClientRegisterType qui a beaucoup de champs.
        // Mais pour l'entité User brute, cela dépend des contraintes DB. 
        // Je vais remplir un minimum safe.
        $user->setSexe('masculin');
        $user->setTelephone('0123456789');
        $user->setAdresse('1 rue test');
        $user->setCodePostal('75000');
        $user->setVille('Paris');
        $user->setPresence(true);
        $user->setDateInscription(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // 2. Demander la réinitialisation (Page "Mot de passe oublié")
        $crawler = $this->client->request('GET', '/mot-de-passe-oublie');
        $this->assertResponseIsSuccessful();

        // Remplir le formulaire (C'est un formulaire simple sans classe apparemment, ou juste un input name="email" d'après le controller)
        // Le controller fait $request->get('email').
        // Je vais simuler la soumission du formulaire ou directement une requête POST si le crawler ne trouve pas de bouton unique.
        // Cherchons un bouton "Valider" ou "Envoyer" ou submit.

        // Supposons qu'il y a un bouton submit. S'il n'y en a pas de clair, submitForm est plus sûr
        // Mais je peux faire une requête POST directe pour être sûr.
        $this->client->request('POST', '/mot-de-passe-oublie', ['email' => $email]);

        $this->assertResponseIsSuccessful(); // Le controller retourne index.html.twig avec un flash
        // Vérifier le message flash
        // Note: Le controller utilise FlashyNotifier ($this->flashy->success(...))
        // Flashy stocke souvent en session, mais le sélecteur dépend de l'implémentation de flashy dans le template.
        // On va assumer que la réponse contient le texte success.
        $this->assertSelectorTextContains('body', 'Vous allez recevoir un email');

        // 3. Récupérer le token généré en base
        /** @var ResetPassword $resetPassword */
        $resetPassword = $this->entityManager->getRepository(ResetPassword::class)->findOneBy(['user' => $user], ['createdAt' => 'DESC']);
        $this->assertNotNull($resetPassword, 'Le token de reset password aurait dû être généré.');
        $token = $resetPassword->getToken();

        // 4. Accéder à la page de modification du mot de passe
        $crawler = $this->client->request('GET', '/modifier-mon-mot-de-passe-oublie/' . $token);
        $this->assertResponseIsSuccessful();

        // 5. Soumettre le nouveau mot de passe
        // Le controller attend $request->request->get('reset_password')['new_password']['first']
        // Form name: reset_password

        $form = $crawler->selectButton('Mettre à jour mon mot de passe')->form([
            'reset_password[new_password][first]' => 'NewPassword123!',
            'reset_password[new_password][second]' => 'NewPassword123!',
        ]);

        $this->client->submit($form);

        // 6. Vérifier la redirection vers login et le succès
        $this->assertResponseRedirects('/connexion');
        $this->client->followRedirect();

        // Vérifier le message de succès (Flashy ou FlashBag standard)
        // Le controller fait $this->addFlash('message', ...) ET $this->flashy->success(...)
        // Donc on peut tester la présence du message.
        $this->assertSelectorTextContains('body', 'Votre mot de passe a bien été mise à jour');

        // Vérifier que le mot de passe a changé (hash différent de 'old_password' - bien que old_password n'était pas hashé dans mon setup test rapide,
        // mais le nouveau le sera par le controller).
        // Re-fetch user to get updated password
        $updatedUser = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
        $this->assertNotEquals('old_password', $updatedUser->getPassword());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
