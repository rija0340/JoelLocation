<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622092945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devis (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, vehicule_id INT NOT NULL, date_depart DATETIME NOT NULL, date_retour DATETIME NOT NULL, agence_depart VARCHAR(255) NOT NULL, agence_retour VARCHAR(255) NOT NULL, lieu_sejour VARCHAR(255) NOT NULL, conducteur TINYINT(1) NOT NULL, siege LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', garantie LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date_creation DATETIME DEFAULT NULL, duree DOUBLE PRECISION NOT NULL, prix DOUBLE PRECISION NOT NULL, INDEX IDX_8B27C52B19EB6921 (client_id), INDEX IDX_8B27C52B4A4A3511 (vehicule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE reservation ADD agence_depart VARCHAR(255) DEFAULT NULL, ADD agence_retour VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE adresse adresse VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE devis');
        $this->addSql('ALTER TABLE reservation DROP agence_depart, DROP agence_retour');
        $this->addSql('ALTER TABLE user CHANGE adresse adresse VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
