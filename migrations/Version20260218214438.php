<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218214438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Switch rapport and ordonnances relation from rendez_vous_id to appointment_id';
    }

    public function up(Schema $schema): void
    {
        $this->switchRelationColumn('rapport', 'rendez_vous_id', 'appointment_id');
        $this->switchRelationColumn('ordonnances', 'rendez_vous_id', 'appointment_id');
    }

    public function down(Schema $schema): void
    {
        $this->switchRelationColumn('rapport', 'appointment_id', 'rendez_vous_id', 'rendez_vous');
        $this->switchRelationColumn('ordonnances', 'appointment_id', 'rendez_vous_id', 'rendez_vous');
    }

    private function switchRelationColumn(
        string $table,
        string $oldColumn,
        string $newColumn,
        string $targetTable = 'appointment'
    ): void {
        if (!$this->tableExists($table)) {
            return;
        }

        if (!$this->columnExists($table, $oldColumn) && !$this->columnExists($table, $newColumn)) {
            return;
        }

        if ($this->columnExists($table, $oldColumn)) {
            $this->dropForeignKeysForColumn($table, $oldColumn);
            $this->dropIndexesForColumn($table, $oldColumn);

            if (!$this->columnExists($table, $newColumn)) {
                $this->addSql(sprintf(
                    'ALTER TABLE `%s` CHANGE `%s` `%s` INT NOT NULL',
                    $table,
                    $oldColumn,
                    $newColumn
                ));
            }
        }

        $this->dropForeignKeysForColumn($table, $newColumn);
        $this->dropIndexesForColumn($table, $newColumn);

        $indexName = sprintf('IDX_%s_%s', strtoupper(substr(md5($table), 0, 8)), strtoupper(substr(md5($newColumn), 0, 8)));
        $this->addSql(sprintf('CREATE INDEX `%s` ON `%s` (`%s`)', $indexName, $table, $newColumn));
        // Intentionally do not add the FK here: legacy data may still reference
        // IDs not yet present in `appointment`/`rendez_vous`.
    }

    private function tableExists(string $table): bool
    {
        $sql = <<<'SQL'
SELECT 1
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
LIMIT 1
SQL;

        return (bool) $this->connection->fetchOne($sql, ['table' => $table]);
    }

    private function columnExists(string $table, string $column): bool
    {
        $sql = <<<'SQL'
SELECT 1
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column
LIMIT 1
SQL;

        return (bool) $this->connection->fetchOne($sql, ['table' => $table, 'column' => $column]);
    }

    private function dropForeignKeysForColumn(string $table, string $column): void
    {
        $sql = <<<'SQL'
SELECT CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = :table
  AND COLUMN_NAME = :column
  AND REFERENCED_TABLE_NAME IS NOT NULL
SQL;

        $constraints = $this->connection->fetchFirstColumn($sql, ['table' => $table, 'column' => $column]);
        foreach ($constraints as $constraint) {
            $this->addSql(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraint));
        }
    }

    private function dropIndexesForColumn(string $table, string $column): void
    {
        $sql = <<<'SQL'
SELECT DISTINCT INDEX_NAME
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = :table
  AND COLUMN_NAME = :column
  AND INDEX_NAME <> 'PRIMARY'
SQL;

        $indexes = $this->connection->fetchFirstColumn($sql, ['table' => $table, 'column' => $column]);
        foreach ($indexes as $index) {
            $this->addSql(sprintf('DROP INDEX `%s` ON `%s`', $index, $table));
        }
    }
}
