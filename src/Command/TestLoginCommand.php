<?php

namespace App\Command;

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:test-login',
    description: 'Debug login issues by checking user existence and password hash'
)]
class TestLoginCommand extends Command
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
        $output->writeln('Checking users in database...');

        $patients = $this->entityManager->getRepository(Patient::class)->findAll();
        $output->writeln(sprintf('Found %d patients.', count($patients)));

        foreach ($patients as $patient) {
            $output->writeln(sprintf(' - Patient Email: %s', $patient->getEmail()));
            $output->writeln(sprintf('   Password Hash: %s', substr($patient->getPassword(), 0, 20) . '...'));
            $output->writeln(sprintf('   Roles: %s', implode(', ', $patient->getRoles())));
        }

        $medecins = $this->entityManager->getRepository(Medecin::class)->findAll();
        $output->writeln(sprintf('Found %d medecins.', count($medecins)));

        return Command::SUCCESS;
    }
}
