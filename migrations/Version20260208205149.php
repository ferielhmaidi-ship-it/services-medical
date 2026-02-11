<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208205149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admins (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, age INT DEFAULT NULL, gender VARCHAR(10) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_A2E0150FE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE medecins (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, age INT DEFAULT NULL, gender VARCHAR(10) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, specialty VARCHAR(50) NOT NULL, cin VARCHAR(8) NOT NULL, address LONGTEXT DEFAULT NULL, education LONGTEXT DEFAULT NULL, experience LONGTEXT DEFAULT NULL, governorate VARCHAR(100) DEFAULT NULL, is_verified TINYINT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_691272DDE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patients (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, age INT DEFAULT NULL, gender VARCHAR(10) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, phone_number VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, date_of_birth DATE DEFAULT NULL, has_insurance TINYINT DEFAULT 0 NOT NULL, insurance_number VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_2CCC2E2CE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE admins');
        $this->addSql('DROP TABLE medecins');
        $this->addSql('DROP TABLE patients');
    }
}
