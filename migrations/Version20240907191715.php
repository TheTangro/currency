<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240907191715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE config (id INT NOT NULL, path VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP INDEX currency_rate_rate_index');
        $this->addSql('DROP INDEX currency_rate_created_at_index');
        $this->addSql('DROP INDEX currency_rate_currency_from_currency_to_created_at_index');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE config');
        $this->addSql('CREATE INDEX currency_rate_rate_index ON currency_rate (rate)');
        $this->addSql('CREATE INDEX currency_rate_created_at_index ON currency_rate (created_at)');
        $this->addSql('CREATE INDEX currency_rate_currency_from_currency_to_created_at_index ON currency_rate (currency_from, currency_to, created_at)');
    }
}
