<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211123050923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE infos_resa CHANGE nbr_adultes nbr_adultes INT DEFAULT NULL, CHANGE nbr_enfants nbr_enfants INT DEFAULT NULL, CHANGE nbr_bebes nbr_bebes INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE infos_resa CHANGE nbr_adultes nbr_adultes DOUBLE PRECISION DEFAULT NULL, CHANGE nbr_enfants nbr_enfants DOUBLE PRECISION DEFAULT NULL, CHANGE nbr_bebes nbr_bebes DOUBLE PRECISION DEFAULT NULL');
    }
}
