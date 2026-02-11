<?php
require __DIR__.'/vendor/autoload.php';
(new \Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__.'/.env');
$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();
$sm = $conn->createSchemaManager();
$exists = $sm->tablesExist(['question_likes_patients']);
echo $exists ? "Table exists.\n" : "Table does NOT exist.\n";
