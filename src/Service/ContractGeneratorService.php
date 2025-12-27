<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\Reservation;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;

class ContractGeneratorService
{
    private ContractService $contractService;
    private ContractRepository $contractRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ContractService $contractService,
        ContractRepository $contractRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->contractService = $contractService;
        $this->contractRepository = $contractRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Generate a contract for a reservation
     */
    public function generateContractForReservation(Reservation $reservation): Contract
    {
        // Generate contract content based on reservation details
        $contractContent = $this->generateContractContent($reservation);
        
        // Create the contract
        $contract = $this->contractService->createContract($reservation, $contractContent);
        
        return $contract;
    }

    /**
     * Generate contract content from reservation data
     */
    private function generateContractContent(Reservation $reservation): string
    {
        $content = "CONTRAT DE LOCATION DE VÉHICULE\n\n";
        $content .= "Numéro de contrat : CT-" . str_pad($reservation->getId(), 6, "0", STR_PAD_LEFT) . "\n";
        $content .= "Date de création : " . (new \DateTime())->format('d/m/Y à H:i') . "\n\n";
        
        $content .= "DONNÉES CLIENT :\n";
        $content .= "- Nom : " . $reservation->getClient()->getNom() . "\n";
        $content .= "- Prénom : " . $reservation->getClient()->getPrenom() . "\n";
        $content .= "- Email : " . $reservation->getClient()->getEmail() . "\n";
        $content .= "- Téléphone : " . $reservation->getClient()->getTelephone() . "\n\n";
        
        $content .= "DONNÉES VÉHICULE :\n";
        $content .= "- Marque : " . $reservation->getVehicule()->getMarque()->getLibelle() . "\n";
        $content .= "- Modèle : " . $reservation->getVehicule()->getModele()->getLibelle() . "\n";
        $content .= "- Immatriculation : " . $reservation->getVehicule()->getImmatriculation() . "\n";
        $content .= "- Type : " . $reservation->getVehicule()->getType()->getLibelle() . "\n\n";
        
        $content .= "CONDITIONS DE LOCATION :\n";
        $content .= "- Date de début : " . $reservation->getDateDebut()->format('d/m/Y à H:i') . "\n";
        $content .= "- Date de fin : " . $reservation->getDateFin()->format('d/m/Y à H:i') . "\n";
        $content .= "- Lieu de départ : " . $reservation->getLieu() . "\n";
        $content .= "- Agence de départ : " . $reservation->getAgenceDepart() . "\n";
        $content .= "- Agence de retour : " . $reservation->getAgenceRetour() . "\n\n";
        
        $content .= "TARIFS ET CONDITIONS :\n";
        $content .= "- Prix du véhicule : " . number_format($reservation->getTarifVehicule(), 2, ',', ' ') . " €\n";
        $content .= "- Prix des options : " . number_format($reservation->getPrixOptions(), 2, ',', ' ') . " €\n";
        $content .= "- Prix des garanties : " . number_format($reservation->getPrixGaranties(), 2, ',', ' ') . " €\n";
        $content .= "- Prix total : " . number_format($reservation->getPrix(), 2, ',', ' ') . " €\n\n";
        
        $content .= "CONDITIONS GÉNÉRALES :\n";
        $content .= "1. Le locataire déclare être en possession d'un permis de conduire valide.\n";
        $content .= "2. Le véhicule devra être restitué dans le même état qu'au départ.\n";
        $content .= "3. En cas d'accident, le locataire devra immédiatement prévenir l'agence.\n\n";
        
        $content .= "Fait à ______________________, le " . (new \DateTime())->format('d/m/Y') . "\n\n";
        $content .= "Signatures :\n";
        $content .= "Client : ______________________\n";
        $content .= "Agence : ______________________\n";
        
        return $content;
    }
}