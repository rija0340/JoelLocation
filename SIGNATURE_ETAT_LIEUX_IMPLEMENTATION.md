# Implémentation des Signatures des États des Lieux

## Résumé

Extension du système de signature électronique pour gérer les signatures des états des lieux (départ et retour) en plus du contrat de location.

## Architecture

### Types de documents signables

```php
ContractSignature::DOC_CONTRACT  // Contrat de location (existant)
ContractSignature::DOC_CHECKIN   // État des lieux départ
ContractSignature::DOC_CHECKOUT  // État des lieux retour
```

### Flux de signature

```
Contrat (contract)
   ↓ (doit être signé avant la location)
État des lieux départ (checkin)
   ↓ (doit être signé avant le retour)
État des lieux retour (checkout)
```

## Modifications apportées

### 1. Entités

#### `ContractSignature`
- Ajout du champ `documentType` (contract/checkin/checkout)
- Valeur par défaut : `'contract'` (compatibilité avec les signatures existantes)

#### `Contract`
- Ajout des méthodes :
  - `isSignedByClient(string $documentType)` - Vérifie si le client a signé un type de document
  - `isSignedByAdmin(string $documentType)` - Vérifie si l'admin a signé un type de document
  - `isFullySigned(string $documentType)` - Vérifie si les deux parties ont signé
  - `getDocumentStatus(string $documentType)` - Récupère le statut d'un document
  - `canSignCheckout()` - Vérifie si le checkout peut être signé (checkin requis)

### 2. Services

#### `ContractService`
- Extension de `processClientSignature()` et `processAdminSignature()` pour accepter un `$documentType`
- Validation : le checkout ne peut être signé que si le checkin est complètement signé

#### `SignatureService`
- Extension de `createContractSignature()` pour accepter un `$documentType`

### 3. Contrôleur

#### `CheckinCheckoutController`
Routes disponibles :
- `GET /vehicle-check/checkin/sign/{id}` - Page de signature départ
- `POST /vehicle-check/checkin/sign/{id}/process` - Traitement signature départ
- `GET /vehicle-check/checkout/sign/{id}` - Page de signature retour
- `POST /vehicle-check/checkout/sign/{id}/process` - Traitement signature retour
- `GET /vehicle-check/summary/{id}` - Récapitulatif des signatures

### 4. Templates

- `vehicle_check/checkin_sign.html.twig` - Interface signature départ
- `vehicle_check/checkout_sign.html.twig` - Interface signature retour
- `vehicle_check/summary.html.twig` - Récapitulatif des signatures

### 5. Interface admin

Ajout d'une section "Signatures des états des lieux" dans la page de détail d'une réservation avec :
- Visualisation du statut des signatures départ et retour
- Boutons d'accès rapide aux pages de signature
- Blocage du retour si le départ n'est pas signé

## Règles métier

1. **Signature départ (checkin)** :
   - Peut être signé par le client ou l'admin dans n'importe quel ordre
   - Doit être complètement signé (client + admin) pour débloquer le retour

2. **Signature retour (checkout)** :
   - Ne peut être signé que si le départ est complètement signé
   - Sinon, un message d'erreur s'affiche

3. **Compatibilité** :
   - Les signatures existantes (contrat) continuent de fonctionner
   - Le champ `documentType` a une valeur par défaut `'contract'`

## Migration base de données

Fichier : `migrations/Version20260201120000.php`

```sql
ALTER TABLE contract_signature ADD document_type VARCHAR(20) NOT NULL DEFAULT 'contract';
CREATE INDEX idx_document_type ON contract_signature (document_type);
```

## Utilisation

### Depuis le backoffice (admin)

1. Aller sur la page d'une réservation
2. Section "Signatures des états des lieux"
3. Cliquer sur "Signer l'état des lieux départ"
4. Le client et/ou l'admin signent sur la tablette/écran
5. Une fois le départ signé, le retour devient disponible
6. Au retour du véhicule, signer l'état des lieux retour

### URLs directes

```
/vehicle-check/checkin/sign/{contract_id}   # Signature départ
/vehicle-check/checkout/sign/{contract_id}  # Signature retour
/vehicle-check/summary/{contract_id}        # Récapitulatif
```

## Sécurité

- Vérification des rôles (ROLE_ADMIN pour l'admin, ROLE_CLIENT pour le client)
- Validation que le checkout ne peut pas être signé avant le checkin
- Traçabilité (IP, User-Agent, timestamp) pour chaque signature
- Signatures cryptographiques (RSA + SHA-256) pour chaque document

## UX/UI

- Interface moderne avec des couleurs distinctes :
  - Départ : vert (dégradé #11998e → #38ef7d)
  - Retour : rose/rouge (dégradé #f093fb → #f5576c)
- Timeline visuelle montrant la progression
- Indicateurs clairs du statut (signé/en attente/bloqué)
- Signature tactile (souris ou doigt sur mobile/tablette)
- Prévisualisation des signatures existantes
