<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241215105100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devis_option (id INT AUTO_INCREMENT NOT NULL, devis_id INT DEFAULT NULL, opt_id INT DEFAULT NULL, quantity INT NOT NULL, INDEX IDX_6693C77C41DEFADA (devis_id), INDEX IDX_6693C77CCCEFD70A (opt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE devis_option ADD CONSTRAINT FK_6693C77C41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
        $this->addSql('ALTER TABLE devis_option ADD CONSTRAINT FK_6693C77CCCEFD70A FOREIGN KEY (opt_id) REFERENCES options (id)');
        $this->addSql('ALTER TABLE devis CHANGE lieu_sejour lieu_sejour VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE devis_option');
        $this->addSql('ALTER TABLE devis CHANGE lieu_sejour lieu_sejour VARCHAR(255) NOT NULL');
    }
}
