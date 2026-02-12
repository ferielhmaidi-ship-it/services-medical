<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210210851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sentiment_score to feedback and ai_average_score to medecin for AI sentiment analysis';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback ADD sentiment_score DOUBLE PRECISION NULL');
        $this->addSql('ALTER TABLE medecin ADD ai_average_score DOUBLE PRECISION NULL');
        $this->addSql('ALTER TABLE medecin ADD ai_score_updated_at DATETIME NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback DROP COLUMN sentiment_score');
        $this->addSql('ALTER TABLE medecin DROP COLUMN ai_score_updated_at');
        $this->addSql('ALTER TABLE medecin DROP COLUMN ai_average_score');
    }
}
