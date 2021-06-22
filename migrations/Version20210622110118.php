<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622110118 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis ADD siege_id INT DEFAULT NULL, ADD garantie_id INT DEFAULT NULL, DROP siege, DROP garantie');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BBF006E8B FOREIGN KEY (siege_id) REFERENCES options (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BA4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id)');
        $this->addSql('CREATE INDEX IDX_8B27C52BBF006E8B ON devis (siege_id)');
        $this->addSql('CREATE INDEX IDX_8B27C52BA4B9602F ON devis (garantie_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BBF006E8B');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BA4B9602F');
        $this->addSql('DROP INDEX IDX_8B27C52BBF006E8B ON devis');
        $this->addSql('DROP INDEX IDX_8B27C52BA4B9602F ON devis');
        $this->addSql('ALTER TABLE devis ADD siege LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', ADD garantie LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', DROP siege_id, DROP garantie_id');
    }
}
