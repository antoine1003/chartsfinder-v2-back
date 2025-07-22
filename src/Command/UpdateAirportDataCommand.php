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
        $this->importCountries();
        $this->entityManager->flush();

        $this->importAirports();
        $this->entityManager->flush();

        $this->importRunways();
        $this->entityManager->flush();
        return Command::SUCCESS;
    }

    private function importCountries(): void
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
            $country->setId((int) $id);
            $country->setCode($code);
            $country->setName($name);
            $country->setContinent($continent);
            $country->setWikipediaLink($wikipediaLink);
            $country->setKeywords($keywords ?: null);

            $this->entityManager->persist($country);
        }

        fclose($file);
    }

    private function importAirports(): void
    {
        $path = $this->rootResourcePath . 'airports.csv';

        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: $path");
        }

        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        while (($row = fgetcsv($file)) !== false) {
            [$id, $ident, $type, $name, $lat, $lon, $elevation, $continent, $isoCountry, $isoRegion,
                $municipality, $scheduled, $icao, $iata, $gps, $local, $home, $wiki, $keywords] = $row;

            $country = $this->entityManager->getRepository(Country::class)->findOneBy(['code' => $isoCountry]);
            if (!$country) {
                continue; // skip if country missing
            }

            $airport = new Airport();
            $airport->setId((int) $id);
            $airport->setIdent($ident);
            $airport->setType($type);
            $airport->setName($name);
            $airport->setLatitudeDeg((float) $lat);
            $airport->setLongitudeDeg((float) $lon);
            $airport->setElevationFt($elevation !== '' ? (float) $elevation : null);
            $airport->setMunicipality($municipality ?: null);
            $airport->setScheduledService(strtolower($scheduled) === 'yes');
            $airport->setIcaoCode($icao ?: null);
            $airport->setIataCode($iata ?: null);
            $airport->setGpsCode($gps ?: null);
            $airport->setLocalCode($local ?: null);
            $airport->setHomeLink($home ?: null);
            $airport->setWikipediaLink($wiki ?: null);
            $airport->setKeywords($keywords ?: null);
            $airport->setCountry($country);

            $this->entityManager->persist($airport);
        }

        fclose($file);
    }

    private function importRunways(): void
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

            $airport = $this->entityManager->getRepository(Airport::class)->find((int) $airportRef);
            if (!$airport) {
                continue;
            }

            $runway = new Runway();
            $runway->setId((int) $id);
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
        }

        fclose($file);
    }
}
