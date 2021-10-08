<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211008103357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis CHANGE vehicule_id vehicule_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD prix_options DOUBLE PRECISION DEFAULT NULL, ADD prix_garanties DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis CHANGE vehicule_id vehicule_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation DROP prix_options, DROP prix_garanties');
    }
}
