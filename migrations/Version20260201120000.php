<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter le système de signature des états des lieux (départ/retour)
 */
final class Version20260201120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute document_type à contract_signature pour gérer les signatures des états des lieux';
    }

    public function up(Schema $schema): void
    {
        // Ajouter la colonne document_type avec une valeur par défaut 'contract'
        $this->addSql("ALTER TABLE contract_signature ADD document_type VARCHAR(20) NOT NULL DEFAULT 'contract'");
        
        // Note: contract_id reste NOT NULL car toutes les signatures sont liées à un contrat
        
        // Créer un index sur document_type pour les recherches rapides
        $this->addSql('CREATE INDEX idx_document_type ON contract_signature (document_type)');
    }

    public function down(Schema $schema): void
    {
        // Supprimer l'index
        $this->addSql('DROP INDEX idx_document_type ON contract_signature');
        
        // Supprimer la colonne document_type
        $this->addSql('ALTER TABLE contract_signature DROP document_type');
        
        // Note: contract_id n'a pas été modifié
    }
}
