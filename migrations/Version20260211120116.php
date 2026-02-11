<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211120116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, resume LONGTEXT NOT NULL, auteur VARCHAR(255) NOT NULL, date_pub DATE NOT NULL, statut VARCHAR(50) NOT NULL, magazine_id INT DEFAULT NULL, INDEX IDX_23A0E663EB84A1D (magazine_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE magazine (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, date_create DATE NOT NULL, statut VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E663EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E663EB84A1D');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE magazine');
    }
}
