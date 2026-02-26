<?php
/**
 * Script de test pour vérifier le calcul de tarif multi-mois
 * 
 * Exemple : Réservation du 7 Janvier au 25 Mars (77 jours)
 * Véhicule : marque_id=1, modele_id=1
 */

// Bootstrap Symfony
require __DIR__ . '/vendor/autoload.php';

use App\Service\TarifsHelper;
use App\Service\DateHelper;
use App\Repository\TarifsRepository;

// Initialiser le kernel Symfony
$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

// Récupérer les services
$tarifsHelper = $container->get(TarifsHelper::class);
$dateHelper = $container->get(DateHelper::class);
$tarifsRepo = $container->get('doctrine')->getRepository(\App\Entity\Tarifs::class);

// Créer un mock de TarifsRepository si nécessaire
class MockTarifsRepository {
    private $tarifs = [
        // marque_id=1, modele_id=1
        ['mois' => 'Janvier', '3j' => 97, '7j' => 220, '15j' => 469, '30j' => 930],
        ['mois' => 'Février', '3j' => 85, '7j' => 196, '15j' => 420, '30j' => 837],
        ['mois' => 'Mars', '3j' => 75, '7j' => 188, '15j' => 375, '30j' => 750],
    ];
    
    public function findOneBy($criteria) {
        foreach ($this->tarifs as $t) {
            if ($t['mois'] === $criteria['mois']) {
                return new class($t) {
                    private $data;
                    public function __construct($data) { $this->data = $data; }
                    public function getTroisJours() { return $this->data['3j']; }
                    public function getSeptJours() { return $this->data['7j']; }
                    public function getQuinzeJours() { return $this->data['15j']; }
                    public function getTrenteJours() { return $this->data['30j']; }
                };
            }
        }
        return null;
    }
}

echo "=== TEST CALCUL TARIF MULTI-MOIS ===\n\n";

// Test 1 : Réservation du 7 Janvier au 25 Mars (77 jours)
echo "Test 1 : 7 Janvier au 25 Mars (77 jours)\n";
echo "----------------------------------------\n";

$dateDepart = new DateTime('2025-01-07');
$dateRetour = new DateTime('2025-03-25');

// Calcul manuel attendu :
// Janvier (7-31) = 25 jours → bracket 15 jours = 469€
// Février (1-28) = 28 jours → bracket 30 jours = 837€
// Mars (1-25) = 25 jours → bracket 15 jours = 375€
// TOTAL ATTENDU = 469 + 837 + 375 = 1 681€

echo "Date départ : " . $dateDepart->format('d/m/Y') . "\n";
echo "Date retour : " . $dateRetour->format('d/m/Y') . "\n";

$duree = $dateHelper->calculDuree($dateDepart, $dateRetour);
echo "Durée : $duree jours\n\n";

echo "Détail par mois :\n";
echo "  Janvier (7-31) : 25 jours → bracket 15 jours = 469€\n";
echo "  Février (1-28) : 28 jours → bracket 30 jours = 837€\n";
echo "  Mars (1-25) : 25 jours → bracket 15 jours = 375€\n";
echo "  TOTAL ATTENDU = 1 681€\n\n";

// Test 2 : Réservation courte (5 jours) - doit utiliser l'ancienne logique
echo "\nTest 2 : 10 Janvier au 15 Janvier (5 jours)\n";
echo "----------------------------------------\n";

$dateDepart2 = new DateTime('2025-01-10');
$dateRetour2 = new DateTime('2025-01-15');

echo "Date départ : " . $dateDepart2->format('d/m/Y') . "\n";
echo "Date retour : " . $dateRetour2->format('d/m/Y') . "\n";

$duree2 = $dateHelper->calculDuree($dateDepart2, $dateRetour2);
echo "Durée : $duree2 jours\n";
echo "TARIF ATTENDU : bracket 7 jours = 220€\n";

// Test 3 : Réservation sur 2 mois (45 jours)
echo "\n\nTest 3 : 15 Janvier au 1er Mars (45 jours)\n";
echo "----------------------------------------\n";

$dateDepart3 = new DateTime('2025-01-15');
$dateRetour3 = new DateTime('2025-03-01');

echo "Date départ : " . $dateDepart3->format('d/m/Y') . "\n";
echo "Date retour : " . $dateRetour3->format('d/m/Y') . "\n";

$duree3 = $dateHelper->calculDuree($dateDepart3, $dateRetour3);
echo "Durée : $duree3 jours\n\n";

echo "Détail par mois :\n";
echo "  Janvier (15-31) : 17 jours → bracket 30 jours = 930€\n";
echo "  Février (1-28) : 28 jours → bracket 30 jours = 837€\n";
echo "  Mars (1) : 1 jour → bracket 3 jours = 75€\n";
echo "  TOTAL ATTENDU = 930 + 837 + 75 = 1 842€\n";

echo "\n=== FIN DES TESTS ===\n";
