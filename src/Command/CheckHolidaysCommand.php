<?php

namespace App\Command;

use App\Service\HolidayService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:check-holidays',
    description: 'Check Tunisian public holidays via HolidayService for a given year.',
)]
class CheckHolidaysCommand extends Command
{
    private HolidayService $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        parent::__construct();
        $this->holidayService = $holidayService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('year', InputArgument::OPTIONAL, 'Year to fetch holidays for', (int) date('Y'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $year = (int) $input->getArgument('year');

        $output->writeln(sprintf('Fetching Tunisian public holidays for <info>%d</info>...', $year));

        $holidays = $this->holidayService->getTunisianHolidays($year);

        if (empty($holidays)) {
            $output->writeln('<comment>No holidays returned (API unreachable or empty response).</comment>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>%d holidays found.</info>', count($holidays)));

        foreach (array_slice($holidays, 0, 5) as $holiday) {
            $output->writeln(sprintf(
                '- %s: %s',
                $holiday['date'] ?? 'unknown date',
                $holiday['localName'] ?? ($holiday['name'] ?? 'unknown')
            ));
        }

        if (count($holidays) > 5) {
            $output->writeln(sprintf('... and %d more', count($holidays) - 5));
        }

        return Command::SUCCESS;
    }
}

