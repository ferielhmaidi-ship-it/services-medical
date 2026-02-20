<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260214123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add appointment_id relation to feedback table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback ADD appointment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D22944585B2A183F FOREIGN KEY (appointment_id) REFERENCES appointment (id)');
        $this->addSql('CREATE INDEX IDX_D22944585B2A183F ON feedback (appointment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D22944585B2A183F');
        $this->addSql('DROP INDEX IDX_D22944585B2A183F ON feedback');
        $this->addSql('ALTER TABLE feedback DROP appointment_id');
    }
}
