<?php

namespace App\Service;

use MercurySeries\FlashyBundle\FlashyNotifier;

class EmailNotifierService
{
    private $flashy;

    public function __construct(FlashyNotifier $flashy)
    {
        $this->flashy = $flashy;
    }

    public function notifyDevisSent(string $reference): void
    {
        $this->flashy->success("L'url de téléchargement du devis N°$reference a été envoyé");
    }

    public function notifyDevisNotSent(string $reference): void
    {
        $this->flashy->error("L'url de téléchargement du devis N°$reference n'a pas été envoyé");
    }

    public function notifyContratSent(string $reference): void
    {
        $this->flashy->success("L'url de téléchargement du contrat N°$reference a été envoyé");
    }

    public function notifyContratNotSent(string $reference): void
    {
        $this->flashy->error("L'url de téléchargement du contrat N°$reference n'a pas été envoyé");
    }

    public function notifyFactureSent(string $reference): void
    {
        $this->flashy->success("L'url de téléchargement de la facture N°$reference a été envoyé");
    }

    public function notifyFactureNotSent(string $reference): void
    {
        $this->flashy->error("L'url de téléchargement de la facture N°$reference n'a pas été envoyé");
    }

    public function notifyValidationSent(): void
    {
        $this->flashy->success('Un email de confirmation vous a été envoyé. Veuillez vérifier votre boîte de réception.');
    }

    public function notifyValidationNotSent(): void
    {
        $this->flashy->error('L\'email de validation n\'a pas pu être envoyé. Veuillez réessayer.');
    }

    public function notifyContactSent(): void
    {
        $this->flashy->success("Votre message a été envoyé");
    }

    public function notifyContactNotSent(): void
    {
        $this->flashy->error("Votre message n'a pas été envoyé");
    }

    public function notifyPaiementConfirmationSent(): void
    {
        $this->flashy->success("La confirmation de paiement a été envoyée");
    }

    public function notifyPaiementConfirmationNotSent(): void
    {
        $this->flashy->error("La confirmation de paiement n'a pas pu être envoyée");
    }

    public function notifyAppelPaiementSent(): void
    {
        $this->flashy->success("L'appel à paiement a été envoyé");
    }

    public function notifyAppelPaiementNotSent(): void
    {
        $this->flashy->error("L'appel à paiement n'a pas pu être envoyé");
    }
}