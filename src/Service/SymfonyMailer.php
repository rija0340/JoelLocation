<?php

namespace App\Service;

use App\Classe\Mailjet;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
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
    private $emailLogger;
    private $requestStack;
    private $site;
    private const SENDER = "contact@joellocation.com";

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        Mailjet $mailjet,
        LoggerInterface $emailLogger,
        TransportInterface $mainTransport,
        TransportInterface $yahooTransport,
        RequestStack $requestStack,
        Site $site
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->mailjet = $mailjet;
        $this->mainTransport = $mainTransport;
        $this->yahooTransport = $yahooTransport;
        $this->emailLogger = $emailLogger;
        $this->requestStack = $requestStack;
        $this->site = $site;
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
    //pour validation inscription
    public function sendValidationEmail(string $to, string $name, string $token)
    {
        $this->context['name'] = $name;
        $this->context['validationUrl'] = $this->generateValidationUrl($token);

        $email = $this->createBaseEmailAndSend(
            $to,
            "Confirmation de votre inscription",
            'admin/templates_email/validation_inscription.html.twig'
        );
    }

    public function sendPaiementConfirmation(string $to, string $name, string $reference, float $montant, string $vehicule, \DateTime $dateDebut, \DateTime $dateFin)
    {
        $this->context['name'] = $name;
        $this->context['reference'] = $reference;
        $this->context['montant'] = number_format($montant, 2, ',', ' ') . ' €';
        $this->context['vehicule'] = $vehicule;
        $this->context['dateDebut'] = $dateDebut->format('d/m/Y H:i');
        $this->context['dateFin'] = $dateFin->format('d/m/Y H:i');

        $email = $this->createBaseEmailAndSend(
            $to,
            "Confirmation de votre paiement",
            'admin/templates_email/confirmation_paiement.html.twig'
        );
    }

    private function generateValidationUrl(string $token): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $this->site->getBaseUrl($request);
        return $baseUrl . '/validation-email/' . $token;
    }

    private function createBaseEmailAndSend(string $to, string $subject, string $template)
    {
        if (strpos($to, '@yahoo.com') !== false) {
            $this->context['replyToEmail'] = 'contact@joellocation.com';
            // Render template content first
            $htmlContent = $this->twig->render($template, array_merge($this->context, [
                'logo' => 'images/Joel-Location-new.png',
                'facebook-icon' => 'images/logos/icons8-facebook-48.png',
                'instagram-icon' => 'images/logos/icons8-instagram-48.png',
                'youtube-icon' => 'images/logos/icons8-youtube-48.png'
            ]));
            // die('mandalo ato tsika');
            $email = (new Email())
                ->from('joel.mandret@yahoo.com')
                ->to($to)
                // ->replyTo('contact@joellocation.com')
                ->subject($subject)
                ->html($htmlContent)
                ->embed(fopen('images/Joel-Location-new.png', 'r'), 'logo')
                ->embed(fopen('images/logos/icons8-facebook-48.png', 'r'), 'facebook-icon')
                ->embed(fopen('images/logos/icons8-instagram-48.png', 'r'), 'instagram-icon')
                ->embed(fopen('images/logos/icons8-youtube-48.png', 'r'), 'youtube-icon');

            try {
                $this->yahooTransport->send($email);
            } catch (\Exception $e) {
                $this->emailLogger->error('Failed to send email via Yahoo transport: ' . $e->getMessage(), [
                    'recipient' => $to,
                    'subject' => $subject,
                    'exception' => $e
                ]);
                // Optionally return false or throw a custom exception if needed
            }
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

            try {
                $this->mainTransport->send($smtpMail);
            } catch (\Exception $e) {
                $this->emailLogger->error('Failed to send email via main transport: ' . $e->getMessage(), [
                    'recipient' => $to,
                    'subject' => $subject,
                    'exception' => $e
                ]);
                // Optionally return false or throw a custom exception if needed
            }
        }
    }
}
