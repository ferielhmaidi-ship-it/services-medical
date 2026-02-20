<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add AI sentiment analysis columns to medecin table
 */
final class Version20260210225000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ai_average_score and ai_score_updated_at to medecin table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE medecin ADD ai_average_score DOUBLE PRECISION NULL');
        $this->addSql('ALTER TABLE medecin ADD ai_score_updated_at DATETIME NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE medecin DROP COLUMN ai_score_updated_at');
        $this->addSql('ALTER TABLE medecin DROP COLUMN ai_average_score');
    }
}
