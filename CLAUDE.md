# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

JoelLocation is a vehicle rental management system built with Symfony 5.3 (PHP). It features a customer-facing frontend for browsing vehicles and making reservations, an administrative backoffice for managing operations, and a modern planning system for tracking vehicle availability.

## Common Commands

### PHP/Symfony
```bash
# Install dependencies
composer install

# Run Symfony console commands
php bin/console cache:clear
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:migrations:diff
php bin/console server:run

# Run tests
php bin/phpunit
./vendor/bin/phpunit tests/Service/ReservationServiceTest.php  # Single test file
./vendor/bin/phpunit --filter testMethodName                    # Single test method
```

### Frontend Build (Webpack Encore)
```bash
# Install dependencies
npm install

# Development build
npm run dev

# Watch mode (rebuilds on file changes)
npm run watch

# Production build
npm run build
```

## Architecture

### Tech Stack
- **Framework**: Symfony 5.3 with PHP 7.2.5+
- **Database**: MySQL/MariaDB with Doctrine ORM
- **Frontend**: Webpack Encore, Tailwind CSS v4, Bootstrap, jQuery
- **PDF Generation**: DomPDF and KnpSnappyBundle (wkhtmltopdf)
- **Email**: Mailjet API and Symfony Mailer
- **Payment**: PayPal integration (Stripe configured but unused)
- **File Uploads**: VichUploaderBundle for vehicle images

### Directory Structure
```
src/
├── Classe/           # Helper classes (Mailjet, Payment, etc.)
├── Controller/
│   ├── Client/       # Client-specific controllers
│   └── Testing/      # Test controllers
├── Entity/           # Doctrine entities (35+ entities)
├── Form/             # Symfony form types
├── Repository/       # Doctrine repositories
├── Service/          # Business logic services (20+ services)
├── Security/         # LoginFormAuthenticator
└── Twig/             # Twig extensions

templates/
├── admin/            # Admin dashboard templates (Bootstrap)
├── backoffice/       # Backoffice templates
├── client/           # Client area templates
├── client2/          # New client interface (Tailwind)
├── vitrine/          # Public website templates
└── pdf_generation/   # PDF contract templates

assets/
├── backoffice/       # Admin interface assets (SCSS/JS)
└── vitrine/          # Customer-facing assets (Tailwind, images)
```

### Key Entities
- **User**: Customer and staff accounts with role hierarchy
- **Vehicule**: Rental vehicles with make/model
- **Reservation**: Central booking entity linking vehicles, clients, and dates
- **Devis**: Quotes/estimates for reservations
- **Paiement**: Payment tracking
- **Contract/ContractSignature**: Electronic signature system
- **Agence**: Rental locations
- **Options/Garanties**: Rental options and insurance

### Security & Roles
Role hierarchy (from lowest to highest):
```
ROLE_USER < ROLE_CLIENT < ROLE_PERSONNEL < ROLE_SUPERVISEUR < ROLE_ADMIN < ROLE_SUPER_ADMIN
```

Key access controls:
- `/admin`, `/backoffice`: Requires ROLE_PERSONNEL+
- `/espaceclient`: Requires ROLE_CLIENT (except guest booking flow)
- Guest booking paths (`/espaceclient/nouvelle-reservation/*`): Anonymous access allowed

### Frontend Build System
Webpack Encore configuration (`webpack.config.js`) has multiple entry points:
- `vitrine`: Legacy JS for old Bootstrap templates
- `vitrine_styles`: Tailwind CSS v4 for new templates
- `admin_*`: Various admin dashboard entry points

Tailwind CSS v4 is configured in `postcss.config.js` with DaisyUI plugin. Custom colors defined in `tailwind.config.js` use Joel branding.

### Key Services
Located in `src/Service/`:
- `PdfGenerationService`: PDF contract generation using DomPDF
- `SignatureService`: Electronic signature handling with RSA keys and TSA
- `ContractService`: Contract workflow management
- `EmailService`: Email sending via Mailjet
- `PaymentProcessingService`: PayPal payment handling
- `VehicleAvailabilityService`: Vehicle booking availability logic

### Testing
PHPUnit is configured in `phpunit.xml.dist`. Tests are in `tests/` directory:
- `tests/Service/ReservationServiceTest.php`
- `tests/Controller/BackofficeAuthTest.php`
- `tests/Controller/ReservationControllerTest.php`

Run single test: `./vendor/bin/phpunit tests/Service/ReservationServiceTest.php`
Run single method: `./vendor/bin/phpunit --filter testMethodName`

### Database Migrations
Doctrine Migrations are used for schema changes:
```bash
php bin/console doctrine:migrations:diff    # Generate new migration
php bin/console doctrine:migrations:migrate # Run pending migrations
```

### Electronic Signature System
The application has a sophisticated electronic signature system (see `GUIDE_SIGNATURE_ELECTRONIQUE.md` and `SIGNATURE_IMPLEMENTATION.md`):
- RSA key pair generation per contract
- SHA-256 hashing of contract content
- TSA (Timestamp Authority) integration for legal validity
- Separate signing workflows for clients and administrators
- Signature verification and logging

### Guest Booking Flow
Anonymous users can start reservations and complete payment before creating an account. Session-based data storage is used during the flow, with account creation at the final step.
