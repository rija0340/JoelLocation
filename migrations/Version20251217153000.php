<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Contract and ContractSignature entities for electronic signature system';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, contract_hash VARCHAR(255) NOT NULL, contract_status VARCHAR(50) NOT NULL, contract_content LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_8D93D649B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_signature (id INT AUTO_INCREMENT NOT NULL, contract_id INT NOT NULL, signature_type VARCHAR(20) NOT NULL, signature_data LONGTEXT NOT NULL, public_key_data LONGTEXT NOT NULL, signed_at DATETIME NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, signature_valid TINYINT(1) NOT NULL, timestamp_token LONGTEXT DEFAULT NULL, timestamp_verified_at DATETIME DEFAULT NULL, INDEX IDX_78FBC3A12576E0A7 (contract_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_8D93D649B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE contract_signature ADD CONSTRAINT FK_78FBC3A12576E0A7 FOREIGN KEY (contract_id) REFERENCES contract (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_8D93D649B83297E7');
        $this->addSql('ALTER TABLE contract_signature DROP FOREIGN KEY FK_78FBC3A12576E0A7');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE contract_signature');
    }
}