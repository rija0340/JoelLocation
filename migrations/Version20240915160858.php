<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240915160858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD saisisseur_km_id INT DEFAULT NULL, ADD km_depart DOUBLE PRECISION DEFAULT NULL, ADD km_retour DOUBLE PRECISION DEFAULT NULL, ADD date_km DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955C1E5BBF9 FOREIGN KEY (saisisseur_km_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_42C84955C1E5BBF9 ON reservation (saisisseur_km_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955C1E5BBF9');
        $this->addSql('DROP INDEX IDX_42C84955C1E5BBF9 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP saisisseur_km_id, DROP km_depart, DROP km_retour, DROP date_km');
    }
}
