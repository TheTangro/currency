<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240913201440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE notification_history_entry_id INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification_history_entry (id INT NOT NULL, notification_request_id INT DEFAULT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_242B5FDEB90C1D87 ON notification_history_entry (notification_request_id)');
        $this->addSql('COMMENT ON COLUMN notification_history_entry.sent_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE notification_history_entry ADD CONSTRAINT FK_242B5FDEB90C1D87 FOREIGN KEY (notification_request_id) REFERENCES notification_request (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE notification_history_entry_id CASCADE');
        $this->addSql('ALTER TABLE notification_history_entry DROP CONSTRAINT FK_242B5FDEB90C1D87');
        $this->addSql('DROP TABLE notification_history_entry');
    }
}
