<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241222103345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis_option ADD reservation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE devis_option ADD CONSTRAINT FK_6693C77CB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_6693C77CB83297E7 ON devis_option (reservation_id)');
        $this->addSql('ALTER TABLE vehicule DROP INDEX IDX_292FFF1DC1E5BBF9, ADD UNIQUE INDEX UNIQ_292FFF1DC1E5BBF9 (saisisseur_km_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis_option DROP FOREIGN KEY FK_6693C77CB83297E7');
        $this->addSql('DROP INDEX IDX_6693C77CB83297E7 ON devis_option');
        $this->addSql('ALTER TABLE devis_option DROP reservation_id');
        $this->addSql('ALTER TABLE vehicule DROP INDEX UNIQ_292FFF1DC1E5BBF9, ADD INDEX IDX_292FFF1DC1E5BBF9 (saisisseur_km_id)');
    }
}
