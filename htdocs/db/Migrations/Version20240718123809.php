<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718123809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE herbaria (id SERIAL NOT NULL, acronym VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40DF22BA512D8851 ON herbaria (acronym)');
        $this->addSql('COMMENT ON TABLE herbaria IS \'List of involved herbaria\'');
        $this->addSql('COMMENT ON COLUMN herbaria.acronym IS \'Acronym of herbarium according to Index Herbariorum\'');
        $this->addSql('CREATE TABLE photos (id SERIAL NOT NULL, herbarium_id INT DEFAULT NULL, archive_filename VARCHAR(255) NOT NULL, jp2filename VARCHAR(255) NOT NULL, specimen_id VARCHAR(255) DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, archive_file_size BIGINT DEFAULT NULL, jp2file_size BIGINT DEFAULT NULL, finalized BOOLEAN NOT NULL, message text DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_876E0D911642609 ON photos (archive_filename)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_876E0D9765B2490 ON photos (jp2filename)');
        $this->addSql('CREATE INDEX IDX_876E0D9DD127992 ON photos (herbarium_id)');
        $this->addSql('COMMENT ON TABLE photos IS \'Specimen photos\'');
        $this->addSql('COMMENT ON COLUMN photos.herbarium_id IS \'Herbarium storing and managing the specimen data\'');
        $this->addSql('COMMENT ON COLUMN photos.archive_filename IS \'Filename of Archive Master TIF file\'');
        $this->addSql('COMMENT ON COLUMN photos.jp2filename IS \'Filename of JP2 file\'');
        $this->addSql('COMMENT ON COLUMN photos.specimen_id IS \'Herbarium internal unique id of specimen in form without herbarium acronym\'');
        $this->addSql('COMMENT ON COLUMN photos.width IS \'Width of image with pixels\'');
        $this->addSql('COMMENT ON COLUMN photos.height IS \'Height of image in pixels\'');
        $this->addSql('COMMENT ON COLUMN photos.archive_file_size IS \'Filesize of Archive Master TIFF file in bytes\'');
        $this->addSql('COMMENT ON COLUMN photos.jp2file_size IS \'Filesize of converted JP2 file in bytes\'');
        $this->addSql('COMMENT ON COLUMN photos.finalized IS \'Flag with not finally usage decided yet\'');
        $this->addSql('COMMENT ON COLUMN photos.message IS \'Result of migration\'');
        $this->addSql('COMMENT ON COLUMN photos.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE photos ADD CONSTRAINT FK_876E0D9DD127992 FOREIGN KEY (herbarium_id) REFERENCES herbaria (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql("INSERT INTO herbaria VALUES (DEFAULT, 'PRC')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos DROP CONSTRAINT FK_876E0D9DD127992');
        $this->addSql('DROP TABLE herbaria');
        $this->addSql('DROP TABLE photos');
    }
}
