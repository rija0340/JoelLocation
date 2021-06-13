<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210612160618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955FB88E14F');
        $this->addSql('DROP INDEX IDX_42C84955FB88E14F ON reservation');
        $this->addSql('ALTER TABLE reservation ADD conducteur VARCHAR(255) DEFAULT NULL, ADD siege VARCHAR(255) DEFAULT NULL, ADD garantie VARCHAR(255) DEFAULT NULL, ADD commentaire VARCHAR(255) DEFAULT NULL, DROP utilisateur_id, CHANGE mode_reservation_id mode_reservation_id INT DEFAULT NULL, CHANGE etat_reservation_id etat_reservation_id INT DEFAULT NULL, CHANGE lieu lieu VARCHAR(255) DEFAULT NULL, CHANGE code_reservation code_reservation VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule DROP options, DROP prix, DROP garantie, DROP commentaire');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD utilisateur_id INT DEFAULT NULL, DROP conducteur, DROP siege, DROP garantie, DROP commentaire, CHANGE mode_reservation_id mode_reservation_id INT NOT NULL, CHANGE etat_reservation_id etat_reservation_id INT NOT NULL, CHANGE lieu lieu VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE code_reservation code_reservation VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_42C84955FB88E14F ON reservation (utilisateur_id)');
        $this->addSql('ALTER TABLE vehicule ADD options VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD prix VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD garantie VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD commentaire VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
