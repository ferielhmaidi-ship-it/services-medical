<?php

namespace App\Command;

use App\Repository\RendezVousRepository;
use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-appointment-reminders',
    description: 'Envoie des rappels email pour les rendez-vous de demain (24h avant)',
)]
class SendAppointmentRemindersCommand extends Command
{
    private RendezVousRepository $rendezVousRepository;
    private EmailService $emailService;

    public function __construct(RendezVousRepository $rendezVousRepository, EmailService $emailService)
    {
        parent::__construct();
        $this->rendezVousRepository = $rendezVousRepository;
        $this->emailService = $emailService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer tous les RDV de demain avec statut "en_attente"
        // (pour envoyer un rappel 24h avant)
        $tomorrow = new \DateTime('tomorrow');
        $dayAfterTomorrow = new \DateTime('+2 days');

        $rendezVous = $this->rendezVousRepository->createQueryBuilder('r')
            ->where('r.appointmentDate >= :tomorrow')
            ->andWhere('r.appointmentDate < :dayAfterTomorrow')
            ->andWhere('r.statut = :statut')
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('dayAfterTomorrow', $dayAfterTomorrow)
            ->setParameter('statut', 'en_attente')
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($rendezVous as $rdv) {
            try {
                $this->emailService->sendAppointmentReminder($rdv);
                $count++;
                $io->success(sprintf(
                    'Rappel envoyé à %s pour RDV demain avec Dr. %s à %s',
                    $rdv->getPatient()->getEmail(),
                    $rdv->getDoctor()->getLastName(),
                    $rdv->getAppointmentDate()->format('H:i')
                ));
            } catch (\Exception $e) {
                $io->error(sprintf('Erreur pour RDV #%d: %s', $rdv->getId(), $e->getMessage()));
            }
        }

        $io->success(sprintf('%d rappel(s) envoyé(s) avec succès!', $count));

        return Command::SUCCESS;
    }
}