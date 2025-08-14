<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250810130310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_photo (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, image VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_57C854BFB83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_photo ADD CONSTRAINT FK_57C854BFB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reservation_photo');
        $this->addSql('ALTER TABLE reservation ADD saisisseur_km_id INT DEFAULT NULL, ADD km_depart DOUBLE PRECISION DEFAULT NULL, ADD km_retour DOUBLE PRECISION DEFAULT NULL, ADD date_km DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955C1E5BBF9 FOREIGN KEY (saisisseur_km_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_42C84955C1E5BBF9 ON reservation (saisisseur_km_id)');
    }
}
