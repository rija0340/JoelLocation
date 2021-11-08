<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211107051503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE infos_resa (id INT AUTO_INCREMENT NOT NULL, nbr_adultes DOUBLE PRECISION DEFAULT NULL, nbr_enfants DOUBLE PRECISION DEFAULT NULL, nbr_bebes DOUBLE PRECISION DEFAULT NULL, infos_internes VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE infos_vol_resa (id INT AUTO_INCREMENT NOT NULL, compagnie_aller VARCHAR(255) DEFAULT NULL, compagnie_retour VARCHAR(255) DEFAULT NULL, num_vol_aller VARCHAR(255) DEFAULT NULL, num_vol_retour VARCHAR(255) DEFAULT NULL, heure_vol_aller DATETIME DEFAULT NULL, heure_vol_retour DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mail (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, mail VARCHAR(255) NOT NULL, objet VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, date_reception DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE avis ADD reservation_id INT DEFAULT NULL, CHANGE global global INT DEFAULT NULL, CHANGE ponctualite ponctualite INT DEFAULT NULL, CHANGE accueil accueil INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F91ABF0B83297E7 ON avis (reservation_id)');
        $this->addSql('ALTER TABLE conducteur DROP FOREIGN KEY FK_2367714319EB6921');
        $this->addSql('DROP INDEX IDX_2367714319EB6921 ON conducteur');
        $this->addSql('ALTER TABLE conducteur CHANGE client_id reservation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conducteur ADD CONSTRAINT FK_23677143B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_23677143B83297E7 ON conducteur (reservation_id)');
        $this->addSql('ALTER TABLE reservation ADD archived TINYINT(1) NOT NULL, ADD canceled TINYINT(1) NOT NULL, DROP km_depart, DROP km_retour, CHANGE conducteur conducteur TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD infos_resa_id INT DEFAULT NULL, ADD infos_vol_resa_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491DF27D6E FOREIGN KEY (infos_resa_id) REFERENCES infos_resa (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B62F3B9C FOREIGN KEY (infos_vol_resa_id) REFERENCES infos_vol_resa (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6491DF27D6E ON user (infos_resa_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B62F3B9C ON user (infos_vol_resa_id)');
        $this->addSql('ALTER TABLE vehicule ADD km_depart DOUBLE PRECISION DEFAULT NULL, ADD km_retour DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491DF27D6E');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B62F3B9C');
        $this->addSql('DROP TABLE infos_resa');
        $this->addSql('DROP TABLE infos_vol_resa');
        $this->addSql('DROP TABLE mail');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0B83297E7');
        $this->addSql('DROP INDEX UNIQ_8F91ABF0B83297E7 ON avis');
        $this->addSql('ALTER TABLE avis DROP reservation_id, CHANGE global global INT NOT NULL, CHANGE ponctualite ponctualite INT NOT NULL, CHANGE accueil accueil INT NOT NULL, CHANGE service service INT NOT NULL');
        $this->addSql('ALTER TABLE conducteur DROP FOREIGN KEY FK_23677143B83297E7');
        $this->addSql('DROP INDEX IDX_23677143B83297E7 ON conducteur');
        $this->addSql('ALTER TABLE conducteur CHANGE reservation_id client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conducteur ADD CONSTRAINT FK_2367714319EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2367714319EB6921 ON conducteur (client_id)');
        $this->addSql('ALTER TABLE reservation ADD km_depart DOUBLE PRECISION DEFAULT NULL, ADD km_retour DOUBLE PRECISION DEFAULT NULL, DROP archived, DROP canceled, CHANGE conducteur conducteur VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX UNIQ_8D93D6491DF27D6E ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649B62F3B9C ON user');
        $this->addSql('ALTER TABLE user DROP infos_resa_id, DROP infos_vol_resa_id');
        $this->addSql('ALTER TABLE vehicule DROP km_depart, DROP km_retour');
    }
}
