<?php

namespace App\EventSubscriber;

use App\Entity\Paiement;
use App\Classe\ReserverDevis;
use App\Classe\Payment\Event\PaymentSuccessEvent;
use App\Repository\ModePaiementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentSubscriber implements EventSubscriberInterface
{
    private $em;
    private $modePaiementRepo;
    private $reserverDevis;

    public function __construct(
        EntityManagerInterface $em,
        ModePaiementRepository $modePaiementRepo,
        ReserverDevis $reserverDevis
    ) {
        $this->em = $em;
        $this->modePaiementRepo = $modePaiementRepo;
        $this->reserverDevis = $reserverDevis;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentSuccessEvent::NAME => 'onPaymentSuccess',
        ];
    }

    public function onPaymentSuccess(PaymentSuccessEvent $event)
    {
        try {
            $devis = $event->getDevis();
            $data = $event->getData();

            // 1. Réserver le devis (le transformer en réservation)
            $orderId = $data["paymentData"]['id'] ?? null; // ID de la transaction PayPal
            $reservation = $this->reserverDevis->reserver($devis, $orderId, true);

            // Stocker la réservation dans l'événement
            $event->setReservation($reservation);

            // 2. Créer et enregistrer le paiement
            $modePaiement = $this->modePaiementRepo->findOneBy(['libelle' => 'Virement']);
            if (!$modePaiement) {
                throw new \Exception("Mode de paiement Virement non trouvé");
            }

            $paiement = new Paiement();

            // Définir le montant à partir des données PayPal
            $montant = $data['paymentData']['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
            $paiement->setMontant(floatval($montant));

            // Lier à la réservation créée
            $paiement->setReservation($reservation);

            // Définir la date de paiement
            $paiement->setDatePaiement(new \DateTime());

            // Définir le client
            $paiement->setClient($reservation->getClient());

            // Définir le motif
            $paiement->setMotif("Réservation");

            // Définir le mode de paiement (PayPal)
            $paiement->setModePaiement($modePaiement);

            // Définir la date de création
            $paiement->setCreatedAt(new \DateTime());

            $paiement->setStripeSessionId($orderId);
            //indiquer que le devis est rerservé
            $devis->setTransformed(true);

            // Sauvegarder le paiement
            $this->em->persist($paiement);
            $this->em->flush();
        } catch (\Exception $e) {
            // Log l'erreur ou la gérer d'une autre manière
            throw new \Exception('Erreur lors du traitement du paiement: ' . $e->getMessage());
        }
    }
}
