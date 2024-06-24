<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240623134655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photos ADD jp2filename VARCHAR(255) NOT NULL COMMENT \'Filename of JP2 file\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_876E0D9765B2490 ON photos (jp2filename)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_876E0D9765B2490 ON photos');
        $this->addSql('ALTER TABLE photos DROP jp2filename');
    }
}
