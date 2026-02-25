<?php

namespace App\Command;

use App\Entity\Medecin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-medecin',
    description: 'Creates a new Medecin user.',
)]
class CreateMedecinCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = 'doctor.verified@example.com';
        $password = 'password123';

        // Create Medecin
        $existingMedecin = $this->entityManager->getRepository(Medecin::class)->findOneBy(['email' => $email]);
        if ($existingMedecin) {
            $output->writeln("Medecin $email already exists. Updating.");
            $medecin = $existingMedecin;
        } else {
            $output->writeln("Creating new Medecin $email.");
            $medecin = new Medecin();
            $medecin->setEmail($email);
            $medecin->setFirstName('Admin');
            $medecin->setLastName('Doctor');
        }

        $medecin->setPassword($this->passwordHasher->hashPassword($medecin, $password));
        $medecin->setIsActive(true);
        $medecin->setIsVerified(true);
        $medecin->setRoles(['ROLE_MEDECIN']);
        $medecin->setSpecialty('Generaliste');
        $medecin->setCin('77665544');

        try {
            $this->entityManager->persist($medecin);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $output->writeln("Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln("Medecin created/updated successfully.");
        $output->writeln("Email: $email");
        $output->writeln("Password: $password");

        return Command::SUCCESS;
    }
}
