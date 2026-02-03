# AGENTS.md - JoelLocation Vehicle Rental System

Ce fichier est destiné aux agents de codage AI travaillant sur ce projet. Il contient les informations essentielles pour comprendre l'architecture, les conventions et les processus de développement.

---

## Vue d'ensemble du projet

**JoelLocation** est un système de gestion de location de véhicules développé avec Symfony 5.3 (PHP 7.2.5+). L'application comprend :

- Un site vitrine public pour la présentation des véhicules
- Un espace client pour les réservations en ligne
- Un backoffice administratif pour la gestion des opérations
- Un système de planification pour le suivi de disponibilité des véhicules
- Un système de signature électronique pour les contrats

---

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Framework Backend | Symfony 5.3 |
| Langage | PHP 7.2.5+ |
| Base de données | MySQL/MariaDB |
| ORM | Doctrine 2.8 |
| Frontend Build | Webpack Encore |
| CSS Frameworks | Tailwind CSS v4, Bootstrap 4, DaisyUI |
| JavaScript | jQuery, Moment.js, DateJS |
| Génération PDF | DomPDF, wkhtmltopdf (KnpSnappyBundle) |
| Email | Symfony Mailer, Mailjet API |
| Paiement | PayPal (Stripe configuré mais non utilisé) |
| Upload de fichiers | VichUploaderBundle |

---

## Structure des répertoires

```
symfony-app/
├── assets/                    # Assets frontend (Webpack Encore)
│   ├── backoffice/           # Assets admin (Bootstrap, SCSS, JS)
│   │   ├── base/             # Styles de base, layouts
│   │   ├── dashboard/        # Tableau de bord
│   │   ├── planGen/          # Planning général
│   │   ├── planJour/         # Planning journalier
│   │   └── planning_modern/  # Planning moderne
│   └── vitrine/              # Assets site public (Tailwind, images)
├── config/                   # Configuration Symfony
│   ├── packages/             # Config bundles
│   └── routes/               # Définition des routes
├── migrations/               # Migrations Doctrine
├── public/                   # Document root
│   ├── build/               # Assets compilés (Webpack)
│   ├── uploads/             # Fichiers uploadés
│   └── index.php            # Front controller
├── src/                      # Code source PHP
│   ├── Classe/              # Classes utilitaires
│   │   └── Payment/         # Système de paiement
│   ├── Controller/          # Contrôleurs
│   │   ├── Client/          # Contrôleurs espace client
│   │   └── Testing/         # Contrôleurs de test
│   ├── Entity/              # Entités Doctrine (35+ entités)
│   ├── Event/               # Événements personnalisés
│   ├── EventListener/       # Écouteurs d'événements
│   ├── EventSubscriber/     # Souscripteurs d'événements
│   ├── Form/                # Types de formulaires Symfony
│   ├── Repository/          # Repositories Doctrine
│   ├── Security/            # Authenticator personnalisé
│   ├── Service/             # Services métier (20+ services)
│   └── Twig/                # Extensions Twig
├── templates/               # Templates Twig
│   ├── admin/               # Templates backoffice (Bootstrap)
│   ├── client/              # Ancien espace client
│   ├── client2/             # Nouvel espace client (Tailwind)
│   ├── contract/            # Signature de contrats
│   ├── pdf_generation/      # Templates PDF
│   └── vitrine/             # Site public (Tailwind)
├── tests/                   # Tests PHPUnit
└── translations/            # Fichiers de traduction
```

---

## Commandes de build et de test

### Installation des dépendances

```bash
# PHP dependencies
composer install

# Node.js dependencies
npm install
```

### Commandes Symfony

```bash
# Vider le cache
php bin/console cache:clear

# Créer la base de données
php bin/console doctrine:database:create

# Générer une migration
php bin/console doctrine:migrations:diff

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Lancer le serveur de développement
php bin/console server:run
```

