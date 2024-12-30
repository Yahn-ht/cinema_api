<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241225220700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE place_session (place_id INT NOT NULL, session_id INT NOT NULL, INDEX IDX_E488C22FDA6A219 (place_id), INDEX IDX_E488C22F613FECDF (session_id), PRIMARY KEY(place_id, session_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE place_session ADD CONSTRAINT FK_E488C22FDA6A219 FOREIGN KEY (place_id) REFERENCES place (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE place_session ADD CONSTRAINT FK_E488C22F613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE place DROP is_reserve');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE place_session DROP FOREIGN KEY FK_E488C22FDA6A219');
        $this->addSql('ALTER TABLE place_session DROP FOREIGN KEY FK_E488C22F613FECDF');
        $this->addSql('DROP TABLE place_session');
        $this->addSql('ALTER TABLE place ADD is_reserve TINYINT(1) NOT NULL');
    }
}
