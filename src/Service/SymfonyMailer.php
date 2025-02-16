<?php

namespace App\Service;

use App\Classe\Mailjet;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class SymfonyMailer
{
    private $mailer;
    private $context;
    private $twig;
    private $mailjet;
    private const SENDER = "contact@joellocation.com";

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        Mailjet $mailjet
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->mailjet = $mailjet;
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
        $smtpMail =  (new TemplatedEmail())
            ->from(self::SENDER)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->embedFromPath('images/Joel-Location-new.png', 'logo', 'image/png')
            ->embedFromPath('images/logos/icons8-facebook-48.png', 'facebook-icon', 'image/png')
            ->embedFromPath('images/logos/icons8-instagram-48.png', 'instagram-icon', 'image/png')
            ->embedFromPath('images/logos/icons8-youtube-48.png', 'youtube-icon', 'image/png')
            ->context($this->context);
        $mailjetMail = $this->createMailjetEmail($to, $subject, $template);

        //check if mail is yahoo
        if (strpos($to, '@yahoo.com') !== false) {
            $this->mailjet->sendWithMailjet($mailjetMail);
        } else {
            $this->mailer->send($smtpMail);
        }
    }

    private function createMailjetEmail(string $to, string $subject, string $template): array
    {
        return [
            'Messages' => [[
                'From' => [
                    'Email' => self::SENDER,
                    'Name' => 'JOEL LOCATION'
                ],
                'To' => [[
                    'Email' => $to
                ]],
                'Subject' => $subject,
                'HTMLPart' => $this->twig->render($template, $this->context),
                'InlinedAttachments' => [
                    [
                        'ContentType' => 'image/png',
                        'Filename' => 'logo.png',
                        'ContentID' => 'logo',
                        'Base64Content' => base64_encode(file_get_contents('images/Joel-Location-new.png'))
                    ],
                    [
                        'ContentType' => 'image/png',
                        'Filename' => 'facebook-icon.png',
                        'ContentID' => 'facebook-icon',
                        'Base64Content' => base64_encode(file_get_contents('images/logos/icons8-facebook-48.png'))
                    ],
                    [
                        'ContentType' => 'image/png',
                        'Filename' => 'instagram-icon.png',
                        'ContentID' => 'instagram-icon',
                        'Base64Content' => base64_encode(file_get_contents('images/logos/icons8-instagram-48.png'))
                    ],
                    [
                        'ContentType' => 'image/png',
                        'Filename' => 'youtube-icon.png',
                        'ContentID' => 'youtube-icon',
                        'Base64Content' => base64_encode(file_get_contents('images/logos/icons8-youtube-48.png'))
                    ]
                ]
            ]]
        ];
    }
}
