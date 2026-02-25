<?php

namespace App\Command;

use App\Service\WeatherService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:check-weather',
    description: 'Check OpenWeather forecast via WeatherService for a given Tunisian location and time.',
)]
class CheckWeatherAndSuggestReschedulingCommand extends Command
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        parent::__construct();
        $this->weatherService = $weatherService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('location', InputArgument::OPTIONAL, 'Tunisian city / governorate (e.g. Tunis, Sfax, Sousse)', 'Tunis')
            ->addArgument('datetime', InputArgument::OPTIONAL, 'Date/time to check (Y-m-d H:i, default: now + 3 hours)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $location = (string) $input->getArgument('location');
        $datetimeArg = $input->getArgument('datetime');

        if ($datetimeArg) {
            try {
                $dateTime = new \DateTimeImmutable((string) $datetimeArg);
            } catch (\Exception $e) {
                $output->writeln('<error>Invalid datetime format. Use "Y-m-d H:i", e.g. 2026-02-25 15:00</error>');
                return Command::INVALID;
            }
        } else {
            $dateTime = new \DateTimeImmutable('+3 hours');
        }

        $output->writeln(sprintf(
            'Checking weather for <info>%s</info>, date/time <info>%s</info>...',
            $location,
            $dateTime->format('Y-m-d H:i')
        ));

        $badWeather = $this->weatherService->getBadWeatherForecast($location, $dateTime);

        if ($badWeather === null) {
            $output->writeln('<info>No bad weather detected (or API/cache returned no data).</info>');
        } else {
            $output->writeln(sprintf(
                '<comment>Bad weather detected: %s</comment>',
                $badWeather
            ));
        }

        return Command::SUCCESS;
    }
}

