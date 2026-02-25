<?php

namespace App\Command;

use App\Repository\MedecinRepository;
use App\Service\MedecinSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:doctor:sync-ai-scores',
    description: 'Synchronize all doctors AI scores using Flask Sentiment API',
)]
class SyncAiScoresCommand extends Command
{
    private MedecinRepository $medecinRepository;
    private MedecinSyncService $syncService;

    public function __construct(MedecinRepository $medecinRepository, MedecinSyncService $syncService)
    {
        parent::__construct();
        $this->medecinRepository = $medecinRepository;
        $this->syncService = $syncService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $doctors = $this->medecinRepository->findAll();

        $io->title('Synchronizing Doctor AI Scores');
        $io->progressStart(count($doctors));

        $successCount = 0;
        $failCount = 0;

        foreach ($doctors as $doctor) {
            if ($this->syncService->syncAiScore($doctor)) {
                $successCount++;
            } else {
                $failCount++;
            }
            $io->progressAdvance();
        }

        $io->progressFinish();

        if ($failCount === 0) {
            $io->success(sprintf('Successfully synced %d doctors.', $successCount));
        } else {
            $io->warning(sprintf('Synced %d doctors, but %d failed (check logs).', $successCount, $failCount));
        }

        return Command::SUCCESS;
    }
}