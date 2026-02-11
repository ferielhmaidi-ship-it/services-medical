<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210195420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE indisponibilite (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, doctor_id INT NOT NULL, is_emergency TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE pause (id INT AUTO_INCREMENT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, temps_travail_id INT NOT NULL, UNIQUE INDEX UNIQ_D79A92EDA260A06C (temps_travail_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE temps_travail (id INT AUTO_INCREMENT NOT NULL, day_of_week VARCHAR(20) NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, doctor_id INT NOT NULL, specific_date DATE DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE pause ADD CONSTRAINT FK_D79A92EDA260A06C FOREIGN KEY (temps_travail_id) REFERENCES temps_travail (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pause DROP FOREIGN KEY FK_D79A92EDA260A06C');
        $this->addSql('DROP TABLE indisponibilite');
        $this->addSql('DROP TABLE pause');
        $this->addSql('DROP TABLE temps_travail');
    }
}
