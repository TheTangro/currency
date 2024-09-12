<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240911232500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification_channel (id INT NOT NULL, notification_request_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, payload_serialized TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B7E704F0B90C1D87 ON notification_channel (notification_request_id)');
        $this->addSql('CREATE TABLE notification_request (id INT NOT NULL, notification_serialized TEXT NOT NULL, is_finished BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE notification_channel ADD CONSTRAINT FK_B7E704F0B90C1D87 FOREIGN KEY (notification_request_id) REFERENCES notification_request (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE poison_pill ALTER pill SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE notification_channel DROP CONSTRAINT FK_B7E704F0B90C1D87');
        $this->addSql('DROP TABLE notification_channel');
        $this->addSql('DROP TABLE notification_request');
        $this->addSql('ALTER TABLE poison_pill ALTER pill DROP NOT NULL');
    }
}
