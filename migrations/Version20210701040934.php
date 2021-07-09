<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701040934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD siege_id INT DEFAULT NULL, ADD garantie_id INT DEFAULT NULL, ADD prix DOUBLE PRECISION DEFAULT NULL, ADD duree DOUBLE PRECISION DEFAULT NULL, DROP siege, DROP garantie, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955BF006E8B FOREIGN KEY (siege_id) REFERENCES options (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id)');
        $this->addSql('CREATE INDEX IDX_42C84955BF006E8B ON reservation (siege_id)');
        $this->addSql('CREATE INDEX IDX_42C84955A4B9602F ON reservation (garantie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955BF006E8B');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A4B9602F');
        $this->addSql('DROP INDEX IDX_42C84955BF006E8B ON reservation');
        $this->addSql('DROP INDEX IDX_42C84955A4B9602F ON reservation');
        $this->addSql('ALTER TABLE reservation ADD siege VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD garantie VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP siege_id, DROP garantie_id, DROP prix, DROP duree, CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
