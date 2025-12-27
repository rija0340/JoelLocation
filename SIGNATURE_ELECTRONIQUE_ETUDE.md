# Étude sur la Signature Électronique pour JoelLocation

## Objectif

Implémenter un système de signature électronique sécurisé et juridiquement valable pour les contrats de location sans dépendance sur des bibliothèques ou services payants externes.

## 1. Cadre Légal

### Réglementation applicable
- **Règlement eIDAS (UE) No 910/2014** : Établit le cadre juridique pour les signatures électroniques dans l'UE
- **Code civil français** : Article 1367 concernant la valeur probante des documents électroniques

### Niveaux de signature selon eIDAS
1. **Signature électronique simple** : Données électroniques attachées à d'autres données électroniques
2. **Signature électronique avancée (AdES)** : Liée à un signataire, capable d'identifier, créée à l'aide de moyens sous son contrôle exclusif, liée aux données signées de manière à détecter toute modification
3. **Signature électronique qualifiée (QES)** : Respecte les exigences AdES et créée avec un dispositif de création de signature qualifié

> Notre objectif est d'atteindre le niveau AdES pour garantir une valeur juridique solide.

## 2. Analyse du Système Actuel

### Architecture existante
- **Contrats** : Générés en PDF via KnpSnappyBundle à partir de templates Twig
- **Réservations** : Gérées via l'entité `Reservation` avec statuts (en cours, terminées)
- **Devis** : Gérés via l'entité `Devis` qui peut être transformé en réservation
- **Système d'authentification** : Basé sur Symfony Security

### Points clés
- Les contrats sont générés numériquement mais non signés
- L'envoi se fait par courriel avec lien de téléchargement
- Aucun mécanisme de signature électronique n'est actuellement implémenté

## 3. Approche Technique Proposée

### 3.1. Cryptographie à clé publique (RSA + SHA-256)

#### Procédure de signature
1. Génération d'un hash SHA-256 du contenu du contrat (contenu normalisé des données de la réservation)
2. Chiffrement de ce hash avec la clé privée du signataire (client ou admin)
3. Stockage de la signature résultante dans la base de données
4. Stockage de la clé publique pour vérification future

#### Procédure de vérification
1. Recalcul du hash du document à partir des données sauvegardées
2. Déchiffrement de la signature avec la clé publique
3. Comparaison avec le hash recalculé
4. Si correspondance → signature valide

### 3.2. Modèles de données

#### Nouvelles entités
```php
// ContractSignature
- id: int (PK)
- reservation: Reservation (FK)
- signature_type: string (enum: 'client', 'admin')
- signature_data: text (signature encodée en base64)
- public_key_data: text (clé publique)
- signed_at: datetime
- ip_address: string
- user_agent: text
```

#### Modifications des entités existantes
```php
// Reservation
- contract_hash: string (hash SHA-256 du contrat)
- client_signed_at: datetime (nullable)
- admin_signed_at: datetime (nullable)
- contract_status: string (enum: 'unsigned', 'client_signed', 'fully_signed')
```

### 3.3. Horodatage (TSA - Time Stamp Authority)

#### Principe
- Ajouter un mécanisme d'horodatage cryptographique pour prouver la date exacte de signature
- Utilisation d'un client TSA (Time Stamp Authority) pour obtenir des preuves tempororelles certifiées
- Actuellement implémenté en mode simulé dans `tsa_client.php`

#### Avantages
- Preuve irréfutable de la date de signature
- Protection contre les modifications futures même si les clés sont compromises
- Renforcement de la valeur juridique de la signature

#### Approche mise en œuvre
1. Génération d'un hash du document à signer
2. Envoi de ce hash à la TSA pour horodatage
3. Réception d'un ticket contenant le hash, l'horodatage et une signature de la TSA
4. Stockage du ticket avec la signature principale

> Note : L'implémentation actuelle est simulée. Pour une vraie TSA, il faudrait soit en créer une interne soit utiliser un service externe (gratuit ou payant).

## 4. Flux de travail

### 4.1. Processus de signature

1. **Création du contrat**
   - Le système génère le PDF du contrat à partir des données de la réservation
   - Calcul du hash du contenu du contrat
   - Stockage initial avec statut "unsigned"

2. **Signature par le client**
   - Le client accède au contrat via une interface sécurisée
   - Vérification que l'utilisateur est bien le client de la réservation
   - Génération d'une paire de clés RSA (temporaire)
   - Signature du hash avec la clé privée
   - Enregistrement de la signature et de la clé publique
   - Mise à jour du statut à "client_signed"

3. **Signature par l'admin**
   - L'administrateur accède via l'interface backoffice
   - Vérification que le client a déjà signé
   - Signature du hash avec une clé privée administrative
   - Enregistrement de la signature admin
   - Mise à jour du statut à "fully_signed"

4. **Vérification**
   - Interface pour vérifier l'authenticité des signatures
   - Affichage de l'état de chaque signature
   - Affichage de l'horodatage (si implémenté)

## 5. Sécurité et conformité

### 5.1. Contrôles d'accès
- Authentification requise pour signer
- Vérification des rôles (client vs admin)
- Restriction basée sur les données de la réservation

### 5.2. Journalisation
- Trace complète des opérations de signature
- IP et User-Agent enregistrés
- Horodatage des actions

### 5.3. Intégrité des données
- Hash SHA-256 pour vérification de non-modification
- Clés RSA 2048 bits pour la signature
- Encodage base64 pour le stockage des signatures

## 6. Intégration technique

### 6.1. Services nécessaires
- `SignatureService` : Gestion des opérations de signature
- `TsaClient` : Client pour l'horodatage (actuellement simulé)
- `ContractService` : Gestion du cycle de vie des contrats

### 6.2. Interfaces utilisateur
- Boutons de signature dans les écrans client et admin
- Indicateurs de statut de signature
- Page de vérification des signatures

### 6.3. Modèles Twig
- Mise à jour de `contrat.html.twig` pour inclure les informations de signature
- Interfaces pour la signature et la vérification

## 7. Avantages de l'approche

1. **Autonomie** : Aucune dépendance externe payante
2. **Sécurité** : Cryptographie à clé publique robuste
3. **Conformité** : Respect des standards eIDAS pour AdES
4. **Extensibilité** : Possibilité d'intégrer une vraie TSA externe ou interne
5. **Auditabilité** : Journalisation complète des opérations

## 8. Recommandations

1. **Déploiement progressif** : Commencer avec la signature AdES, puis évaluer l'ajout d'une vraie TSA
2. **Tests de sécurité** : Validation du système par un expert en sécurité
3. **Documentation juridique** : Faire certifier la conformité par un juriste spécialisé
4. **Sauvegarde des clés** : Mécanisme de sauvegarde et de récupération des clés de signature
5. **Mise à jour des CGV** : Inclure les modalités de signature électronique dans les conditions générales

## Conclusion

Cette solution permet d'implémenter un système de signature électronique conforme aux standards européens sans recourir à des services extérieurs payants. L'ajout d'une TSA renforcerait la légalité en fournissant des preuves de temporalité cryptographiquement solides. L'approche hybride (AdES + horodatage) offre un bon équilibre entre sécurité, conformité juridique et autonomie technique.