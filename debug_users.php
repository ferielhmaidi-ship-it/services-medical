<?php

require __DIR__ . '/vendor/autoload.php';

if (!class_exists('App\Kernel')) {
    require __DIR__ . '/src/Kernel.php';
}

use App\Entity\Admin;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();

echo "Checking Admins:\n";
$admins = $entityManager->getRepository(Admin::class)->findAll();
foreach ($admins as $a) echo " - " . $a->getEmail() . "\n";

echo "Checking Medecins:\n";
$medecins = $entityManager->getRepository(Medecin::class)->findAll();
foreach ($medecins as $m) echo " - " . $m->getEmail() . "\n";

echo "Checking Patients:\n";
$patients = $entityManager->getRepository(Patient::class)->findAll();
foreach ($patients as $p) echo " - " . $p->getEmail() . "\n";
