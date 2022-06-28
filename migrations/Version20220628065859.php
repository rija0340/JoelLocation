<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220628065859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE frais_suppl_resa ADD reservation_id INT NOT NULL');
        $this->addSql('ALTER TABLE frais_suppl_resa ADD CONSTRAINT FK_9B1D74A2B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_9B1D74A2B83297E7 ON frais_suppl_resa (reservation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE frais_suppl_resa DROP FOREIGN KEY FK_9B1D74A2B83297E7');
        $this->addSql('DROP INDEX IDX_9B1D74A2B83297E7 ON frais_suppl_resa');
        $this->addSql('ALTER TABLE frais_suppl_resa DROP reservation_id');
    }
}
