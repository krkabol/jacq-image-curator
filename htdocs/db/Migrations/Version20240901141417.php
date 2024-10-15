<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20240901141417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce users and roles';
    }

    public function up(Schema $schema): void
    {
         $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, herbarium_id INT NOT NULL, role_id INT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, comment TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, lastedit_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        $this->addSql('CREATE INDEX IDX_1483A5E9DD127992 ON users (herbarium_id)');
        $this->addSql('CREATE INDEX IDX_1483A5E9D60322AC ON users (role_id)');
        $this->addSql('COMMENT ON TABLE users IS \'Repository users\'');
        $this->addSql('COMMENT ON COLUMN users.herbarium_id IS \'Herbarium\'');
        $this->addSql('COMMENT ON COLUMN users.role_id IS \'Role for ACL\'');
        $this->addSql('COMMENT ON COLUMN users.email IS \'User email address\'');
        $this->addSql('COMMENT ON COLUMN users.active IS \'Option to disable access for a specific user\'');
        $this->addSql('COMMENT ON COLUMN users.comment IS \'additional information about user\'');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE usersrole (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29D904A65E237E06 ON usersrole (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29D904A66DE44026 ON usersrole (description)');
        $this->addSql('COMMENT ON TABLE usersrole IS \'List of available roles for users\'');
        $this->addSql('COMMENT ON COLUMN usersrole.name IS \'name of the role\'');
        $this->addSql('COMMENT ON COLUMN usersrole.description IS \'short description\'');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9DD127992 FOREIGN KEY (herbarium_id) REFERENCES herbaria (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES usersrole (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE herbaria ADD bucket VARCHAR(255)');
        $this->addSql('COMMENT ON COLUMN herbaria.bucket IS \'S3 bucket where are stored new images before imported to the repository\'');
        $this->addSql("UPDATE herbaria SET bucket = 'herbarium-prc' WHERE acronym = 'PRC'");
        $this->addSql('ALTER TABLE herbaria ALTER COLUMN bucket SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40DF22BAE73F36A6 ON herbaria (bucket)');
        $this->addSql("INSERT INTO usersrole VALUES (DEFAULT, 'superadmin', 'all privileges')");
        $this->addSql("INSERT INTO usersrole VALUES (DEFAULT, 'admin', 'curator privileges over all herbaria')");
        $this->addSql("INSERT INTO usersrole VALUES (DEFAULT, 'curator', 'manage photos in single herbarium')");
        $this->addSql("INSERT INTO usersrole VALUES (DEFAULT, 'guest', 'read only access to single herbarium')");
        $this->addSql('INSERT INTO users VALUES (DEFAULT, 1, 1, \'admin\', \'$2y$10$iMv7YJoqFRGCrpWzVX/84e1NZcoeKbKMx1FIG9HhEcrBVHwglYeP2\', \'Petr\', \'Novotný\', \'novotp@natur.cuni.cz\', true, NULL, now(), now())');
        $this->addSql('INSERT INTO users VALUES (DEFAULT, 1, 3, \'curator_prc_1\', \'$2y$10$iMv7YJoqFRGCrpWzVX/84e1NZcoeKbKMx1FIG9HhEcrBVHwglYeP2\', \'Zdeněk\', \'Vaněček\', \'vanecekz@natur.cuni.cz\', true, NULL, now(), now())');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9DD127992');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9D60322AC');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE usersrole');
        $this->addSql('DROP INDEX UNIQ_40DF22BAE73F36A6');
        $this->addSql('ALTER TABLE herbaria DROP bucket');
    }
}
