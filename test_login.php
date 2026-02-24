<?php

require __DIR__ . '/vendor/autoload.php';

// Manually require Kernel if autoloader misses it for some reason
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

// Change this email to the one you are testing with
$email = 'patient@example.com'; 

$user = $entityManager->getRepository(Patient::class)->findOneBy(['email' => $email]);

if (!$user) {
    echo "User found with email: $email? NO\n";
    // Try to find ANY patient to see what's in the DB
    $patients = $entityManager->getRepository(Patient::class)->findAll();
    echo "Total patients in DB: " . count($patients) . "\n";
    if (count($patients) > 0) {
        foreach ($patients as $p) {
            echo " - Found patient: " . $p->getEmail() . "\n";
        }
    }
} else {
    echo "User found with email: $email? YES\n";
    echo "Password hash: " . $user->getPassword() . "\n";
    
    // Test a known password if you want, or just verify the hash format
    // $isValid = $passwordHasher->isPasswordValid($user, 'your_password');
    // echo "Password 'your_password' valid? " . ($isValid ? 'YES' : 'NO') . "\n";
}
