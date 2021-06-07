<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210607102446 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs ADD quinze_jours INT DEFAULT NULL, ADD trente_jours INT DEFAULT NULL, DROP une_semaine, DROP un_mois');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs ADD une_semaine INT DEFAULT NULL, ADD un_mois INT DEFAULT NULL, DROP quinze_jours, DROP trente_jours');
    }
}
