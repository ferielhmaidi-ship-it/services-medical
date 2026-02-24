<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224202938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE patient_notification');
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY `FK_D2294458E5B533F9`');
        $this->addSql('DROP INDEX IDX_D22944585B2A183F ON feedback');
        $this->addSql('ALTER TABLE feedback ADD sentiment_label VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE ordonnances ADD CONSTRAINT FK_5F98A94AE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id)');
        $this->addSql('ALTER TABLE ordonnances RENAME INDEX idx_a813c365_54c91d3b TO IDX_5F98A94AE5B533F9');
        $this->addSql('ALTER TABLE question DROP status, DROP image_name, DROP updated_at');
        $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_BE34A09CE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id)');
        $this->addSql('ALTER TABLE rapport RENAME INDEX idx_9b93a196_54c91d3b TO IDX_BE34A09CE5B533F9');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE patient_notification (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, message VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, target_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_936E553DB611F9D2 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE feedback DROP sentiment_label');
        $this->addSql('CREATE INDEX IDX_D22944585B2A183F ON feedback (appointment_id)');
        $this->addSql('ALTER TABLE ordonnances DROP FOREIGN KEY FK_5F98A94AE5B533F9');
        $this->addSql('ALTER TABLE ordonnances RENAME INDEX idx_5f98a94ae5b533f9 TO IDX_A813C365_54C91D3B');
        $this->addSql('ALTER TABLE question ADD status VARCHAR(32) DEFAULT \'draft\' NOT NULL, ADD image_name VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE rapport DROP FOREIGN KEY FK_BE34A09CE5B533F9');
        $this->addSql('ALTER TABLE rapport RENAME INDEX idx_be34a09ce5b533f9 TO IDX_9B93A196_54C91D3B');
    }
}
