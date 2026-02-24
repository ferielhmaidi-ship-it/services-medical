<?php

require __DIR__ . '/vendor/autoload.php';

if (!class_exists('App\Kernel')) {
    require __DIR__ . '/src/Kernel.php';
}

use App\Entity\Medecin;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get('security.user_password_hasher');

$email = 'doctor@example.com';
$password = 'password123';

$existing = $entityManager->getRepository(Medecin::class)->findOneBy(['email' => $email]);
if ($existing) {
    echo "Medecin $email already exists. Updating.\n";
    $medecin = $existing;
} else {
    echo "Creating new Medecin $email.\n";
    $medecin = new Medecin();
    $medecin->setEmail($email);
    $medecin->setFirstName('Admin');
    $medecin->setLastName('Doctor');
}

$medecin->setPassword($passwordHasher->hashPassword($medecin, $password));
$medecin->setIsActive(true);
$medecin->setIsVerified(true);
$medecin->setRoles(['ROLE_MEDECIN']);
$medecin->setSpecialty('Generaliste');
$medecin->setCin('12345678'); // Assuming 8 digit CIN based on entity

$entityManager->persist($medecin);
$entityManager->flush();

echo "Medecin created/updated successfully.\n";
echo "Email: $email\n";
echo "Password: $password\n";
echo "Role: ROLE_MEDECIN\n";
