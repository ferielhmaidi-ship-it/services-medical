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

$target = 'ademjazi472@gmail.com';

echo "Searching for $target ...\n";

$a = $entityManager->getRepository(Admin::class)->findOneBy(['email' => $target]);
if ($a) echo " - Found in Admins!\n";

$m = $entityManager->getRepository(Medecin::class)->findOneBy(['email' => $target]);
if ($m) echo " - Found in Medecins!\n";

$p = $entityManager->getRepository(Patient::class)->findOneBy(['email' => $target]);
if ($p) echo " - Found in Patients!\n";

if (!$a && !$m && !$p) echo " - Not found in any table.\n";
