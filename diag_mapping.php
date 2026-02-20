<?php
// diag_mapping.php
require 'vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
try {
    $metas = $em->getMetadataFactory()->getAllMetadata();
    foreach ($metas as $meta) {
        echo "OK: " . $meta->getName() . "\n";
    }
}
catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
