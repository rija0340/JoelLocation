<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tarifs_v2 table with JSON pricing ranges';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tarifs_v2 (
            id INT AUTO_INCREMENT NOT NULL, 
            marque_id INT NOT NULL, 
            modele_id INT NOT NULL, 
            mois VARCHAR(255) NOT NULL, 
            tarifs LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\',
            INDEX IDX_5C4C0CB5A92B14E9 (marque_id),
            INDEX IDX_5C4C0CB5A92B14EA (modele_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE tarifs_v2 ADD CONSTRAINT FK_5C4C0CB5A92B14E9 FOREIGN KEY (marque_id) REFERENCES marque (id)');
        $this->addSql('ALTER TABLE tarifs_v2 ADD CONSTRAINT FK_5C4C0CB5A92B14EA FOREIGN KEY (modele_id) REFERENCES modele (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tarifs_v2');
    }
}
