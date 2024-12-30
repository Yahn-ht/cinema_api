<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129013746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE snack_reservation (id INT AUTO_INCREMENT NOT NULL, snack_id INT NOT NULL, reservation_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_62E173A6F469C3E0 (snack_id), INDEX IDX_62E173A6B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE snack_reservation ADD CONSTRAINT FK_62E173A6F469C3E0 FOREIGN KEY (snack_id) REFERENCES snack (id)');
        $this->addSql('ALTER TABLE snack_reservation ADD CONSTRAINT FK_62E173A6B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE snack DROP FOREIGN KEY FK_7E0CCC91B83297E7');
        $this->addSql('DROP INDEX IDX_7E0CCC91B83297E7 ON snack');
        $this->addSql('ALTER TABLE snack DROP reservation_id, DROP quantity, CHANGE prix prix NUMERIC(10, 0) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE snack_reservation DROP FOREIGN KEY FK_62E173A6F469C3E0');
        $this->addSql('ALTER TABLE snack_reservation DROP FOREIGN KEY FK_62E173A6B83297E7');
        $this->addSql('DROP TABLE snack_reservation');
        $this->addSql('ALTER TABLE snack ADD reservation_id INT DEFAULT NULL, ADD quantity INT DEFAULT NULL, CHANGE prix prix NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE snack ADD CONSTRAINT FK_7E0CCC91B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7E0CCC91B83297E7 ON snack (reservation_id)');
    }
}
