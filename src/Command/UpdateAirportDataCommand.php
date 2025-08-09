<?php

namespace App\Command;

use App\Entity\Airport;
use App\Entity\Runway;
use App\Repository\AirportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-airport-data',
    description: 'Add a short description for your command',
)]
class UpdateAirportDataCommand extends Command
{
    private string $rootResourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AirportRepository $airportRepository

    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Ask for confirmation before proceeding
        if (!$io->confirm('This command will empty existing airport data and import new data from CSV files. Do you want to continue?', false)) {
            $io->warning('Operation cancelled by user.');
            return Command::FAILURE;
        }
        $start = microtime(true);
        // Empty existing data
        $io->title('Updating Airport Data');
        $io->section('Emptying existing data');
        $io->text('Removing existing countries, airports, and runways...');
        $this->entityManager->createQuery('DELETE FROM App\Entity\Chart')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Runway')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Airport')->execute();
        $io->text('Existing data removed successfully.');

        // Import new data
        $io->text('Importing airports...');
        $this->importAirports($io);
        $this->entityManager->flush();

        $end = microtime(true);
        $duration = $end - $start;
        $io->success(sprintf('Data imported successfully in %.2f seconds.', $duration));

        return Command::SUCCESS;
    }

    private function importAirports(SymfonyStyle $io): void
    {
        $path = $this->rootResourcePath . 'Airports.txt';

        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: $path");
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nbAirports = 0;
        $nbRunways = 0;
        foreach ($lines as $line) {
            $parts = str_getcsv($line);
            $type = $parts[0];

            switch ($type) {
                case 'A':
                    // Extract airport
                    $ident = $parts[1];
                    $currentAirport = $this->airportRepository->findOneBy(['icaoCode' => $ident]);
                    if (!$currentAirport) {
                        // Skip or log warning
                        $currentAirport = null;
                        $io->warning("Airport with ICAO code $ident not found. Creating new airport.");
                        $currentAirport = new Airport();
                        $currentAirport->setIcaoCode($ident);
                        $currentAirport->setName($parts[2] ?? null);
                        $currentAirport->setLatitudeDeg((float)$parts[3]);
                        $currentAirport->setLongitudeDeg((float)$parts[4]);
                        $currentAirport->setElevationFt((float)($parts[5] ?? 0));
                        $this->entityManager->persist($currentAirport);
                        $nbAirports++;
                    }

                    break;

                case 'R':
                    if (!$currentAirport) {
                        // Skip: runway without airport context
                        continue 2;
                    }

                    // Extract and create Runway, link to $currentAirport
                    $runway = new Runway();
                    $runway->setAirport($currentAirport);
                    $runway->setIdent($parts[1]);
                    $runway->setHeading((int)$parts[2]);
                    $runway->setLengthFt((int)$parts[3]);
                    $runway->setWidthFt((int)$parts[4]);
                    $ilsAvailable = $parts[5] === '1';
                    $runway->setIlsAvailable($ilsAvailable);
                    $runway->setIlsFrequency($ilsAvailable ? $parts[6] : null);
                    $runway->setIlsQdm($ilsAvailable ? $parts[7] : null);
                    $runway->setSurface(null); // not provided
                    $runway->setLat((float)$parts[8]);
                    $runway->setLon((float)$parts[9]);
                    $runway->setElevationFt((float)$parts[10]);
                    $runway->setSurface((float)$parts[13] ?? null);
                    $this->entityManager->persist($runway);
                    $nbRunways++;
                    break;
            }
        }
        $io->text('Airports imported successfully.');
        $io->text(sprintf('Total airports: %d, Total runways: %d', $nbAirports, $nbRunways));
        $this->entityManager->flush();
    }
}
