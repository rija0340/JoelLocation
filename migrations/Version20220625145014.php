<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220625145014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, infos_resa_id INT DEFAULT NULL, infos_vol_resa_id INT DEFAULT NULL, username VARCHAR(180) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, mail VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, portable VARCHAR(255) DEFAULT NULL, presence TINYINT(1) NOT NULL, date_inscription DATETIME NOT NULL, fonction VARCHAR(255) DEFAULT NULL, recupass VARCHAR(255) DEFAULT NULL, date_naissance DATE DEFAULT NULL, numero_permis VARCHAR(1000) DEFAULT NULL, date_permis DATE DEFAULT NULL, lieu_naissance VARCHAR(255) DEFAULT NULL, complement_adresse VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, code_postal DOUBLE PRECISION DEFAULT NULL, ville_delivrance_permis VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D6495126AC48 (mail), UNIQUE INDEX UNIQ_8D93D6491DF27D6E (infos_resa_id), UNIQUE INDEX UNIQ_8D93D649B62F3B9C (infos_vol_resa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, type_id INT DEFAULT NULL, modele_id INT DEFAULT NULL, marque_id INT DEFAULT NULL, saisisseur_km_id INT DEFAULT NULL, immatriculation VARCHAR(255) NOT NULL, date_mise_service DATE DEFAULT NULL, date_mise_location DATE NOT NULL, prix_acquisition INT DEFAULT NULL, details LONGTEXT DEFAULT NULL, carburation VARCHAR(255) DEFAULT NULL, caution DOUBLE PRECISION DEFAULT NULL, vitesse VARCHAR(255) DEFAULT NULL, bagages VARCHAR(255) DEFAULT NULL, portes VARCHAR(255) DEFAULT NULL, passagers VARCHAR(255) DEFAULT NULL, atouts VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, km_depart DOUBLE PRECISION DEFAULT NULL, km_retour DOUBLE PRECISION DEFAULT NULL, date_km DATETIME DEFAULT NULL, INDEX IDX_292FFF1DC54C8C93 (type_id), INDEX IDX_292FFF1DAC14B70A (modele_id), INDEX IDX_292FFF1D4827B9B2 (marque_id), UNIQUE INDEX UNIQ_292FFF1DC1E5BBF9 (saisisseur_km_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491DF27D6E FOREIGN KEY (infos_resa_id) REFERENCES infos_resa (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B62F3B9C FOREIGN KEY (infos_vol_resa_id) REFERENCES infos_vol_resa (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DAC14B70A FOREIGN KEY (modele_id) REFERENCES modele (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D4827B9B2 FOREIGN KEY (marque_id) REFERENCES marque (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DC1E5BBF9 FOREIGN KEY (saisisseur_km_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE annulation_reservation ADD CONSTRAINT FK_4418C7BBB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE appel_paiement ADD CONSTRAINT FK_CD9A1FBCB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE conducteur ADD CONSTRAINT FK_2367714319EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE devis_options ADD CONSTRAINT FK_42DB61DB41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis_options ADD CONSTRAINT FK_42DB61DB3ADB05F1 FOREIGN KEY (options_id) REFERENCES options (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis_garantie ADD CONSTRAINT FK_13DC356C41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis_garantie ADD CONSTRAINT FK_13DC356CA4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modele ADD CONSTRAINT FK_100285584827B9B2 FOREIGN KEY (marque_id) REFERENCES marque (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E438F5B63 FOREIGN KEY (mode_paiement_id) REFERENCES mode_paiement (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849554A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849556776468B FOREIGN KEY (mode_reservation_id) REFERENCES mode_reservation (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495514237FB FOREIGN KEY (etat_reservation_id) REFERENCES etat_reservation (id)');
        $this->addSql('ALTER TABLE reservation_options ADD CONSTRAINT FK_B7A04102B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_options ADD CONSTRAINT FK_B7A041023ADB05F1 FOREIGN KEY (options_id) REFERENCES options (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_garantie ADD CONSTRAINT FK_EC26243CB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_garantie ADD CONSTRAINT FK_EC26243CA4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_conducteur ADD CONSTRAINT FK_43CDB8F7B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_conducteur ADD CONSTRAINT FK_43CDB8F7F16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES conducteur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password ADD CONSTRAINT FK_B9983CE5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C4964827B9B2 FOREIGN KEY (marque_id) REFERENCES marque (id)');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C496AC14B70A FOREIGN KEY (modele_id) REFERENCES modele (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conducteur DROP FOREIGN KEY FK_2367714319EB6921');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B19EB6921');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F19EB6921');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E19EB6921');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921');
        $this->addSql('ALTER TABLE reset_password DROP FOREIGN KEY FK_B9983CE5A76ED395');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DC1E5BBF9');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B4A4A3511');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554A4A3511');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vehicule');
        $this->addSql('ALTER TABLE annulation_reservation DROP FOREIGN KEY FK_4418C7BBB83297E7');
        $this->addSql('ALTER TABLE appel_paiement DROP FOREIGN KEY FK_CD9A1FBCB83297E7');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0B83297E7');
        $this->addSql('ALTER TABLE devis_garantie DROP FOREIGN KEY FK_13DC356C41DEFADA');
        $this->addSql('ALTER TABLE devis_garantie DROP FOREIGN KEY FK_13DC356CA4B9602F');
        $this->addSql('ALTER TABLE devis_options DROP FOREIGN KEY FK_42DB61DB41DEFADA');
        $this->addSql('ALTER TABLE devis_options DROP FOREIGN KEY FK_42DB61DB3ADB05F1');
        $this->addSql('ALTER TABLE modele DROP FOREIGN KEY FK_100285584827B9B2');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EB83297E7');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E438F5B63');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849556776468B');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495514237FB');
        $this->addSql('ALTER TABLE reservation_conducteur DROP FOREIGN KEY FK_43CDB8F7B83297E7');
        $this->addSql('ALTER TABLE reservation_conducteur DROP FOREIGN KEY FK_43CDB8F7F16F4AC6');
        $this->addSql('ALTER TABLE reservation_garantie DROP FOREIGN KEY FK_EC26243CB83297E7');
        $this->addSql('ALTER TABLE reservation_garantie DROP FOREIGN KEY FK_EC26243CA4B9602F');
        $this->addSql('ALTER TABLE reservation_options DROP FOREIGN KEY FK_B7A04102B83297E7');
        $this->addSql('ALTER TABLE reservation_options DROP FOREIGN KEY FK_B7A041023ADB05F1');
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C4964827B9B2');
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C496AC14B70A');
    }
}
