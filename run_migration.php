<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine')->getManager();
$connection = $em->getConnection();

$sql = file_get_contents(__DIR__.'/manual_schema_update.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            $connection->executeStatement($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (\Throwable $e) {
            echo "Error executing: " . substr($statement, 0, 50) . "...\n";
            echo "Message: " . $e->getMessage() . "\n";
        }
    }
}

echo "Migration script completed.\n";
