<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240414072455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE transport_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE discord_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE transport (id INT NOT NULL, event_id INT NOT NULL, seats INT NOT NULL, direction VARCHAR(255) NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, postal_code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_66AB212E71F7E88B ON transport (event_id)');
        $this->addSql('COMMENT ON COLUMN transport.start_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transport.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE traveler (transport_id INT NOT NULL, user_id INT NOT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(transport_id, user_id))');
        $this->addSql('CREATE INDEX IDX_6841F2169909C13F ON traveler (transport_id)');
        $this->addSql('CREATE INDEX IDX_6841F216A76ED395 ON traveler (user_id)');
        $this->addSql('COMMENT ON COLUMN traveler.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE discord_user (id INT NOT NULL, user_id BIGINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX user_idx ON discord_user (user_id)');
        $this->addSql('COMMENT ON COLUMN discord_user.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE transport ADD CONSTRAINT FK_66AB212E71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE traveler ADD CONSTRAINT FK_6841F2169909C13F FOREIGN KEY (transport_id) REFERENCES transport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE traveler ADD CONSTRAINT FK_6841F216A76ED395 FOREIGN KEY (user_id) REFERENCES discord_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE transport_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE discord_user_id_seq CASCADE');
        $this->addSql('ALTER TABLE transport DROP CONSTRAINT FK_66AB212E71F7E88B');
        $this->addSql('ALTER TABLE traveler DROP CONSTRAINT FK_6841F2169909C13F');
        $this->addSql('ALTER TABLE traveler DROP CONSTRAINT FK_6841F216A76ED395');
        $this->addSql('DROP TABLE transport');
        $this->addSql('DROP TABLE traveler');
        $this->addSql('DROP TABLE discord_user');
    }
}
