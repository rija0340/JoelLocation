<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210606172315 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs ADD vehicule_id INT NOT NULL');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C4964A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F9B8C4964A4A3511 ON tarifs (vehicule_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C4964A4A3511');
        $this->addSql('DROP INDEX UNIQ_F9B8C4964A4A3511 ON tarifs');
        $this->addSql('ALTER TABLE tarifs DROP vehicule_id');
    }
}
