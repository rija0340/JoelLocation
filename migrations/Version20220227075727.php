<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220227075727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conducteur DROP FOREIGN KEY FK_23677143B83297E7');
        $this->addSql('DROP INDEX IDX_23677143B83297E7 ON conducteur');
        $this->addSql('ALTER TABLE conducteur DROP reservation_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conducteur ADD reservation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conducteur ADD CONSTRAINT FK_23677143B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_23677143B83297E7 ON conducteur (reservation_id)');
    }
}
