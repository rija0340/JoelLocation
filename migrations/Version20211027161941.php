<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211027161941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE infos_resa (id INT AUTO_INCREMENT NOT NULL, nbr_adultes DOUBLE PRECISION DEFAULT NULL, nbr_enfants DOUBLE PRECISION DEFAULT NULL, nbr_bebes DOUBLE PRECISION DEFAULT NULL, infor_internes VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE infos_vol_resa (id INT AUTO_INCREMENT NOT NULL, compagnie_aller VARCHAR(255) DEFAULT NULL, compagnie_retour VARCHAR(255) DEFAULT NULL, num_vol_aller VARCHAR(255) DEFAULT NULL, num_vol_retour VARCHAR(255) DEFAULT NULL, heure_vol_aller DATETIME DEFAULT NULL, heure_vol_retour DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE infos_resa');
        $this->addSql('DROP TABLE infos_vol_resa');
    }
}
