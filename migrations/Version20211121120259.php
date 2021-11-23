<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211121120259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicule ADD saisisseur_km_id INT DEFAULT NULL, ADD date_km DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DC1E5BBF9 FOREIGN KEY (saisisseur_km_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_292FFF1DC1E5BBF9 ON vehicule (saisisseur_km_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DC1E5BBF9');
        $this->addSql('DROP INDEX UNIQ_292FFF1DC1E5BBF9 ON vehicule');
        $this->addSql('ALTER TABLE vehicule DROP saisisseur_km_id, DROP date_km');
    }
}
