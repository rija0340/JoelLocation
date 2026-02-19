<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217165502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_8d93d649b83297e7 TO IDX_E98F2859B83297E7');
        $this->addSql('DROP INDEX idx_document_type ON contract_signature');
        $this->addSql('ALTER TABLE contract_signature CHANGE document_type document_type VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE contract_signature RENAME INDEX idx_78fbc3a12576e0a7 TO IDX_831F59D72576E0FD');
        $this->addSql('ALTER TABLE reservation_photo ADD type VARCHAR(20) DEFAULT \'depart\' NOT NULL');
        $this->addSql('ALTER TABLE vehicule DROP INDEX IDX_292FFF1DC1E5BBF9, ADD UNIQUE INDEX UNIQ_292FFF1DC1E5BBF9 (saisisseur_km_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_e98f2859b83297e7 TO IDX_8D93D649B83297E7');
        $this->addSql('ALTER TABLE contract_signature CHANGE document_type document_type VARCHAR(20) DEFAULT \'contract\' NOT NULL');
        $this->addSql('CREATE INDEX idx_document_type ON contract_signature (document_type)');
        $this->addSql('ALTER TABLE contract_signature RENAME INDEX idx_831f59d72576e0fd TO IDX_78FBC3A12576E0A7');
        $this->addSql('ALTER TABLE reservation_photo DROP type');
        $this->addSql('ALTER TABLE vehicule DROP INDEX UNIQ_292FFF1DC1E5BBF9, ADD INDEX IDX_292FFF1DC1E5BBF9 (saisisseur_km_id)');
    }
}
