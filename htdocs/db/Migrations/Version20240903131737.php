<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240903131737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'introduce photo statuses';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE photos_status (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_190AE20B5E237E06 ON photos_status (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_190AE20B6DE44026 ON photos_status (description)');
        $this->addSql('COMMENT ON TABLE photos_status IS \'List of allowed photo statuses\'');
        $this->addSql('COMMENT ON COLUMN photos_status.name IS \'name of the status\'');
        $this->addSql('COMMENT ON COLUMN photos_status.description IS \'short description\'');
        $this->addSql("INSERT INTO photos_status VALUES (DEFAULT, 'waiting before control', 'Photo to be copied from the users bucket, do not delete manually!')");
        $this->addSql("INSERT INTO photos_status VALUES (DEFAULT, 'control error', 'Entry control did not passed, it is not possible to include this photo in the repository')");
        $this->addSql("INSERT INTO photos_status VALUES (DEFAULT, 'control ok', 'Entry control passed well, it is time to include it')");
        $this->addSql("INSERT INTO photos_status VALUES (DEFAULT, 'published', 'Photo is stored in the repository - final status for most photos')");
        $this->addSql("INSERT INTO photos_status VALUES (DEFAULT, 'hidden', 'Photo is stored in the repository (=published) but public should not see it - contains error or is not devoted for public')");

        $this->addSql('ALTER TABLE photos ADD status_id INT');
        $this->addSql("UPDATE photos SET status_id = '4' WHERE finalized = TRUE");
        $this->addSql("UPDATE photos SET status_id = '5' WHERE finalized = FALSE");
        $this->addSql("ALTER TABLE photos DROP COLUMN finalized");
        $this->addSql('COMMENT ON COLUMN photos.status_id IS \'Status of the photo\'');
        $this->addSql('ALTER TABLE photos ADD CONSTRAINT FK_876E0D96BF700BD FOREIGN KEY (status_id) REFERENCES photos_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_876E0D96BF700BD ON photos (status_id)');

        $this->addSql('ALTER TABLE photos ALTER COLUMN status_id SET NOT NULL');
        $this->addSql('ALTER TABLE IF EXISTS public.photos ALTER COLUMN archive_filename DROP NOT NULL;');
        $this->addSql('ALTER TABLE IF EXISTS public.photos ALTER COLUMN jp2filename DROP NOT NULL;');
        $this->addSql('ALTER TABLE photos ADD original_file_timestamp TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE photos ADD lastedit_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL default now()');
        $this->addSql('ALTER TABLE photos ADD original_filename VARCHAR(255) DEFAULT NULL');

        $this->addSql('COMMENT ON COLUMN photos.original_filename IS \'Filename that was provided during curator upload, could make sense or completely missing semantical content\'');
        $this->addSql('ALTER TABLE photos ALTER lastedit_timestamp SET DEFAULT \'now()\'');


    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos DROP CONSTRAINT FK_876E0D96BF700BD');
        $this->addSql('DROP TABLE photos_status');
        $this->addSql('DROP INDEX IDX_876E0D96BF700BD');
        $this->addSql('ALTER TABLE photos DROP status_id');
        $this->addSql('ALTER TABLE photos DROP original_file_timestamp');
        $this->addSql('ALTER TABLE photos ADD finalized bool NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE photos DROP lastedit_timestamp');
        $this->addSql('ALTER TABLE photos DROP original_filename');


    }
}
