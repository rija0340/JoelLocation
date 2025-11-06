# JoelLocation - Symfony Application

## Project Overview

JoelLocation is a comprehensive vehicle rental management system built with Symfony 5.3. The application provides a complete solution for managing vehicle rentals, reservations, customer accounts, and administrative operations. It features both a customer-facing frontend and an administrative backoffice interface for managing business operations.

## Architecture & Technologies

- **Framework**: Symfony 5.3 (PHP)
- **Frontend**: Built with Webpack Encore, Bootstrap, jQuery, and custom JavaScript/CSS
- **Database**: MySQL/MariaDB with Doctrine ORM
- **Email**: Mailjet API and SMTP integration
- **PDF Generation**: DomPDF and KnpSnappyBundle
- **Payment**: Stripe integration
- **File Uploads**: VichUploaderBundle
- **Pagination**: KnpPaginatorBundle

## Key Features

### Customer Features
- Vehicle browsing and reservation system
- Guest booking flow (allows booking without creating an account)
- User registration and authentication
- Account management (profile, reservations, payment history)
- Contact form with email notifications
- FAQ section
- Reservation validation and payment processing

### Administrative Features
- Dashboard with business analytics (chiffre d'affaire)
- Vehicle management (models, brands, types, tariffs)
- Reservation management and planning
- Customer management
- Payment tracking
- Reservation cancellation handling
- Agency management
- Options and guarantees management

### Planning System
- Traditional dhtmlxGantt-based vehicle planning
- Modern responsive planning interface with timeline view
- Mobile-friendly reservation visualization
- Vehicle availability tracking

## Project Structure

```
symfony-app/
├── assets/                 # Frontend assets (SCSS, JS)
│   ├── backoffice/         # Admin interface assets
│   └── vitrine/            # Customer interface assets
├── config/                 # Symfony configuration
├── migrations/             # Database migrations
├── public/                 # Web root
├── src/                    # PHP source code
│   ├── Controller/         # Controllers (Admin, Client subdirectories)
│   ├── Entity/            # Doctrine entities
│   ├── Form/              # Form types
│   ├── Repository/        # Doctrine repositories
│   ├── Service/           # Service classes
│   └── Twig/              # Twig extensions
├── templates/             # Twig templates (accueil, admin, backoffice)
├── tests/                 # PHPUnit tests
└── var/                   # Runtime files (logs, cache)
```

## Building and Running

### Prerequisites
- PHP 7.2.5+
- Composer
- Node.js and npm/yarn
- MySQL or MariaDB

### Setup Instructions

1. **Install PHP dependencies**:
   ```bash
   composer install
   ```

2. **Install frontend dependencies**:
   ```bash
   npm install
   # or
   yarn install
   ```

3. **Configure environment**:
   - Copy `.env` or use `.env.save` as reference
   - Update database connection in `DATABASE_URL`
   - Configure email settings (`MAILER_DSN`)
   - Configure Mailjet credentials if using Mailjet

4. **Database setup**:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Build frontend assets**:
   ```bash
   npm run build
   # or for development with watch
   npm run dev
   npm run watch
   ```

6. **Start development server**:
   ```bash
   php bin/console server:run
   ```

### Frontend Build Commands
- `npm run dev` - Build assets for development
- `npm run watch` - Watch for changes and rebuild
- `npm run build` - Build assets for production (with optimization)

## Key Functionality

### Guest Booking Flow
The system implements a sophisticated guest booking workflow:
1. Users can start reservations without authentication via "Réserver maintenant" button
2. Reservation data is stored in session during the process
3. At final validation step, users are redirected to registration
4. Guest data is pre-populated in registration form
5. After successful registration, the reservation is linked to the new account

### Planning System Options
The application provides two planning interfaces:
1. **Traditional**: Based on dhtmlxGantt library
2. **Modern**: Responsive timeline view built with pure JavaScript/CSS

### Security Configuration
- Role-based access control (ROLE_CLIENT, ROLE_PERSONNEL, ROLE_ADMIN)
- Anonymous access allowed for booking flow steps
- Password encoding with Symfony's encoder
- Account activation by email verification

## Development Conventions

### Frontend
- SCSS is used for styling with Bootstrap components
- Webpack Encore manages asset compilation
- Responsive design with mobile-first approach
- jQuery for DOM manipulation where needed

### Backend
- Doctrine ORM for database interactions
- Form handling with Symfony Form component
- Twig templates with consistent structure
- Service classes for business logic

### Routing
- Annotations used for route definition
- Controllers organized by functionality (Admin, Client subdirectories)
- RESTful patterns where appropriate

## Testing

- PHPUnit tests in `tests/` directory
- Symfony PHPUnit bridge configured
- Browser testing capabilities with Symfony's test tools

## Notable Configuration Files

- `composer.json` - PHP dependencies and autoloading
- `package.json` - Frontend dependencies and build scripts
- `webpack.config.js` - Asset compilation configuration
- `config/packages/` - Bundle-specific configurations
- `.env` - Environment variables

## Key Entities

- `User` - Customer and staff accounts
- `Vehicule` - Rental vehicles with make/model specifications
- `Reservation` - Vehicle rental bookings
- `Devis` - Estimates/quotes for reservations
- `Paiement` - Payment tracking
- `Agence` - Rental locations

This application serves as a complete vehicle rental management system with sophisticated features for both customers and administrators.