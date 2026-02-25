<?php

require __DIR__ . '/vendor/autoload.php';

// Manually require Kernel if autoloader misses it for some reason
if (!class_exists('App\Kernel')) {
    require __DIR__ . '/src/Kernel.php';
}

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Admin;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();

$email = 'ademjazi472@gmail.com';

echo "Checking for user: $email\n";

$patient = $entityManager->getRepository(Patient::class)->findOneBy(['email' => $email]);
if ($patient) {
    echo "Found PATIENT: " . $patient->getEmail() . "\n";
    echo "Roles: " . implode(', ', $patient->getRoles()) . "\n";
} else {
    echo "Not found in Patients.\n";
}

$medecin = $entityManager->getRepository(Medecin::class)->findOneBy(['email' => $email]);
if ($medecin) {
    echo "Found MEDECIN: " . $medecin->getEmail() . "\n";
} else {
    echo "Not found in Medecins.\n";
}

$admin = $entityManager->getRepository(Admin::class)->findOneBy(['email' => $email]);
if ($admin) {
    echo "Found ADMIN: " . $admin->getEmail() . "\n";
} else {
    echo "Not found in Admins.\n";
}
