<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240916131226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos ADD thumbnail BYTEA DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN photos.thumbnail IS \'Thumbnail during import phase\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos DROP thumbnail');
    }
}
