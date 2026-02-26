<?php

namespace App\Command;

use App\Entity\Medecin;
use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: "app:create-test-users",
    description: "Creates test Medecin and Patient users",
)]
class CreateTestUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create Medecin
        $medecinEmail = "medecin@test.com";
        $existingMedecin = $this->entityManager->getRepository(Medecin::class)->findOneBy(["email" => $medecinEmail]);

        if (!$existingMedecin) {
            $medecin = new Medecin();
            $medecin->setEmail($medecinEmail);
            $medecin->setFirstName("Jean");
            $medecin->setLastName("Doctor");
            $medecin->setSpecialty("Cardiologist"); // Required
            $medecin->setCin("12345678"); // Required unique
            $medecin->setPhoneNumber("12345678");
            $medecin->setRoles(["ROLE_MEDECIN"]);
            $medecin->setPassword(
                $this->passwordHasher->hashPassword($medecin, "password123")
            );
            $this->entityManager->persist($medecin);
            $io->success("Medecin created: " . $medecinEmail);
        } else {
            $io->warning("Medecin already exists: " . $medecinEmail);
        }

        // Create Patient
        $patientEmail = "patient@test.com";
        $existingPatient = $this->entityManager->getRepository(Patient::class)->findOneBy(["email" => $patientEmail]);

        if (!$existingPatient) {
            $patient = new Patient();
            $patient->setEmail($patientEmail);
            $patient->setFirstName("Pierre");
            $patient->setLastName("Patient");
            $patient->setRoles(["ROLE_PATIENT"]);
            $patient->setPassword(
                $this->passwordHasher->hashPassword($patient, "password123")
            );
            $this->entityManager->persist($patient);
            $io->success("Patient created: " . $patientEmail);
        } else {
            $io->warning("Patient already exists: " . $patientEmail);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
