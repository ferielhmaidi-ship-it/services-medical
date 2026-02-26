<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add sentiment_score column to feedback table
 */
final class Version20260210230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sentiment_score column to feedback table for AI sentiment analysis';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback ADD sentiment_score DOUBLE PRECISION NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback DROP COLUMN sentiment_score');
    }
}
