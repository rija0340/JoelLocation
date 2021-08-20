<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818105917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devis_options (devis_id INT NOT NULL, options_id INT NOT NULL, INDEX IDX_42DB61DB41DEFADA (devis_id), INDEX IDX_42DB61DB3ADB05F1 (options_id), PRIMARY KEY(devis_id, options_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE devis_garantie (devis_id INT NOT NULL, garantie_id INT NOT NULL, INDEX IDX_13DC356C41DEFADA (devis_id), INDEX IDX_13DC356CA4B9602F (garantie_id), PRIMARY KEY(devis_id, garantie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE devis_options ADD CONSTRAINT FK_42DB61DB41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis_options ADD CONSTRAINT FK_42DB61DB3ADB05F1 FOREIGN KEY (options_id) REFERENCES options (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis_garantie ADD CONSTRAINT FK_13DC356C41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis_garantie ADD CONSTRAINT FK_13DC356CA4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BA4B9602F');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BBF006E8B');
        $this->addSql('DROP INDEX IDX_8B27C52BA4B9602F ON devis');
        $this->addSql('DROP INDEX IDX_8B27C52BBF006E8B ON devis');
        $this->addSql('ALTER TABLE devis DROP siege_id, DROP garantie_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE devis_options');
        $this->addSql('DROP TABLE devis_garantie');
        $this->addSql('ALTER TABLE devis ADD siege_id INT DEFAULT NULL, ADD garantie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BA4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BBF006E8B FOREIGN KEY (siege_id) REFERENCES options (id)');
        $this->addSql('CREATE INDEX IDX_8B27C52BA4B9602F ON devis (garantie_id)');
        $this->addSql('CREATE INDEX IDX_8B27C52BBF006E8B ON devis (siege_id)');
    }
}
