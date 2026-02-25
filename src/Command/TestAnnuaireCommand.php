<?php
// src/Command/TestAnnuaireCommand.php

namespace App\Command;

use App\Service\AnnuaireService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-annuaire',
    description: 'Test AnnuaireService connection',
)]
class TestAnnuaireCommand extends Command
{
    public function __construct(
        private AnnuaireService $annuaireService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Testing AnnuaireService');

        try {
            $status = $this->annuaireService->getApiStatus();
            $io->success('Connected to Flask API');
            $io->table(
                ['Key', 'Value'],
                [
                    ['Status', $status['status'] ?? 'N/A'],
                    ['Message', $status['message'] ?? 'N/A'],
                    ['Data Loaded', $status['data_loaded'] ? 'Yes' : 'No'],
                    ['Record Count', $status['record_count'] ?? 0],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Connection failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
