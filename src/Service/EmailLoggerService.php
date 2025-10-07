<?php

namespace App\Service;

use App\Entity\Mail;
use App\Entity\Devis;
use App\Entity\Reservation;
use App\Entity\User;
use DateTime;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class EmailLoggerService
{
    private $em;
    private $emailLogger;
    private $dateHelper;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $emailLogger,
        DateHelper $dateHelper
    ) {
        $this->em = $em;
        $this->emailLogger = $emailLogger;
        $this->dateHelper = $dateHelper;
    }

    public function logEmail($entity, string $type, string $status, ?string $errorMessage = null): void
    {
        $json = json_encode($entity);

        $email = new Mail();
        if (!is_array($entity)) {
            $ref = ($entity instanceof Reservation) ? $entity->getReference() : $entity->getNumero();
            $email->setPrenom($entity->getClient()->getPrenom());
            $email->setNom($entity->getClient()->getNom());
            $email->setMail($entity->getClient()->getMail());
            $email->setObjet($type . " " . $ref);
            $email->setContenu($status);
        } else {
            $email->setPrenom("");
            $email->setNom($entity['nom'] ?? '');
            $email->setMail($entity['emailClient'] ?? '');
            $email->setObjet($type);
            $email->setContenu($json);
        }
        
        // Set date of today 
        $date = $this->dateHelper->dateNow();
        $email->setDateReception($date);
        $this->em->persist($email);
        $this->em->flush();
    }

    public function logDevisEmail(Devis $devis, string $status, ?string $errorMessage = null): void
    {
        $this->logEmail($devis, 'devis', $status, $errorMessage);
    }

    public function logContratEmail(Reservation $reservation, string $status, ?string $errorMessage = null): void
    {
        $this->logEmail($reservation, 'contrat', $status, $errorMessage);
    }

    public function logFactureEmail(Reservation $reservation, string $status, ?string $errorMessage = null): void
    {
        $this->logEmail($reservation, 'facture', $status, $errorMessage);
    }

    public function logValidationEmail(User $user, string $status, ?string $errorMessage = null): void
    {
        $this->logEmail([
            'nom' => $user->getNom(),
            'emailClient' => $user->getMail()
        ], 'validation_inscription', $status, $errorMessage);
    }

    public function logContactEmail(array $data, string $status, ?string $errorMessage = null): void
    {
        $this->logEmail($data, 'contact', $status, $errorMessage);
    }

    public function logPaiementConfirmationEmail(Reservation $reservation, string $status, ?string $errorMessage = null): void
    {
        $this->logEmail($reservation, 'paiement', $status, $errorMessage);
    }

    public function logAppelPaiementEmail(Reservation $reservation, string $status, ?string $errorMessage = null): void
    {
        $this->logEmail($reservation, 'appel_paiement', $status, $errorMessage);
    }
}