### Build Frontend (Webpack Encore)

```bash
# Build de développement
npm run dev

# Watch mode (rebuild automatique)
npm run watch

# Build de production
npm run build
```

### Tests

```bash
# Exécuter tous les tests
php bin/phpunit

# Exécuter un fichier de test spécifique
./vendor/bin/phpunit tests/Service/ReservationServiceTest.php

# Exécuter une méthode de test spécifique
./vendor/bin/phpunit --filter testMethodName
```

---

## Architecture et conventions de code

### Organisation des contrôleurs

- Les annotations de routes sont utilisées (`@Route`)
- Injection de dépendances par constructeur (autowiring activé)
- Les contrôleurs client sont dans `src/Controller/Client/`
- Les contrôleurs de test sont dans `src/Controller/Testing/`

### Entités principales

| Entité | Description |
|--------|-------------|
| `User` | Comptes clients et personnel |
| `Vehicule` | Véhicules de location |
| `Reservation` | Réservations centrales |
| `Devis` | Devis/estimations |
| `Paiement` | Suivi des paiements |
| `Contract` | Contrats de location |
| `ContractSignature` | Signatures électroniques |
| `Agence` | Agences de location |
| `Options` | Options de location |
| `Garantie` | Assurances/garanties |

### Hiérarchie des rôles utilisateur

```
ROLE_USER < ROLE_CLIENT < ROLE_PERSONNEL < ROLE_SUPERVISEUR < ROLE_ADMIN < ROLE_SUPER_ADMIN
```

### Services métier principaux (src/Service/)

- `PdfGenerationService` : Génération des PDF (contrats, factures)
- `SignatureService` : Gestion des signatures électroniques
- `ContractService` : Workflow des contrats
- `EmailService` : Envoi d'emails
- `PaymentProcessingService` : Traitement des paiements PayPal
- `VehicleAvailabilityService` : Logique de disponibilité des véhicules
- `ReservationStateService` : Gestion des états des réservations

---

## Configuration Webpack Encore

Le fichier `webpack.config.js` définit plusieurs points d'entrée :

```javascript
// Site vitrine
.addEntry('vitrine', './assets/vitrine/app.js')
.addEntry('vitrine_styles', './assets/vitrine/vitrine-tailwind.js')

// Backoffice
.addEntry('admin_base', './assets/backoffice/base/app.js')
.addEntry('admin_dashboard', './assets/backoffice/dashboard/dashboard.js')
.addEntry('admin_plangen', './assets/backoffice/plangen/plangen.js')
.addEntry('admin_planjour', './assets/backoffice/planjour/planjour.js')
.addEntry('admin_planning_modern', './assets/backoffice/planning_modern/planning_modern.js')
```

### Tailwind CSS v4

- Configuration dans `postcss.config.js`
- Configuration personnalisée des couleurs dans `tailwind.config.js`
- DaisyUI activé comme plugin

---

## Instructions de test

### Configuration PHPUnit

Le fichier `phpunit.xml.dist` configure :
- Bootstrap : `tests/bootstrap.php`
- Répertoire des tests : `tests/`
- Couverture de code : répertoire `src/`

### Types de tests existants

- `tests/Service/ReservationServiceTest.php` : Tests unitaires des services
- `tests/Controller/BackofficeAuthTest.php` : Tests d'authentification
- `tests/Controller/ReservationControllerTest.php` : Tests des contrôleurs

### Bonnes pratiques de test

- Utiliser des mocks pour les dépendances externes
- Nommer les méthodes de test de façon descriptive : `test<Action><Resultat>()`
- Tester les cas nominaux et les cas d'erreur

---

## Considérations de sécurité

### Authentification

- Formulaire de login personnalisé (`LoginFormAuthenticator`)
- Hashage automatique des mots de passe (algorithmes modernes)
- Protection CSRF activée

### Autorisations

Les règles d'accès sont définies dans `config/packages/security.yaml` :

