<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SymfonyMailer
{
    private $mailer;
    private $context;
    private const SENDER = "contact@joellocation.com";

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->context = [
            'phone_number1' => '06 90 73 76 74',
            'phone_number2' => '07 67 32 14 47',
            'website_url' => 'https://joellocation.com/',
            'facebook_url' => 'https://www.facebook.com/joellocation/',
            'instagram_url' => 'https://www.instagram.com/joel.location/',
            'youtube_url' => 'https://youtube.com/channel/UCMZZRNgOmDBIghZwg4tTQJQ',
        ];
    }

    public function send(string $to, string $subject)
    {
        $email = $this->createBaseEmail($to, $subject, 'admin/templates_email/devis.html.twig');
        $this->mailer->send($email);
    }

    public function sendContact($data)
    {
        $this->context = array_merge($this->context, $data);
        $email = $this->createBaseEmail(self::SENDER, "Contact", 'admin/templates_email/formulaire_contact.html.twig');
        $this->mailer->send($email);
    }

    public function sendDevis(string $to, string $name, string $subject, $devisLink)
    {
        $this->context['devisLink'] = $devisLink;
        $this->context['name'] = $name;
        $email = $this->createBaseEmail($to, $subject, 'admin/templates_email/devis.html.twig');
        $this->mailer->send($email);
    }

    public function sendContrat(string $to, string $name, string $subject, $contratLink)
    {
        $this->context['contratLink'] = $contratLink;
        $this->context['name'] = $name;
        $email = $this->createBaseEmail($to, $subject, 'admin/templates_email/contrat.html.twig');
        $this->mailer->send($email);
    }

    public function sendFacture(string $to, string $name, string $subject, $factureLink)
    {
        $this->context['factureLink'] = $factureLink;
        $this->context['name'] = $name;
        $email = $this->createBaseEmail($to, $subject, 'admin/templates_email/facture.html.twig');
        $this->mailer->send($email);
    }

    private function createBaseEmail(string $to, string $subject, string $template): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from(self::SENDER)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->embedFromPath('images/Joel-Location-new.png', 'logo', 'image/png')
            ->embedFromPath('images/logos/icons8-facebook-48.png', 'facebook-icon', 'image/png')
            ->embedFromPath('images/logos/icons8-instagram-48.png', 'instagram-icon', 'image/png')
            ->embedFromPath('images/logos/icons8-youtube-48.png', 'youtube-icon', 'image/png')
            ->context($this->context);
    }
}
