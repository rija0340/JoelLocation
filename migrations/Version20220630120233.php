<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220630120233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE frais_suppl_resa CHANGE remise remise DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule DROP INDEX FK_292FFF1DC1E5BBF9, ADD UNIQUE INDEX UNIQ_292FFF1DC1E5BBF9 (saisisseur_km_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE frais_suppl_resa CHANGE remise remise DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE vehicule DROP INDEX UNIQ_292FFF1DC1E5BBF9, ADD INDEX FK_292FFF1DC1E5BBF9 (saisisseur_km_id)');
    }
}