```yaml
access_control:
    - { path: ^/admin, roles: [ROLE_ADMIN, ROLE_SUPER_ADMIN, ROLE_PERSONNEL] }
    - { path: ^/espaceclient, roles: ROLE_CLIENT }
    - { path: ^/espaceclient/nouvelle-reservation, roles: IS_AUTHENTICATED_ANONYMOUSLY }
```

### Données sensibles

- Les credentials email/paiement sont dans `.env.local` (non versionné)
- Les clés de signature RSA sont stockées en dehors du web root
- Les uploads de fichiers sont limités aux images de véhicules

---

## Système de signature électronique

Le projet intègre un système de signature électronique sophistiqué :

### Composants

- `SignatureService` : Génération des clés RSA, signature
- `TsaClient` : Client pour l'horodatage (TSA - Time Stamping Authority)
- `SignatureVerificationService` : Vérification des signatures
- `ContractGeneratorService` : Génération des contrats

### Flux de signature

1. Génération d'une paire de clés RSA par contrat
2. Hashage SHA-256 du contenu du contrat
3. Signature avec la clé privée
4. Horodatage via TSA (FreeTSA)
5. Stockage de la signature et des métadonnées

### Documentation associée

- `GUIDE_SIGNATURE_ELECTRONIQUE.md` : Guide complet
- `SIGNATURE_IMPLEMENTATION.md` : Détails d'implémentation

---

## Guest Booking (réservation sans compte)

Les utilisateurs anonymes peuvent effectuer une réservation complète :

1. Les étapes 1-4 de `/espaceclient/nouvelle-reservation` sont accessibles anonymement
2. Les données sont stockées en session
3. Le compte est créé à la dernière étape après le paiement
4. Les routes concernées sont configurées dans `security.yaml`

---

## Variables d'environnement importantes

### Base de données
```
DATABASE_URL="mysql://root:root@mysql:3306/joellocation"
```

### Email (Gmail SMTP)
```
MAIN_MAILER_DSN=smtp://contact@joellocation.com:password@smtp.gmail.com:587
MAILER_SENDER=contact@joellocation.com
ADMIN_EMAIL=rakotoarinelinarija@gmail.com
```

### PayPal (Sandbox)
```
PAYPAL_CLIENT_ID=AWDZI-LFPNgYnDIXkhBDGTlwkF5HaSDlSKCke0HowHz5xfBAqh4Vdw0_bm3A4JRN2pYgA6J1uZ3ueP12
PAYPAL_CLIENT_SECRET=ELd5p_J3gOiRb372ogtJF_iwvX6TgG5XljSmMO0cjR2DxbdwMGf-MO8PsdHZTKf5Q7MiRE7K7K-T0BWW
PAYPAL_SANDBOX=true
```

### TSA (Time Stamping Authority)
```
TSA_URL="https://freetsa.org/tsa"
```

---

## Notes de développement

### Migration vers Tailwind CSS

Le projet est en transition entre Bootstrap et Tailwind CSS :
- Templates admin : Bootstrap (legacy)
- Templates vitrine et client2 : Tailwind CSS v4
- Les deux systèmes coexistent via des entry points Webpack séparés

### Entités à relations complexes

- `Reservation` : Relations avec User, Vehicule, Paiement, Options, Garanties, Conducteurs
- `User` : Relations avec Reservation, Message
- `Vehicule` : Relations avec Marque, Modele, Type, Agence, Reservation

### Conventions de nommage

- Entités : PascalCase singulier (ex: `Reservation`)
- Tables : snake_case pluriel (généré automatiquement par Doctrine)
- Repositories : suffixe `Repository`
- Services : suffixe `Service`
- Form types : suffixe `Type`

### Gestion des dates

- Utiliser `DateTime` pour les dates/heures
- Le helper `DateHelper` fournit des méthodes utilitaires
- Format d'affichage français par défaut
