<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210608163823 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs CHANGE trois_jours trois_jours DOUBLE PRECISION DEFAULT NULL, CHANGE sept_jours sept_jours DOUBLE PRECISION DEFAULT NULL, CHANGE quinze_jours quinze_jours DOUBLE PRECISION DEFAULT NULL, CHANGE trente_jours trente_jours DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs CHANGE trois_jours trois_jours INT DEFAULT NULL, CHANGE sept_jours sept_jours INT DEFAULT NULL, CHANGE quinze_jours quinze_jours INT DEFAULT NULL, CHANGE trente_jours trente_jours INT DEFAULT NULL');
    }
}
