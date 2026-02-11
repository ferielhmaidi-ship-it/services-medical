<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a new admin user (for command line only)',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password')
            ->addArgument('name', InputArgument::REQUIRED, 'Admin display name')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'Admin first name', 'Admin')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'Admin last name', 'User')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');

        // Check if admin with this email already exists
        $existingAdmin = $this->entityManager->getRepository(Admin::class)->findOneBy(['email' => $email]);

        if ($existingAdmin) {
            $io->error('An admin with this email already exists!');
            return Command::FAILURE;
        }

        $admin = new Admin();
        $admin->setEmail($email);
        $admin->setName($name);
        $admin->setFirstName($firstName);
        $admin->setLastName($lastName);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, $password)
        );

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success([
            'Admin user created successfully!',
            sprintf('Email: %s', $email),
            sprintf('Name: %s (%s %s)', $name, $firstName, $lastName),
            'You can now login at /login'
        ]);

        return Command::SUCCESS;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questions = [];

        if (!$input->getArgument('email')) {
            $questions['email'] = new Question('Please enter the admin email: ');
            $questions['email']->setValidator(function ($value) {
                if (empty($value)) {
                    throw new \Exception('Email cannot be empty');
                }
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception('Please enter a valid email address');
                }
                return $value;
            });
        }

        if (!$input->getArgument('password')) {
            $questions['password'] = new Question('Please enter the admin password: ');
            $questions['password']->setHidden(true);
            $questions['password']->setHiddenFallback(false);
            $questions['password']->setValidator(function ($value) {
                if (empty($value)) {
                    throw new \Exception('Password cannot be empty');
                }
                if (strlen($value) < 6) {
                    throw new \Exception('Password must be at least 6 characters');
                }
                return $value;
            });
        }

        if (!$input->getArgument('name')) {
            $questions['name'] = new Question('Please enter the admin display name: ');
            $questions['name']->setValidator(function ($value) {
                if (empty($value)) {
                    throw new \Exception('Name cannot be empty');
                }
                return $value;
            });
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }
}
