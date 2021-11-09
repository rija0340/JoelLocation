<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211109124536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491DF27D6E');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B62F3B9C');
        $this->addSql('DROP INDEX UNIQ_8D93D6491DF27D6E ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649B62F3B9C ON user');
        $this->addSql('ALTER TABLE user DROP infos_resa_id, DROP infos_vol_resa_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD infos_resa_id INT DEFAULT NULL, ADD infos_vol_resa_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491DF27D6E FOREIGN KEY (infos_resa_id) REFERENCES infos_resa (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B62F3B9C FOREIGN KEY (infos_vol_resa_id) REFERENCES infos_vol_resa (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6491DF27D6E ON user (infos_resa_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B62F3B9C ON user (infos_vol_resa_id)');
    }
}
