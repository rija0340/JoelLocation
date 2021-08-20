<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820090624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_options (reservation_id INT NOT NULL, options_id INT NOT NULL, INDEX IDX_B7A04102B83297E7 (reservation_id), INDEX IDX_B7A041023ADB05F1 (options_id), PRIMARY KEY(reservation_id, options_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation_garantie (reservation_id INT NOT NULL, garantie_id INT NOT NULL, INDEX IDX_EC26243CB83297E7 (reservation_id), INDEX IDX_EC26243CA4B9602F (garantie_id), PRIMARY KEY(reservation_id, garantie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_options ADD CONSTRAINT FK_B7A04102B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_options ADD CONSTRAINT FK_B7A041023ADB05F1 FOREIGN KEY (options_id) REFERENCES options (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_garantie ADD CONSTRAINT FK_EC26243CB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_garantie ADD CONSTRAINT FK_EC26243CA4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A4B9602F');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955BF006E8B');
        $this->addSql('DROP INDEX IDX_42C84955A4B9602F ON reservation');
        $this->addSql('DROP INDEX IDX_42C84955BF006E8B ON reservation');
        $this->addSql('ALTER TABLE reservation DROP siege_id, DROP garantie_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reservation_options');
        $this->addSql('DROP TABLE reservation_garantie');
        $this->addSql('ALTER TABLE reservation ADD siege_id INT DEFAULT NULL, ADD garantie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955BF006E8B FOREIGN KEY (siege_id) REFERENCES options (id)');
        $this->addSql('CREATE INDEX IDX_42C84955A4B9602F ON reservation (garantie_id)');
        $this->addSql('CREATE INDEX IDX_42C84955BF006E8B ON reservation (siege_id)');
    }
}
