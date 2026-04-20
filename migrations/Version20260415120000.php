<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create Tarifs V2 Matrix tables (without history table - using file logging instead)
 */
final class Version20260415120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create pricing_interval and tarifs_v2_cell tables';
    }

    public function up(Schema $schema): void
    {
        // pricing_interval table
        $this->addSql('CREATE TABLE pricing_interval (
            id INT AUTO_INCREMENT NOT NULL,
            min_days INT NOT NULL,
            max_days INT DEFAULT NULL,
            label VARCHAR(50) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // tarifs_v2_cell table
        $this->addSql('CREATE TABLE tarifs_v2_cell (
            id INT AUTO_INCREMENT NOT NULL,
            marque_id INT NOT NULL,
            modele_id INT NOT NULL,
            month VARCHAR(20) NOT NULL,
            pricing_interval_id INT NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            UNIQUE INDEX unique_cell (marque_id, modele_id, month, pricing_interval_id),
            INDEX IDX_CELL_MARQUE (marque_id),
            INDEX IDX_CELL_MODELE (modele_id),
            INDEX IDX_CELL_INTERVAL (pricing_interval_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign keys
        $this->addSql('ALTER TABLE tarifs_v2_cell 
            ADD CONSTRAINT FK_CELL_MARQUE FOREIGN KEY (marque_id) REFERENCES marque (id),
            ADD CONSTRAINT FK_CELL_MODELE FOREIGN KEY (modele_id) REFERENCES modele (id),
            ADD CONSTRAINT FK_CELL_INTERVAL FOREIGN KEY (pricing_interval_id) REFERENCES pricing_interval (id)');

        // Insert default intervals
        $this->addSql("INSERT INTO pricing_interval (min_days, max_days, label, sort_order, created_at) VALUES
            (1, 2, '1-2 jours', 1, NOW()),
            (3, 6, '3-6 jours', 2, NOW()),
            (7, 14, '7-14 jours', 3, NOW()),
            (15, 30, '15-30 jours', 4, NOW()),
            (31, NULL, '31+ jours', 5, NOW())");
    }

    public function down(Schema $schema): void
    {
        // Drop foreign keys first
        $this->addSql('ALTER TABLE tarifs_v2_cell DROP FOREIGN KEY FK_CELL_MARQUE');
        $this->addSql('ALTER TABLE tarifs_v2_cell DROP FOREIGN KEY FK_CELL_MODELE');
        $this->addSql('ALTER TABLE tarifs_v2_cell DROP FOREIGN KEY FK_CELL_INTERVAL');

        // Drop tables
        $this->addSql('DROP TABLE tarifs_v2_cell');
        $this->addSql('DROP TABLE pricing_interval');
    }
}
