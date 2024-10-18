<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241017052728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria ADD logo TEXT DEFAULT NULL');
        $this->addSql('UPDATE herbaria SET logo = \'https://cas.cuni.cz/cas/images/UK-logo.png\' WHERE acronym = \'PRC\'');
        $this->addSql('COMMENT ON COLUMN herbaria.logo IS \'logo URL\'');
        $this->addSql('ALTER TABLE photos ADD exif JSONB DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN photos.exif IS \'raw EXIF data extracted from Archive Master file\'');
        $this->addSql('ALTER TABLE photos ADD identify JSONB DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN photos.exif IS \'Imagick -verbose identify metadata output from the Archive Master file\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos DROP exif');
        $this->addSql('ALTER TABLE photos DROP identify');
        $this->addSql('ALTER TABLE herbaria DROP logo');
    }
}
