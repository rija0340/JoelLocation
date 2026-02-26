<?php
/**
 * Test de calcul de tarif multi-mois
 * Simulation de la logique sans dépendances Symfony
 */

echo "=== TEST CALCUL TARIF MULTI-MOIS ===\n\n";

// Tarifs pour marque_id=1, modele_id=1
$tarifs = [
    'Janvier' => ['3j' => 97, '7j' => 220, '15j' => 469, '30j' => 930],
    'Février' => ['3j' => 85, '7j' => 196, '15j' => 420, '30j' => 837],
    'Mars'    => ['3j' => 75, '7j' => 188, '15j' => 375, '30j' => 750],
    'Avril'   => ['3j' => 60, '7j' => 137, '15j' => 275, '30j' => 551],
];

function getMonthName($date) {
    $months = [
        '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
        '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
        '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
    ];
    return $months[$date->format('m')];
}

function calculDuree($dateDepart, $dateRetour) {
    $diff = $dateDepart->diff($dateRetour);
    return $diff->days;
}

function getBracketPrice($jours, $tarif) {
    if ($jours <= 3) return $tarif['3j'];
    if ($jours <= 7) return $tarif['7j'];
    if ($jours <= 15) return $tarif['15j'];
    return $tarif['30j'];
}

function calculTarifMultiMois($dateDepart, $dateRetour, $tarifs) {
    $tarifTotal = 0;
    $dateCourante = clone $dateDepart;
    $details = [];
    
    while ($dateCourante < $dateRetour) {
        $mois = getMonthName($dateCourante);
        $tarif = $tarifs[$mois] ?? null;
        
        if ($tarif) {
            // Fin du mois courant
            $finDuMois = new DateTime($dateCourante->format('Y-m-t'));
            
            // Date de fin pour cette période
            $dateFinPeriode = ($finDuMois < $dateRetour) ? $finDuMois : $dateRetour;
            
            // Nombre de jours dans cette période
            $joursDansPeriode = calculDuree($dateCourante, $dateFinPeriode);
            
            // Prix pour cette période
            $prix = getBracketPrice($joursDansPeriode, $tarif);
            $tarifTotal += $prix;
            
            $details[] = sprintf(
                "  %s (%s au %s) : %d jours → bracket = %.2f€",
                $mois,
                $dateCourante->format('d/m'),
                $dateFinPeriode->format('d/m'),
                $joursDansPeriode,
                $prix
            );
            
            // Passer au mois suivant
            $dateCourante = new DateTime($finDuMois->format('Y-m-d') . ' +1 day');
        } else {
            break;
        }
    }
    
    return ['total' => $tarifTotal, 'details' => $details];
}

// ============================================================================
// TEST 1 : 7 Janvier au 25 Mars (77 jours) - LE CAS PROBLÈME
// ============================================================================
echo "TEST 1 : Réservation du 7 Janvier au 25 Mars (77 jours)\n";
echo str_repeat("=", 60) . "\n";

$dateDepart = new DateTime('2025-01-07');
$dateRetour = new DateTime('2025-03-25');

echo "Date départ : " . $dateDepart->format('d/m/Y') . "\n";
echo "Date retour : " . $dateRetour->format('d/m/Y') . "\n";
echo "Durée : " . calculDuree($dateDepart, $dateRetour) . " jours\n\n";

$resultat = calculTarifMultiMois($dateDepart, $dateRetour, $tarifs);

echo "Détail du calcul :\n";
foreach ($resultat['details'] as $detail) {
    echo $detail . "\n";
}
echo "\n";
echo "TOTAL CALCULÉ : " . number_format($resultat['total'], 2) . "€\n";
echo "TOTAL ANCIEN (BUG) : 930€ (tarif 30 jours Janvier appliqué une fois)\n";
echo "ÉCONOMIE INJUSTIFIÉE : " . number_format($resultat['total'] - 930, 2) . "€\n\n\n";

// ============================================================================
// TEST 2 : Réservation courte (5 jours) - Doit utiliser l'ancienne logique
// ============================================================================
echo "TEST 2 : Réservation du 10 Janvier au 15 Janvier (5 jours)\n";
echo str_repeat("=", 60) . "\n";

$dateDepart2 = new DateTime('2025-01-10');
$dateRetour2 = new DateTime('2025-01-15');
$duree2 = calculDuree($dateDepart2, $dateRetour2);

echo "Date départ : " . $dateDepart2->format('d/m/Y') . "\n";
echo "Date retour : " . $dateRetour2->format('d/m/Y') . "\n";
echo "Durée : $duree2 jours\n";

if ($duree2 <= 30) {
    $tarif = $tarifs[getMonthName($dateDepart2)];
    $prix = getBracketPrice($duree2, $tarif);
    echo "TOTAL (logique simple) : " . number_format($prix, 2) . "€ (bracket 7 jours)\n";
}
echo "\n\n";

// ============================================================================
// TEST 3 : Réservation sur 2 mois (45 jours)
// ============================================================================
echo "TEST 3 : Réservation du 15 Janvier au 1er Mars (45 jours)\n";
echo str_repeat("=", 60) . "\n";

$dateDepart3 = new DateTime('2025-01-15');
$dateRetour3 = new DateTime('2025-03-01');

echo "Date départ : " . $dateDepart3->format('d/m/Y') . "\n";
echo "Date retour : " . $dateRetour3->format('d/m/Y') . "\n";
echo "Durée : " . calculDuree($dateDepart3, $dateRetour3) . " jours\n\n";

$resultat3 = calculTarifMultiMois($dateDepart3, $dateRetour3, $tarifs);

echo "Détail du calcul :\n";
foreach ($resultat3['details'] as $detail) {
    echo $detail . "\n";
}
echo "\n";
echo "TOTAL CALCULÉ : " . number_format($resultat3['total'], 2) . "€\n\n\n";

// ============================================================================
// TEST 4 : Réservation sur 1 mois exactement (30 jours)
// ============================================================================
echo "TEST 4 : Réservation du 1 Janvier au 31 Janvier (30 jours)\n";
echo str_repeat("=", 60) . "\n";

$dateDepart4 = new DateTime('2025-01-01');
$dateRetour4 = new DateTime('2025-01-31');

echo "Date départ : " . $dateDepart4->format('d/m/Y') . "\n";
echo "Date retour : " . $dateRetour4->format('d/m/Y') . "\n";
echo "Durée : " . calculDuree($dateDepart4, $dateRetour4) . " jours\n\n";

$resultat4 = calculTarifMultiMois($dateDepart4, $dateRetour4, $tarifs);

echo "Détail du calcul :\n";
foreach ($resultat4['details'] as $detail) {
    echo $detail . "\n";
}
echo "\n";
echo "TOTAL CALCULÉ : " . number_format($resultat4['total'], 2) . "€\n";
echo "(Doit être égal au tarif 30 jours de Janvier = 930€)\n\n";

echo "=== FIN DES TESTS ===\n";
