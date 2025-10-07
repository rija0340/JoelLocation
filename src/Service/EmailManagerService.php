<?php

namespace App\Service;

use App\Entity\Devis;
use App\Entity\Reservation;
use App\Entity\User;
use App\Service\EmailLoggerService;
use App\Service\EmailNotifierService;
use App\Service\EmailService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailManagerService
{
    private $emailService;
    private $emailLoggerService;
    private $emailNotifierService;
    private $router;
    private $site;

    public function __construct(
        EmailService $emailService,
        EmailLoggerService $emailLoggerService,
        EmailNotifierService $emailNotifierService,
        UrlGeneratorInterface $router,
        Site $site
    ) {
        $this->emailService = $emailService;
        $this->emailLoggerService = $emailLoggerService;
        $this->emailNotifierService = $emailNotifierService;
        $this->router = $router;
        $this->site = $site;
    }

    public function sendDevis(Request $request, Devis $devis)
    {
        $this->sendDocument($request, $devis, 'devis');
    }

    public function sendContrat(Request $request, Reservation $resa)
    {
        $this->sendDocument($request, $resa, 'contrat');
    }

    public function sendFacture(Request $request, Reservation $resa)
    {
        $this->sendDocument($request, $resa, 'facture');
    }

    public function sendValidationInscription(User $user, string $token)
    {
        try {
            $emailSent = $this->emailService->sendValidationEmail(
                $user->getMail(),
                $user->getNom(),
                $token
            );

            if ($emailSent) {
                $this->emailLoggerService->logValidationEmail($user, 'success');
                $this->emailNotifierService->notifyValidationSent();
            } else {
                $this->emailLoggerService->logValidationEmail($user, 'failed');
                $this->emailNotifierService->notifyValidationNotSent();
            }
        } catch (\Exception $e) {
            $this->emailLoggerService->logValidationEmail($user, 'failed', $e->getMessage());
            $this->emailNotifierService->notifyValidationNotSent();
            
            // Re-throw if needed for further handling by caller
            throw $e;
        }
    }

    private function sendDocument(Request $request, $entity, string $type)
    {
        $baseUrl = $this->site->getBaseUrl($request);
        $route = $type . '_pdf';
        $documentLink = $baseUrl . $this->router->generate($route, ['hashedId' => sha1($entity->getId())]);

        $name = $entity->getClient()->getNom();
        $email = $entity->getClient()->getMail();
        $reference = ($entity instanceof Devis) ? $entity->getNumero() : $entity->getReference();

        // Prepare photo attachments for contracts
        $photos = [];
        if ($type === 'contrat' && $entity instanceof Reservation) {
            foreach ($entity->getPhotos() as $photo) {
                // Vérifier que le fichier existe avant de l'ajouter
                $photoPath = 'uploads/reservation_photos/' . $photo->getImage();
                if ($photo->getImage() && file_exists($photoPath)) {
                    $photos[] = $photoPath; // Juste le chemin
                }
            }
        }

        try {
            $method = 'send' . ucfirst($type);
            $emailSent = false;
            
            if ($type === 'contrat' && !empty($photos)) {
                $emailSent = $this->emailService->$method($email, $name, ucfirst($type), $documentLink, $photos);
            } else {
                $emailSent = $this->emailService->$method($email, $name, ucfirst($type), $documentLink);
            }

            if ($emailSent) {
                $this->emailLoggerService->logEmail($entity, $type, "success");
                
                // Notify based on type
                switch($type) {
                    case 'devis':
                        $this->emailNotifierService->notifyDevisSent($reference);
                        break;
                    case 'contrat':
                        $this->emailNotifierService->notifyContratSent($reference);
                        break;
                    case 'facture':
                        $this->emailNotifierService->notifyFactureSent($reference);
                        break;
                }
            } else {
                $this->emailLoggerService->logEmail($entity, $type, "failed");
                
                // Notify based on type
                switch($type) {
                    case 'devis':
                        $this->emailNotifierService->notifyDevisNotSent($reference);
                        break;
                    case 'contrat':
                        $this->emailNotifierService->notifyContratNotSent($reference);
                        break;
                    case 'facture':
                        $this->emailNotifierService->notifyFactureNotSent($reference);
                        break;
                }
            }
        } catch (\Exception $e) {
            $this->emailLoggerService->logEmail($entity, $type, "failed", $e->getMessage());
            
            // Notify based on type
            switch($type) {
                case 'devis':
                    $this->emailNotifierService->notifyDevisNotSent($reference);
                    break;
                case 'contrat':
                    $this->emailNotifierService->notifyContratNotSent($reference);
                    break;
                case 'facture':
                    $this->emailNotifierService->notifyFactureNotSent($reference);
                    break;
            }
        }
    }

    public function sendContact(array $data)
    {
        try {
            $emailSent = $this->emailService->sendContact($data);

            if ($emailSent) {
                $this->emailLoggerService->logContactEmail($data, 'success');
                $this->emailNotifierService->notifyContactSent();
            } else {
                $this->emailLoggerService->logContactEmail($data, 'failed');
                $this->emailNotifierService->notifyContactNotSent();
            }
        } catch (\Exception $e) {
            $this->emailLoggerService->logContactEmail($data, 'failed', $e->getMessage());
            $this->emailNotifierService->notifyContactNotSent();
        }
    }

    public function sendPaiementConfirmation(Reservation $reservation, float $montant): void
    {
        try {
            $client = $reservation->getClient();
            $vehicule = $reservation->getVehicule()->getMarque() . " " . $reservation->getVehicule()->getModele();

            $emailSent = $this->emailService->sendPaiementConfirmation(
                $client->getMail(),
                $client->getNom(),
                $reservation->getReference(),
                $montant,
                $vehicule,
                $reservation->getDateDebut(),
                $reservation->getDateFin()
            );

            if ($emailSent) {
                $this->emailLoggerService->logPaiementConfirmationEmail($reservation, 'success');
                $this->emailNotifierService->notifyPaiementConfirmationSent();
            } else {
                $this->emailLoggerService->logPaiementConfirmationEmail($reservation, 'failed');
                $this->emailNotifierService->notifyPaiementConfirmationNotSent();
            }
        } catch (\Exception $e) {
            $this->emailLoggerService->logPaiementConfirmationEmail($reservation, 'failed', $e->getMessage());
            $this->emailNotifierService->notifyPaiementConfirmationNotSent();
        }
    }

    public function sendAppelPaiement(Reservation $reservation)
    {
        try {
            $client = $reservation->getClient();
            $vehicule = $reservation->getVehicule()->getMarque() . " " . $reservation->getVehicule()->getModele();

            $emailSent = $this->emailService->sendAppelPaiement(
                $client->getMail(),
                $client->getNom(),
                $reservation->getReference(),
                $reservation->getPrix(),
                $vehicule,
                $reservation->getDateDebut(),
                $reservation->getDateFin(),
                $reservation->getDateReservation(),
                $reservation->getPrix(),
                $reservation->getSommePaiements()
            );

            if ($emailSent) {
                $this->emailLoggerService->logAppelPaiementEmail($reservation, 'success');
                $this->emailNotifierService->notifyAppelPaiementSent();
            } else {
                $this->emailLoggerService->logAppelPaiementEmail($reservation, 'failed');
                $this->emailNotifierService->notifyAppelPaiementNotSent();
            }
        } catch (\Exception $e) {
            $this->emailLoggerService->logAppelPaiementEmail($reservation, 'failed', $e->getMessage());
            $this->emailNotifierService->notifyAppelPaiementNotSent();
            
            throw $e;
        }
    }
}