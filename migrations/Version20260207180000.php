<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add rendez_vous relationship to feedback table
 */
final class Version20260207180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add rendez_vous_id to feedback table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback ADD rendez_vous_id INT NULL');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458BE94A0E6 FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('CREATE INDEX IDX_D2294458BE94A0E6 ON feedback (rendez_vous_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458BE94A0E6');
        $this->addSql('DROP INDEX IDX_D2294458BE94A0E6 ON feedback');
        $this->addSql('ALTER TABLE feedback DROP COLUMN rendez_vous_id');
    }
}
