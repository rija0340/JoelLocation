<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210810050901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C4964A4A3511');
        $this->addSql('DROP INDEX IDX_F9B8C4964A4A3511 ON tarifs');
        $this->addSql('ALTER TABLE tarifs ADD marque_id INT DEFAULT NULL, ADD modele_id INT DEFAULT NULL, DROP vehicule_id');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C4964827B9B2 FOREIGN KEY (marque_id) REFERENCES marque (id)');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C496AC14B70A FOREIGN KEY (modele_id) REFERENCES modele (id)');
        $this->addSql('CREATE INDEX IDX_F9B8C4964827B9B2 ON tarifs (marque_id)');
        $this->addSql('CREATE INDEX IDX_F9B8C496AC14B70A ON tarifs (modele_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C4964827B9B2');
        $this->addSql('ALTER TABLE tarifs DROP FOREIGN KEY FK_F9B8C496AC14B70A');
        $this->addSql('DROP INDEX IDX_F9B8C4964827B9B2 ON tarifs');
        $this->addSql('DROP INDEX IDX_F9B8C496AC14B70A ON tarifs');
        $this->addSql('ALTER TABLE tarifs ADD vehicule_id INT NOT NULL, DROP marque_id, DROP modele_id');
        $this->addSql('ALTER TABLE tarifs ADD CONSTRAINT FK_F9B8C4964A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('CREATE INDEX IDX_F9B8C4964A4A3511 ON tarifs (vehicule_id)');
    }
}
