<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820080809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conducteur (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, date_naissance DATE DEFAULT NULL, ville_naissance VARCHAR(255) DEFAULT NULL, numero_permis VARCHAR(255) DEFAULT NULL, ville_delivrance VARCHAR(255) DEFAULT NULL, date_delivrance DATE DEFAULT NULL, date_obtention DATE DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, INDEX IDX_2367714319EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE modele (id INT AUTO_INCREMENT NOT NULL, marque_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, INDEX IDX_100285584827B9B2 (marque_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conducteur ADD CONSTRAINT FK_2367714319EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modele ADD CONSTRAINT FK_100285584827B9B2 FOREIGN KEY (marque_id) REFERENCES marque (id)');
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C4964A4A3511');
        $this->addSql('DROP INDEX IDX_F9B8C4964A4A3511 ON tarifs');
        $this->addSql('ALTER TABLE tarifs ADD marque_id INT DEFAULT NULL, ADD modele_id INT DEFAULT NULL, DROP vehicule_id');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C4964827B9B2 FOREIGN KEY (marque_id) REFERENCES marque (id)');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C496AC14B70A FOREIGN KEY (modele_id) REFERENCES modele (id)');
        $this->addSql('CREATE INDEX IDX_F9B8C4964827B9B2 ON tarifs (marque_id)');
        $this->addSql('CREATE INDEX IDX_F9B8C496AC14B70A ON tarifs (modele_id)');
        $this->addSql('ALTER TABLE user ADD complement_adresse VARCHAR(255) DEFAULT NULL, ADD ville VARCHAR(255) DEFAULT NULL, ADD code_postal DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule ADD modele_id INT DEFAULT NULL, DROP modele, CHANGE marque_id marque_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DAC14B70A FOREIGN KEY (modele_id) REFERENCES modele (id)');
        $this->addSql('CREATE INDEX IDX_292FFF1DAC14B70A ON vehicule (modele_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C496AC14B70A');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DAC14B70A');
        $this->addSql('DROP TABLE conducteur');
        $this->addSql('DROP TABLE modele');
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C4964827B9B2');
        $this->addSql('DROP INDEX IDX_F9B8C4964827B9B2 ON tarifs');
        $this->addSql('DROP INDEX IDX_F9B8C496AC14B70A ON tarifs');
        $this->addSql('ALTER TABLE tarifs ADD vehicule_id INT NOT NULL, DROP marque_id, DROP modele_id');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C4964A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('CREATE INDEX IDX_F9B8C4964A4A3511 ON tarifs (vehicule_id)');
        $this->addSql('ALTER TABLE user DROP complement_adresse, DROP ville, DROP code_postal');
        $this->addSql('DROP INDEX IDX_292FFF1DAC14B70A ON vehicule');
        $this->addSql('ALTER TABLE vehicule ADD modele VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP modele_id, CHANGE marque_id marque_id INT NOT NULL');
    }
}
