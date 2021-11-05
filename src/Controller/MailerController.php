<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerController extends AbstractController
{
    /**
     * @Route("/mailer", name="mailer")
     */
    public function index(): Response
    {
        return $this->render('mailer/index.html.twig', [
            'controller_name' => 'MailerController',
        ]);
    }


    /**
     * @Route("/email", name="email")
     */
    public function sendEmail(String $dest, MailerInterface $mailer): Response
    {
        $expeditaire = "joel@joellocation.com";
        $destinataire = $dest;
        $objet = "Rappel de payement pour la location";
        $text =  "Bonjour nous vous rappelons que vous devriez payer l'intégralité de votre location avant la date du début de location";
        $email = (new Email())
            ->from($expeditaire)
            ->to($destinataire)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($objet)
            ->text($text);
            //->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        // eto no atao ny fonction redirection rehefa avy nandefa mail
        return $this->render('mailer/index.html.twig', [
            'controller_name' => 'MailerController',
        ]);
    }
}
