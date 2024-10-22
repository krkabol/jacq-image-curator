<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241022111943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA front');
        $this->addSql('CREATE TABLE front.contact (id SERIAL NOT NULL, herbarium_id INT NOT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F5ACD1ACDD127992 ON front.contact (herbarium_id)');
        $this->addSql('COMMENT ON TABLE front.contact IS \'People from herbaria, not necessary connected to repository users\'');
        $this->addSql('ALTER TABLE front.contact ADD CONSTRAINT FK_F5ACD1ACDD127992 FOREIGN KEY (herbarium_id) REFERENCES herbaria (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE herbaria ADD fullname TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE herbaria ADD address TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN herbaria.fullname IS \'full name of the herbarium\'');
        $this->addSql('COMMENT ON COLUMN herbaria.address IS \'address of the institution/herbarium\'');
        $this->addSql("INSERT INTO front.contact VALUES (DEFAULT, 1, 'Patrik', 'Mráz', 'head of herbarium', 'mrazpat@natur.cuni.cz')");
        $this->addSql("INSERT INTO front.contact VALUES (DEFAULT, 1, 'Zdeněk', 'Vaněček', 'specimen digitalization', 'vanecekz@natur.cuni.cz ')");
        $this->addSql("INSERT INTO front.contact VALUES (DEFAULT, 1, 'Petr', 'Novotný', 'IT development', 'novotp@natur.cuni.cz')");
        $this->addSql("UPDATE herbaria SET fullname = 'PřF UK Praha', address = 'Benátská 2, Prague' WHERE id = 1");

    }

    public function down(Schema $schema): void
    {
       $this->addSql('ALTER TABLE front.contact DROP CONSTRAINT FK_F5ACD1ACDD127992');
        $this->addSql('DROP TABLE front.contact');
        $this->addSql('ALTER TABLE herbaria DROP fullname');
        $this->addSql('ALTER TABLE herbaria DROP address');
        }
}
