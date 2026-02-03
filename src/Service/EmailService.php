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

class EmailService
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
    // private const SENDER = "contact@joellocation.com";
    private $senderEmail;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        Mailjet $mailjet,
        LoggerInterface $emailLogger,
        TransportInterface $mainTransport,
        TransportInterface $yahooTransport,
        RequestStack $requestStack,
        Site $site,
        string $senderEmail
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->mailjet = $mailjet;
        $this->mainTransport = $mainTransport;
        $this->yahooTransport = $yahooTransport;
        $this->emailLogger = $emailLogger;
        $this->requestStack = $requestStack;
        $this->site = $site;
        $this->senderEmail = $senderEmail;
        $this->context = [
            'phone_number1' => '06 90 73 76 74',
            'phone_number2' => '07 67 32 14 47',
            'website_url' => 'https://joellocation.com/',
            'facebook_url' => 'https://www.facebook.com/joellocation/',
            'instagram_url' => 'https://www.instagram.com/joel.location/',
            'youtube_url' => 'https://youtube.com/channel/UCMZZRNgOmDBIghZwg4tTQJQ',
        ];
    }

    public function send(string $to, string $subject, string $template, array $context = [], array $attachments = [])
    {
        $emailContext = array_merge($this->context, $context);
        return $this->createBaseEmailAndSend($to, $subject, $template, $attachments, $emailContext);
    }

    public function sendDevis(string $to, string $name, string $subject, $devisLink, array $attachments = [])
    {
        $context = [
            'devisLink' => $devisLink,
            'name' => $name,
        ];
        return $this->send($to, $subject, 'admin/templates_email/devis.html.twig', $context, $attachments);
    }

    public function sendContrat(string $to, string $name, string $subject, $contratLink, array $photos = [])
    {
        $context = [
            'contratLink' => $contratLink,
            'name' => $name,
        ];
        return $this->send($to, $subject, 'admin/templates_email/contrat.html.twig', $context, $photos);
    }

    public function sendFacture(string $to, string $name, string $subject, $factureLink, array $attachments = [])
    {
        $context = [
            'factureLink' => $factureLink,
            'name' => $name,
        ];
        return $this->send($to, $subject, 'admin/templates_email/facture.html.twig', $context, $attachments);
    }
    public function sendAvoir(string $to, string $name, string $subject, $avoirLink, $montant, array $attachments = [])
    {
        $context = [
            'avoirLink' => $avoirLink,
            'name' => $name,
            'montant' => $montant
        ];
        return $this->send($to, $subject, 'admin/templates_email/avoir.html.twig', $context, $attachments);
    }
    public function sendValidationEmail(string $to, string $name, string $token)
    {
        $validationUrl = $this->generateValidationUrl($token);
        $context = [
            'name' => $name,
            'validationUrl' => $validationUrl,
        ];
        return $this->send($to, "Confirmation de votre inscription", 'admin/templates_email/validation_inscription.html.twig', $context);
    }

    public function sendContact(array $data)
    {
        $emailContext = array_merge($this->context, $data);
        return $this->send($this->senderEmail, "Contact", 'admin/templates_email/formulaire_contact.html.twig', $emailContext);
    }

    public function sendPaiementConfirmation(string $to, string $name, string $reference, float $montant, string $vehicule, \DateTime $dateDebut, \DateTime $dateFin)
    {
        $context = [
            'name' => $name,
            'reference' => $reference,
            'montant' => number_format($montant, 2, ',', ' ') . ' €',
            'vehicule' => $vehicule,
            'dateDebut' => $dateDebut->format('d/m/Y H:i'),
            'dateFin' => $dateFin->format('d/m/Y H:i'),
        ];
        return $this->send($to, "Confirmation de votre paiement", 'admin/templates_email/confirmation_paiement.html.twig', $context);
    }

    public function sendAppelPaiement(string $to, string $name, string $reference, float $montant, string $vehicule, \DateTime $dateDebut, \DateTime $dateFin, \DateTime $dateReservation, float $prixTotal, float $sommePaiements)
    {
        $context = [
            'name' => $name,
            'reference' => $reference,
            'montant' => number_format($montant, 2, ',', ' ') . ' €',
            'vehicule' => $vehicule,
            'dateDebut' => $dateDebut->format('d/m/Y H:i'),
            'dateFin' => $dateFin->format('d/m/Y H:i'),
            'dateReservation' => $dateReservation->format('d/m/Y H:i'),
            'prixTotal' => number_format($prixTotal, 2, ',', ' ') . ' €',
            'sommePaiements' => number_format($sommePaiements, 2, ',', ' ') . ' €',
            'montantRestant' => number_format($prixTotal - $sommePaiements, 2, ',', ' ') . ' €',
        ];
        return $this->send($to, "Appel à paiement", 'admin/templates_email/appel_paiement.html.twig', $context);
    }

    public function sendSignatureRequest(string $to, string $name, string $signatureLink, string $reference, string $documentLabel = 'contrat')
    {
        $context = [
            'name' => $name,
            'signatureLink' => $signatureLink,
            'reference' => $reference,
            'documentLabel' => $documentLabel,
        ];
        return $this->send($to, "Signature de votre $documentLabel - Réservation #$reference", 'admin/templates_email/signature_request.html.twig', $context);
    }

    public function notifyAdminContractSigned(string $reference, string $clientName, string $adminLink)
    {
        $context = [
            'reference' => $reference,
            'clientName' => $clientName,
            'adminLink' => $adminLink,
        ];
        // Send to admin email address instead of sender
        $adminEmail = $_ENV['ADMIN_EMAIL'] ?? $this->senderEmail;
        return $this->send($adminEmail, "Contrat signé par le client - #$reference", 'admin/templates_email/admin_contract_signed.html.twig', $context);
    }

    private function generateValidationUrl(string $token): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $this->site->getBaseUrl($request);
        return $baseUrl . '/validation-email/' . $token;
    }

    private function createBaseEmailAndSend(string $to, string $subject, string $template, array $attachments = [], array $emailContext = [])
    {
        // Determine if logos should be included based on template
        $includeLogos = !$this->shouldSkipLogos($template);

        if (strpos($to, '@yahoo.com') !== false) {
            $emailContext['replyToEmail'] = 'contact@joellocation.com';

            if ($includeLogos) {
                $htmlContent = $this->twig->render($template, array_merge($emailContext, [
                    'logo' => 'images/Joel-Location-new.png',
                    'facebook-icon' => 'images/logos/icons8-facebook-48.png',
                    'instagram-icon' => 'images/logos/icons8-instagram-48.png',
                    'youtube-icon' => 'images/logos/icons8-youtube-48.png'
                ]));

                $email = (new Email())
                    ->from($this->senderEmail)
                    ->to($to)
                    ->subject($subject)
                    ->html($htmlContent)
                    ->embed(fopen('images/Joel-Location-new.png', 'r'), 'logo')
                    ->embed(fopen('images/logos/icons8-facebook-48.png', 'r'), 'facebook-icon')
                    ->embed(fopen('images/logos/icons8-instagram-48.png', 'r'), 'instagram-icon')
                    ->embed(fopen('images/logos/icons8-youtube-48.png', 'r'), 'youtube-icon');
            } else {
                $htmlContent = $this->twig->render($template, $emailContext);

                $email = (new Email())
                    ->from($this->senderEmail)
                    ->to($to)
                    ->subject($subject)
                    ->html($htmlContent);
            }

            // Add attachments
            if (!empty($attachments)) {
                foreach ($attachments as $attachmentPath) {
                    if (file_exists($attachmentPath)) {
                        $email->attachFromPath($attachmentPath);
                    }
                }
            }

            try {
                $this->yahooTransport->send($email);
                return true;
            } catch (\Exception $e) {
                $this->emailLogger->error('Failed to send email via Yahoo transport: ' . $e->getMessage(), [
                    'recipient' => $to,
                    'subject' => $subject,
                    'exception' => $e
                ]);
                return false;
            }
        } else {
            $smtpMail = (new TemplatedEmail())
                ->from($this->senderEmail)
                ->to($to)
                ->subject($subject)
                ->htmlTemplate($template)
                ->context($emailContext);

            // Only embed logos if not skipping them
            if ($includeLogos) {
                $smtpMail
                    ->embedFromPath('images/Joel-Location-new.png', 'logo', 'image/png')
                    ->embedFromPath('images/logos/icons8-facebook-48.png', 'facebook-icon', 'image/png')
                    ->embedFromPath('images/logos/icons8-instagram-48.png', 'instagram-icon', 'image/png')
                    ->embedFromPath('images/logos/icons8-youtube-48.png', 'youtube-icon', 'image/png');
            }

            // Add attachments
            foreach ($attachments as $attachmentPath) {
                if (file_exists($attachmentPath)) {
                    $smtpMail->attachFromPath($attachmentPath);
                }
            }

            try {
                $this->mainTransport->send($smtpMail);
                return true;
            } catch (\Exception $e) {
                $this->emailLogger->error('Failed to send email via main transport: ' . $e->getMessage(), [
                    'recipient' => $to,
                    'subject' => $subject,
                    'exception' => $e
                ]);
                return false;
            }
        }
    }

    private function shouldSkipLogos(string $template): bool
    {
        // List of templates that should not include logos
        $templatesWithoutLogos = [
            'admin/templates_email/admin_contract_signed.html.twig',
            // Add other templates here if needed
        ];

        return in_array($template, $templatesWithoutLogos);
    }
}