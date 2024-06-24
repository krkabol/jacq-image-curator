<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240621125754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'initial db schema';    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE herbaria (id INT AUTO_INCREMENT NOT NULL, acronym VARCHAR(255) NOT NULL COMMENT \'Acronym of herbarium according to Index Herbariorum\', UNIQUE INDEX UNIQ_40DF22BA512D8851 (acronym), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'List of involved herbaria\' ');
        $this->addSql('CREATE TABLE photos (id INT AUTO_INCREMENT NOT NULL, herbarium_id INT DEFAULT NULL COMMENT \'Herbarium storing and managing the specimen data\', archive_filename VARCHAR(255) NOT NULL COMMENT \'Filename of Archive Master TIF file\', specimen_id VARCHAR(255) DEFAULT NULL COMMENT \'Herbarium internal unique id of specimen in form without herbarium acronym\', width INT DEFAULT NULL COMMENT \'Width of image with pixels\', height INT DEFAULT NULL COMMENT \'Height of image in pixels\', archive_file_size BIGINT DEFAULT NULL COMMENT \'Filesize of Archive Master TIFF file in bytes\', jp2file_size BIGINT DEFAULT NULL COMMENT \'Filesize of converted JP2 file in bytes\', finalized TINYINT(1) NOT NULL COMMENT \'Flag with not finally usage decided yet\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_876E0D911642609 (archive_filename), INDEX IDX_876E0D9DD127992 (herbarium_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'Specimen photos\' ');
        $this->addSql('ALTER TABLE photos ADD CONSTRAINT FK_876E0D9DD127992 FOREIGN KEY (herbarium_id) REFERENCES herbaria (id)');

        $this->addSql("INSERT INTO herbaria VALUES (DEFAULT, 'PRC')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos DROP FOREIGN KEY FK_876E0D9DD127992');
        $this->addSql('DROP TABLE herbaria');
        $this->addSql('DROP TABLE photos');
    }
}
