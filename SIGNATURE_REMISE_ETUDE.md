# Étude : Extension du Système de Signature Électronique - Remise du Véhicule

## Contexte

Le système de signature électronique actuel permet de signer le **contrat de location** avant que le client ne reçoive le véhicule. Cependant, il est nécessaire d'ajouter une seconde phase de signature pour l'**état des lieux** lors de la remise physique du véhicule.

## Objectifs

1. **Signature du contrat** (existant) : Avant la location, signature du contrat par le client puis l'admin
2. **Signature de l'état des lieux** (nouveau) : À la remise du véhicule, signature conjointe client + admin pour valider l'état du véhicule

## Comparaison des deux types de signatures

| Aspect | Contrat de Location | État des Lieux (Remise) |
|--------|---------------------|------------------------|
| **Moment** | Avant la location | Au début de la location (remise véhicule) |
| **Contenu** | Conditions, tarifs, durée | État du véhicule, kilométrage, carburant, photos |
| **Signataires** | Client puis Admin | Client ET Admin ensemble (signature conjointe) |
| **Obligatoire** | Oui (pour valider la location) | Oui (pour prouver l'état initial) |
| **Localisation** | Espace client (à distance) | En agence (physiquement) |

---

## Architecture Proposée

### 1. Extension du modèle de données

#### Option A : Réutiliser ContractSignature avec type (Recommandée)

Ajouter un champ `document_type` à l'entité `ContractSignature` existante :

```php
// ContractSignature
- id: int (PK)
- contract: Contract (FK, nullable)
- vehicle_delivery: VehicleDelivery (FK, nullable)  // NOUVEAU
- document_type: string (enum: 'contract', 'delivery_checkin', 'delivery_checkout')
- signature_type: string (enum: 'client', 'admin')
- signature_data: text
- public_key_data: text
- signed_at: datetime
- ip_address: string
- user_agent: text
- signature_image: text (PNG base64)
- timestamp_token: text
```

#### Option B : Nouvelle entité VehicleDeliverySignature

Créer une entité séparée pour plus de clarté :

```php
// VehicleDeliverySignature
- id: int (PK)
- reservation: Reservation (FK)
- signature_type: string (enum: 'client', 'admin')
- signature_data: text
- public_key_data: text
- signed_at: datetime
- delivery_kilometrage: int
- delivery_fuel_level: int (pourcentage)
- damage_report: text (JSON des dommages constatés)
- photos: array (références aux photos)
- ip_address: string
- user_agent: text
- timestamp_token: text
```

### 2. Nouvelle entité : VehicleDelivery (État des lieux)

```php
// VehicleDelivery - Représente un état des lieux (départ ou retour)
- id: int (PK)
- reservation: Reservation (FK)
- delivery_type: string (enum: 'checkin', 'checkout')  // Départ ou Retour
- delivery_date: datetime
- kilometrage: int
- fuel_level: int (0-100)
- damage_report: text (JSON)
- general_condition: text
- signatures: OneToMany(VehicleDeliverySignature)
- status: string (enum: 'pending', 'client_signed', 'admin_signed', 'fully_signed')
- created_by: User (FK) - l'admin qui crée l'état des lieux
- created_at: datetime
- updated_at: datetime
```

### 3. Modification de Reservation

```php
// Reservation (ajouts)
- contracts: OneToMany(Contract)
- vehicleDeliveries: OneToMany(VehicleDelivery)

// Méthodes utiles
- getCheckinDelivery(): ?VehicleDelivery  // État des lieux départ
- getCheckoutDelivery(): ?VehicleDelivery // État des lieux retour
- hasSignedCheckin(): bool
- hasSignedCheckout(): bool
```

---

## Flux de travail

### Flux 1 : Signature du Contrat (existant)

```
Réservation créée
    ↓
Contrat généré (STATUS: unsigned)
    ↓
Client signe depuis son espace → STATUS: client_signed
    ↓
Admin signe depuis backoffice → STATUS: fully_signed
    ↓
Location peut commencer
```

### Flux 2 : État des Lieux Départ (nouveau)

```
Client arrive en agence pour récupérer le véhicule
    ↓
Admin crée l'état des lieux "checkin"
    ↓
    - Kilométrage initial
    - Niveau de carburant
    - Photos du véhicule
    - Inspection des dommages existants
    ↓
STATUS: pending
    ↓
Signature TABLETTE/ÉCRAN en agence :
    ├─ Client signe sur la tablette (signature visuelle + crypto)
    └─ Admin signe immédiatement après (signature visuelle + crypto)
    ↓
STATUS: fully_signed
    ↓
Clés remises au client
PDF de l'état des lieux généré et envoyé
```

### Flux 3 : État des Lieux Retour (futur - optionnel)

```
Client retourne le véhicule
    ↓
Admin crée l'état des lieux "checkout"
    ↓
    - Kilométrage final
    - Niveau de carburant
    - Nouveaux dommages éventuels
    ↓
Double signature client + admin
    ↓
Comparaison avec état initial
Facturation des éventuels dommages/carburant
```

---

## Interface Utilisateur

### 1. Interface Admin - Création État des Lieux

```
┌─────────────────────────────────────────────────────────┐
│  ÉTAT DES LIEUX - Départ Location #12345               │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Véhicule: Peugeot 208 - AB-123-CD                     │
│  Client: Jean Dupont                                    │
│  Date: 01/02/2026                                      │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  INSPECTION DU VÉHICULE                                │
│                                                         │
│  Kilométrage: [_________] km                           │
│                                                         │
│  Niveau carburant: [████░░░░░░] 40%                    │
│                                                         │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │
│  │   Photo 1   │ │   Photo 2   │ │   Photo 3   │       │
│  │  (avant)    │ │  (arrière)  │ │ (côté G)    │       │
│  └─────────────┘ └─────────────┘ └─────────────┘       │
│                                                         │
│  [+ Ajouter des photos]                                │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  DOMMAGES CONSTATÉS                                    │
│  [ ] Aucun dommage visible                             │
│  [X] Rayure pare-chocs avant (déjà présente)          │
│      [Voir photo]                                      │
│                                                         │
│  [+ Ajouter un dommage]                                │
│                                                         │
├─────────────────────────────────────────────────────────┤
│           [PASSER À LA SIGNATURE →]                    │
└─────────────────────────────────────────────────────────┘
```

### 2. Interface Tablette - Signature Conjointe

```
┌─────────────────────────────────────────────────────────┐
│  SIGNATURE ÉLECTRONIQUE - État des lieux               │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  📋 Récapitulatif:                                     │
│  • Kilométrage: 45 230 km                              │
│  • Carburant: 40%                                      │
│  • Dommages: 1 signalé (rayure avant)                  │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  1️⃣ SIGNATURE CLIENT                                   │
│  ┌─────────────────────────────────────────────────┐   │
│  │                                                 │   │
│  │                                                 │   │
│  │         [Zone de signature tactile]             │   │
│  │                                                 │   │
│  │                                                 │   │
│  └─────────────────────────────────────────────────┘   │
│            [Effacer]        [Valider]                  │
│                                                         │
│  ✓ Signé par Jean Dupont à 09:15                     │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  2️⃣ SIGNATURE ADMINISTRATEUR                           │
│  ┌─────────────────────────────────────────────────┐   │
│  │                                                 │   │
│  │                                                 │   │
│  │         [Zone de signature tactile]             │   │
│  │                                                 │   │
│  │                                                 │   │
│  └─────────────────────────────────────────────────┘   │
│            [Effacer]        [Valider]                  │
│                                                         │
│  ✓ Signé par Marie Martin (Admin) à 09:16            │
│                                                         │
├─────────────────────────────────────────────────────────┤
│     [✓ FINALISER L'ÉTAT DES LIEUX]                    │
└─────────────────────────────────────────────────────────┘
```

---

## Implémentation Technique

### 1. Nouvelles Entités

```php
// src/Entity/VehicleDelivery.php
namespace App\Entity;

/**
 * @ORM\Entity(repositoryClass=VehicleDeliveryRepository::class)
 */
class VehicleDelivery
{
    public const TYPE_CHECKIN = 'checkin';    // Départ
    public const TYPE_CHECKOUT = 'checkout';  // Retour
    
    public const STATUS_PENDING = 'pending';
    public const STATUS_CLIENT_SIGNED = 'client_signed';
    public const STATUS_ADMIN_SIGNED = 'admin_signed';
    public const STATUS_FULLY_SIGNED = 'fully_signed';
    
    // ... propriétés et méthodes
}

// src/Entity/VehicleDeliverySignature.php  
// OU extension de ContractSignature
```

### 2. Nouveaux Services

```php
// src/Service/VehicleDeliveryService.php
class VehicleDeliveryService
{
    /**
     * Crée un état des lieux
     */
    public function createDelivery(
        Reservation $reservation,
        string $type,
        array $vehicleData
    ): VehicleDelivery;
    
    /**
     * Ajoute une signature à l'état des lieux
     */
    public function addSignature(
        VehicleDelivery $delivery,
        User $user,
        string $signatureType,
        string $signatureImage,
        Request $request
    ): VehicleDeliverySignature;
    
    /**
     * Vérifie si les deux signatures sont présentes
     */
    public function isFullySigned(VehicleDelivery $delivery): bool;
    
    /**
     * Génère le PDF de l'état des lieux
     */
    public function generateDeliveryPDF(VehicleDelivery $delivery): string;
}
```

### 3. Nouveaux Contrôleurs

```php
// src/Controller/Admin/VehicleDeliveryController.php
/**
 * @Route("/admin/vehicle-delivery")
 */
class VehicleDeliveryController extends AbstractController
{
    /**
     * Créer un état des lieux
     * @Route("/new/{reservationId}", name="admin_delivery_new")
     */
    public function new(Request $request, int $reservationId);
    
    /**
     * Page de signature conjointe (tablette)
     * @Route("/sign/{id}", name="admin_delivery_sign")
     */
    public function sign(Request $request, VehicleDelivery $delivery);
    
    /**
     * API: Enregistrer une signature
     * @Route("/api/sign", name="admin_delivery_api_sign")
     */
    public function apiSign(Request $request);
    
    /**
     * Voir l'état des lieux
     * @Route("/view/{id}", name="admin_delivery_view")
     */
    public function view(VehicleDelivery $delivery);
}
```

### 4. Routes API

```php
// API pour signature en temps réel (mode tablette)
POST /api/delivery/{id}/sign
{
    "signature_type": "client|admin",
    "signature_image": "data:image/png;base64,iVBORw0...",
    "metadata": {
        "kilometrage": 45230,
        "fuel_level": 40,
        "damage_report": [...]
    }
}

Response:
{
    "success": true,
    "signature_id": 123,
    "status": "client_signed|fully_signed",
    "timestamp": "2026-02-01T09:15:30+01:00"
}
```

---

## Workflow Détaillé - Signature en Agence

### Scénario : Client arrive pour récupérer sa voiture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         EN AGENCE                                   │
└─────────────────────────────────────────────────────────────────────┘

1. ADMIN ouvre la réservation dans le backoffice
   
   ┌────────────────────────────────────────────────────────────────┐
   │ Réservation #12345 - Jean Dupont                               │
   │ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
   │ Statut contrat: ✓ Signé par client et admin                    │
   │                                                                  │
   │ ÉTAT DES LIEUX DÉPART:                                          │
   │    [Créer l'état des lieux]  ← ADMIN CLIQUE ICI                │
   │                                                                  │
   │ ÉTAT DES LIEUX RETOUR:                                          │
   │    (non disponible - location en cours)                         │
   └────────────────────────────────────────────────────────────────┘

2. ADMIN crée l'état des lieux
   - Saisit le kilométrage actuel
   - Prend des photos du véhicule
   - Note les dommages existants
   
3. ADMIN passe à la phase signature
   
   ┌────────────────────────────────────────────────────────────────┐
   │           TABLETTE / ORDINATEUR EN AGENCE                      │
   │ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
   │                                                                  │
   │  Présentez la tablette au client pour signature                │
   │                                                                  │
   │  ┌──────────────────────────────────────────────────────────┐   │
   │  │                                                          │   │
   │  │            [ZONE DE SIGNATURE CLIENT]                    │   │
   │  │                                                          │   │
   │  │                   (dessin)                               │   │
   │  │                                                          │   │
   │  └──────────────────────────────────────────────────────────┘   │
   │                                                                  │
   │  Je soussigné Jean Dupont, certifie avoir inspecté le véhicule │
   │  et être d'accord avec l'état des lieux décrit ci-dessus.      │
   │                                                                  │
   │              [EFFACER]          [VALIDER]                      │
   │                                                                  │
   └────────────────────────────────────────────────────────────────┘

4. CLIENT signe sur la tablette
   - Signature visuelle capturée (canvas)
   - Signature cryptographique générée (RSA)
   - Timestamp TSA demandé
   - Sauvegarde en base de données

5. ADMIN signe à son tour
   - Même processus
   - Sur le même écran ou écran séparé

6. VALIDATION FINALE
   - Statut passe à "fully_signed"
   - PDF généré avec les deux signatures
   - Email envoyé au client
   - Clés remises au client
```

---

## Points d'attention

### 1. Preuve d'identité en agence

| Risque | Mitigation |
|--------|------------|
| Client n'a pas son compte ouvert | Vérification pièce d'identité obligatoire |
| Usurpation d'identité | Photo du client avec le véhicule + CNI |
| Contestation "c'est pas ma signature" | Caméra de surveillance en agence (si possible) |

### 2. Intégrité des données

- Les photos doivent être immédiatement associées à l'état des lieux
- Hash des photos pour prouver qu'elles n'ont pas été modifiées
- Sauvegarde immédiate après chaque signature

### 3. Juridique

- Mentionner dans les CGV la possibilité de signature en agence
- Conserver les preuves d'identité (CNI) si possible
- Documenter le processus de signature en agence

---

## Recommandations

### Phase 1 : MVP (Minimum Viable Product)

1. Créer l'entité `VehicleDelivery` et `VehicleDeliverySignature`
2. Interface admin simple pour créer l'état des lieux
3. Page de signature conjointe (client + admin) sur la même interface
4. PDF de l'état des lieux signé

### Phase 2 : Améliorations

1. Application mobile/tablette dédiée pour l'agence
2. Upload de photos directement depuis la tablette
3. Reconnaissance automatique des dommages (IA)
4. Comparaison automatique checkin vs checkout

### Phase 3 : Intégrations

1. Signature à distance possible (QR code envoyé au client)
2. Notifications push
3. Intégration avec assurance

---

## Questions à clarifier

1. **Matériel en agence** : Tablette dédiée ou ordinateur existant ?
2. **Identité client** : Faut-il scanner la CNI ou vérification visuelle suffit ?
3. **Photos** : Appareil photo dédié ou tablette avec caméra ?
4. **Connexion internet** : Y a-t-il une connexion stable en agence ?
5. **Signature simultanée** : Les deux signatures doivent-elles être faites sur le même écran ou séparément ?
6. **Backup papier** : Faut-il conserver un système papier en parallèle ?

---

## Conclusion

L'extension du système de signature électronique à la remise du véhicule est techniquement faisable et juridiquement souhaitable. Elle renforce la preuve de l'état du véhicule au moment de la location.

**Points clés de réussite :**
- Interface simple et rapide (le client n'aime pas attendre)
- Double signature immédiate (client + admin)
- Photos timestampées et signées
- PDF envoyé automatiquement au client
- Preuve d'identité du client documentée
