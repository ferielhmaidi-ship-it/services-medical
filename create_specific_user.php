<?php

require __DIR__ . '/vendor/autoload.php';

if (!class_exists('App\Kernel')) {
    require __DIR__ . '/src/Kernel.php';
}

use App\Entity\Patient;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get('security.user_password_hasher');

$email = 'ademjazi472@gmail.com';
$password = 'password123';

$existing = $entityManager->getRepository(Patient::class)->findOneBy(['email' => $email]);
if ($existing) {
    echo "User $email already exists. Updating password.\n";
    $patient = $existing;
} else {
    echo "Creating new user $email.\n";
    $patient = new Patient();
    $patient->setEmail($email);
    $patient->setFirstName('Adem');
    $patient->setLastName('Jazi');
}

$patient->setPassword($passwordHasher->hashPassword($patient, $password));
$patient->setIsActive(true);
$patient->setRoles(['ROLE_PATIENT']);

$entityManager->persist($patient);
$entityManager->flush();

echo "User created/updated successfully.\n";
echo "Email: $email\n";
echo "Password: $password\n";
