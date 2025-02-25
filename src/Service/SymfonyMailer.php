<?php

namespace App\Service;

use App\Classe\Mailjet;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Twig\Environment;

class SymfonyMailer
{
    private $mailer;
    private $context;
    private $twig;
    private $mainTransport;
    private $yahooTransport;
    private $mailjet;
    private const SENDER = "contact@joellocation.com";

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        Mailjet $mailjet,
        TransportInterface $mainTransport,
        TransportInterface $yahooTransport
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->mailjet = $mailjet;
        $this->mainTransport = $mainTransport;
        $this->yahooTransport = $yahooTransport;
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
        $email = $this->createBaseEmailAndSend($to, $subject, 'admin/templates_email/devis.html.twig');
        // $this->mailer->send($email);
    }

    public function sendContact($data)
    {
        $this->context = array_merge($this->context, $data);
        $email = $this->createBaseEmailAndSend(self::SENDER, "Contact", 'admin/templates_email/formulaire_contact.html.twig');
        // $this->mailer->send($email);
    }

    public function sendDevis(string $to, string $name, string $subject, $devisLink)
    {
        $this->context['devisLink'] = $devisLink;
        $this->context['name'] = $name;
        $email = $this->createBaseEmailAndSend($to, $subject, 'admin/templates_email/devis.html.twig');
        // $this->mailer->send($email);
    }

    public function sendContrat(string $to, string $name, string $subject, $contratLink)
    {
        $this->context['contratLink'] = $contratLink;
        $this->context['name'] = $name;
        $email = $this->createBaseEmailAndSend($to, $subject, 'admin/templates_email/contrat.html.twig');
        // $this->mailer->send($email);
    }

    public function sendFacture(string $to, string $name, string $subject, $factureLink)
    {
        $this->context['factureLink'] = $factureLink;
        $this->context['name'] = $name;
        $email = $this->createBaseEmailAndSend($to, $subject, 'admin/templates_email/facture.html.twig');
        // $this->send($email, $type);
    }

    private function createBaseEmailAndSend(string $to, string $subject, string $template)
    {
        $to = "rakotoarinelinarija@yahoo.com";
        if (strpos($to, '@yahoo.com') !== false) {
            // Render template content first
            $htmlContent = $this->twig->render($template, array_merge($this->context, [
                'logo' => 'images/Joel-Location-new.png',
                'facebook-icon' => 'images/logos/icons8-facebook-48.png',
                'instagram-icon' => 'images/logos/icons8-instagram-48.png',
                'youtube-icon' => 'images/logos/icons8-youtube-48.png'
            ]));
            $email = (new Email())
                ->from('rakotoarinelinarija@yahoo.com')
                ->to($to)
                ->subject($subject)
                ->html($htmlContent)
                ->embed(fopen('images/Joel-Location-new.png', 'r'), 'logo')
                ->embed(fopen('images/logos/icons8-facebook-48.png', 'r'), 'facebook-icon')
                ->embed(fopen('images/logos/icons8-instagram-48.png', 'r'), 'instagram-icon')
                ->embed(fopen('images/logos/icons8-youtube-48.png', 'r'), 'youtube-icon');

            $this->yahooTransport->send($email);
        } else {
            $smtpMail = (new TemplatedEmail())
                ->from(self::SENDER)
                ->to($to)
                ->subject($subject)
                ->htmlTemplate($template)
                ->embedFromPath('images/Joel-Location-new.png', 'logo', 'image/png')
                ->embedFromPath('images/logos/icons8-facebook-48.png', 'facebook-icon', 'image/png')
                ->embedFromPath('images/logos/icons8-instagram-48.png', 'instagram-icon', 'image/png')
                ->embedFromPath('images/logos/icons8-youtube-48.png', 'youtube-icon', 'image/png')
                ->context($this->context);

            $this->mainTransport->send($smtpMail);
        }
    }
}
