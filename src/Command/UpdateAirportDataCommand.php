<?php

namespace App\Command;

use App\Entity\Airport;
use App\Entity\Country;
use App\Entity\Runway;
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

        // Empty existing data
        $io->title('Updating Airport Data');
        $io->section('Emptying existing data');
        $io->text('Removing existing countries, airports, and runways...');
        $this->entityManager->createQuery('DELETE FROM App\Entity\Runway')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Airport')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Country')->execute();
        $io->text('Existing data removed successfully.');

        $io->section('Importing new data');
        $io->text('Importing countries, airports, and runways from CSV files...');
        $start = microtime(true);

        // Import new data
        $io->text('Importing countries...');
        $this->importCountries($io);
        $this->entityManager->flush();

        $io->text('Importing airports...');
        $this->importAirports($io);
        $this->entityManager->flush();

        $io->text('Importing runways...');
        $this->importRunways($io);
        $this->entityManager->flush();

        $end = microtime(true);
        $duration = $end - $start;
        $io->success(sprintf('Data imported successfully in %.2f seconds.', $duration));

        return Command::SUCCESS;
    }

    private function importCountries(SymfonyStyle $io): void
    {
        $path = $this->rootResourcePath . 'countries.csv';
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: $path");
        }

        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        while (($row = fgetcsv($file)) !== false) {
            [$id, $code, $name, $continent, $wikipediaLink, $keywords] = $row;

            $country = new Country();
            $country->setCode($code);
            $country->setName($name);
            $country->setContinent($continent);
            $country->setWikipediaLink($wikipediaLink);
            $country->setKeywords($keywords ?: null);
            $io->text(sprintf('Importing country: %s (%s)', $country->getName(), $country->getCode()));

            $this->entityManager->persist($country);
        }

        fclose($file);
    }

    private function importAirports(SymfonyStyle $io): void
    {
        $path = $this->rootResourcePath . 'airports.json';

        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: $path");
        }

        $file = fopen($path, 'r');
        $data = fread($file, filesize($path));
        fclose($file);

        $data = json_decode($data, true);
        foreach ($data as $k => $v) {
            $airport = $v;
            $isoCountry = $airport['country'];

            $country = $this->entityManager->getRepository(Country::class)->findOneBy(['code' => $isoCountry]);
            if (!$country) {
                continue; // skip if country missing
            }

            $airportEntity = new Airport();
            $airportEntity->setType($airport['type']);
            $airportEntity->setName($airport['name']);
            $airportEntity->setLatitudeDeg((float) $airport['lat']);
            $airportEntity->setLongitudeDeg((float) $airport['lon']);
            $airportEntity->setElevationFt($airport['elevation'] !== '' ? (float) $airport['elevation'] : null);
            $airportEntity->setIcaoCode($airport['icao'] ?: null);
            $airportEntity->setIataCode($airport['iata'] ?: null);
            $airportEntity->setCountry($country);

            $this->entityManager->persist($airportEntity);
            $io->text(sprintf('Importing airport: %s (%s)', $airportEntity->getName(), $airportEntity->getIcaoCode()));
        }
    }

    private function importRunways(SymfonyStyle $io): void
    {
        $path = $this->rootResourcePath . 'runways.csv';
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: $path");
        }

        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        while (($row = fgetcsv($file)) !== false) {
            [$id, $airportRef, $airportIdent, $length, $width, $surface, $lighted, $closed,
                $leIdent, $leLat, $leLon, $leElev, $leHeading, $leDisplaced,
                $heIdent, $heLat, $heLon, $heElev, $heHeading, $heDisplaced] = $row;

            // remove " from the beginning and end of the airportIdent
            $airportIdent =  trim($airportIdent, '"');
            if (!str_starts_with($airportIdent, 'LF') && !str_starts_with($airportIdent, 'TF')) {
                continue;
            }
            $io->text("Processing runway for airport: $airportIdent");

            $airport = $this->entityManager->getRepository(Airport::class)->findOneBy(['icaoCode' => $airportIdent]);
            if (!$airport) {
                continue;
            }

            $runway = new Runway();
            $runway->setAirport($airport);
            $runway->setLengthFt($length !== '' ? (int) $length : null);
            $runway->setWidthFt($width !== '' ? (int) $width : null);
            $runway->setSurface($surface ?: null);
            $runway->setLighted((bool) $lighted);
            $runway->setClosed((bool) $closed);
            $runway->setLeIdent($leIdent ?: null);
            $runway->setLeLatitudeDeg($leLat !== '' ? (float) $leLat : null);
            $runway->setLeLongitudeDeg($leLon !== '' ? (float) $leLon : null);
            $runway->setLeElevationFt($leElev !== '' ? (float) $leElev : null);
            $runway->setLeHeadingDegT($leHeading !== '' ? (float) $leHeading : null);
            $runway->setLeDisplacedThresholdFt($leDisplaced !== '' ? (float) $leDisplaced : null);
            $runway->setHeIdent($heIdent ?: null);
            $runway->setHeLatitudeDeg($heLat !== '' ? (float) $heLat : null);
            $runway->setHeLongitudeDeg($heLon !== '' ? (float) $heLon : null);
            $runway->setHeElevationFt($heElev !== '' ? (float) $heElev : null);
            $runway->setHeHeadingDegT($heHeading !== '' ? (float) $heHeading : null);
            $runway->setHeDisplacedThresholdFt($heDisplaced !== '' ? (float) $heDisplaced : null);

            $this->entityManager->persist($runway);
            $io->text(sprintf('Importing runway: %s (%s)', $runway->getLeIdent(), $airport->getIcaoCode()));
        }

        fclose($file);
    }
}
