<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220629114234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE frais_suppl_resa (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, description LONGTEXT NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, quantite DOUBLE PRECISION NOT NULL, remise DOUBLE PRECISION NOT NULL, total_ht DOUBLE PRECISION NOT NULL, INDEX IDX_9B1D74A2B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE frais_suppl_resa ADD CONSTRAINT FK_9B1D74A2B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EFB88E14F');
        $this->addSql('DROP INDEX IDX_B1DC7A1EFB88E14F ON paiement');
        $this->addSql('ALTER TABLE paiement DROP utilisateur_id, CHANGE montant montant DOUBLE PRECISION NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6495126AC48 ON user (mail)');
        $this->addSql('ALTER TABLE vehicule DROP INDEX FK_292FFF1DC1E5BBF9, ADD UNIQUE INDEX UNIQ_292FFF1DC1E5BBF9 (saisisseur_km_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE frais_suppl_resa');
        $this->addSql('ALTER TABLE paiement ADD utilisateur_id INT DEFAULT NULL, CHANGE montant montant INT NOT NULL');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B1DC7A1EFB88E14F ON paiement (utilisateur_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D6495126AC48 ON user');
        $this->addSql('ALTER TABLE vehicule DROP INDEX UNIQ_292FFF1DC1E5BBF9, ADD INDEX FK_292FFF1DC1E5BBF9 (saisisseur_km_id)');
    }
}
