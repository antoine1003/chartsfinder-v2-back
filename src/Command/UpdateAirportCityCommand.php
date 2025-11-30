<?php

namespace App\Command;

use App\Entity\Airport;
use App\Entity\Chart;
use App\Entity\Runway;
use App\Repository\AirportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:update-airport-city',
    description: 'Update airport city names from GlobalAirportDatabase.txt file',
)]
class UpdateAirportCityCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AirportRepository $airportRepository

    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get all data from file ./resources/GlobalAirportDatabase.txt. The file is ':' separated and has the following columns: OACI:IATA:NAME:CITY:COUNTRY and others we don't need
        $filePath = __DIR__ . '/resources/GlobalAirportDatabase.txt';
        if (!file_exists($filePath)) {
            $io->error('File not found: ' . $filePath);
            return Command::FAILURE;
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $progress = $io->createProgressBar(count($lines));
        foreach ($lines as $line) {
            $columns = explode(':', $line);
            $progress->advance();
            if (count($columns) < 4) {
                continue; // Skip invalid lines
            }
            // if nalue are N/A set null
            $oaci = trim($columns[0]);
            $name = trim($columns[2]);
            $city = trim($columns[3]);


            $airport = $this->airportRepository->findOneBy([
                'icaoCode' => $oaci
            ]);
            if ($airport) {

                $airport->setCity($city === 'N/A' ? null : $city);
                $airport->setName($name === 'N/A' ? null : $name);

                $this->em->persist($airport);
            }
        }
        $progress->finish();

        $this->em->flush();

        $progress->start();

        return Command::SUCCESS;
    }
}
