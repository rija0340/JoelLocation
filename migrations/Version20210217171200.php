<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210217171200 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicule ADD details LONGTEXT DEFAULT NULL, ADD carburation VARCHAR(255) DEFAULT NULL, ADD caution DOUBLE PRECISION DEFAULT NULL, ADD vitesse VARCHAR(255) DEFAULT NULL, ADD bagages VARCHAR(255) DEFAULT NULL, ADD portes VARCHAR(255) DEFAULT NULL, ADD passagers VARCHAR(255) DEFAULT NULL, ADD atouts VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicule DROP details, DROP carburation, DROP caution, DROP vitesse, DROP bagages, DROP portes, DROP passagers, DROP atouts');
    }
}
