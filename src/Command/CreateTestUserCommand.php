<?php

namespace App\Command;

use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-user',
    description: 'Creates a test patient user'
)]
class CreateTestUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Check if user already exists
        $email = 'ademjazi472@gmail.com';
        $password = 'password123';

        $existing = $this->entityManager->getRepository(Patient::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $output->writeln("User $email already exists.");
            // Update password just in case
            $existing->setPassword(
                $this->passwordHasher->hashPassword($existing, $password)
            );
            $this->entityManager->flush();
            $output->writeln("Updated password for $email to $password");
            return Command::SUCCESS;
        }

        $patient = new Patient();
        $patient->setEmail($email);
        $patient->setFirstName('Adem');
        $patient->setLastName('Jazi');
        $patient->setPassword(
            $this->passwordHasher->hashPassword($patient, $password)
        );
        $patient->setIsActive(true);
        $patient->setRoles(['ROLE_PATIENT']);

        $this->entityManager->persist($patient);
        $this->entityManager->flush();

        $output->writeln("Created user: $email / $password");

        return Command::SUCCESS;
    }
}
