<?php

namespace App\Command;

use AllowDynamicProperties;
use App\Entity\Airport;
use App\Entity\Chart;
use App\Entity\Enum\ChartTypeEnum;
use App\Entity\Runway;
use App\Repository\AirportRepository;
use App\Repository\ChartRepository;
use App\Service\AirportRestService;
use App\Service\ChartRestService;
use CobaltGrid\AIRACCalculator\AIRACCycle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-charts',
    description: 'Add a short description for your command',
)]
class ImportChartsCommand extends Command
{
    private string $rootResourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'airac' . DIRECTORY_SEPARATOR;
    private AIRACCycle $currentAirac;
    public function __construct(
        private readonly AirportRepository      $airportRepository,
        private readonly ChartRepository        $chartRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();

        $this->currentAirac = AIRACCycle::current();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $start = microtime(true);

        $io->title('Import Charts Command');
        $io->section('Current AIRAC Cycle');
        $io->text(sprintf('Current AIRAC Cycle: %s', $this->currentAirac->getCycleCode()));
        $io->text(sprintf('Effective data: %s', $this->currentAirac->getEffectiveDate()->format('Y-m-d')));

        $path = $this->rootResourcePath . "{$this->currentAirac->getCycleCode()}.json";
        if (!file_exists($path)) {
            $io->error("File not found: $path");
            return Command::FAILURE;
        }

        $file = fopen($path, 'r');
        if ($file === false) {
            $io->error("Failed to open file: $path");
            return Command::FAILURE;
        }
        $io->info("Importing ifrCharts from: $path");
        $data = json_decode(fread($file, filesize($path)), true);
        fclose($file);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error('Invalid JSON format in file: ' . json_last_error_msg());
            return Command::FAILURE;
        }
        if (empty($data)) {
            $io->warning('No data found in the file.');
            return Command::SUCCESS;
        }

        $airports = $data['body']['airports'] ?? [];

        foreach ($airports as $airport) {
            $icao = $airport['oaci'] ?? 'Unknown ICAO';
            $airportEntity = $this->airportRepository->findOneBy(['icaoCode' => $icao]);
            if (!$airportEntity) {
                $io->warning(sprintf('Airport with ICAO %s not found in the database. Skiping.', $icao));
                continue;
            }

            $ifrCharts = $airport['ifrCharts'] ?? [];

            $io->section(sprintf('Processing ifrCharts for airport %s (%s)', $airportEntity->getName(), $icao));
            if (empty($ifrCharts)) {
                $io->text(sprintf('No ifrCharts found for airport %s (%s).', $airportEntity->getName(), $icao));
            }

            $this->handleVfrChart($airport, $airportEntity, $io);
            $this->handleIfrFullChart($airport, $airportEntity, $io);

            foreach ($ifrCharts as $chart) {
                $chartName = $chart['name'];
                $chartUrl = $chart['url'];
                $chartData = $this->extractChartInfo($chartName);

                $airacCycle = $this->currentAirac->getCycleCode();

                $chartEntity = $this->chartRepository->findOneBy([
                    'name' => $chartName,
                    'airport' => $airportEntity
                ]);

                if (!$chartEntity) {
                    $chartEntity = new Chart();
                }

                $chartEntity->setName($chartName)
                    ->setAirport($airportEntity)
                    ->setUrl($chartUrl)
                    ->setAirac($airacCycle)
                    ->setType($chartData['type'])
                    ->setRunway($chartData['runway'])
                    ->setSubType($chartData['subtype']);


                if (is_null($chartEntity->getId())) {
                    $this->entityManager->persist($chartEntity);
                }
            }
        }

        $this->entityManager->flush();

        $end = microtime(true);
        $executionTime = $end - $start;

        $io->success(sprintf('Charts imported successfully in %.2f seconds.', $executionTime));

        return Command::SUCCESS;
    }

    private function handleIfrFullChart(array $airport, Airport $airportEntity, SymfonyStyle $io): void
    {
        if (!empty($airport['ifrFullChart'])) {
            $chartEntity = $this->chartRepository->findOneBy([
                'type' => ChartTypeEnum::IFR_FULL,
                'airport' => $airportEntity
            ]);

            if (!$chartEntity) {
                $chartEntity = new Chart();
            }

            $chartEntity->setUrl($airport['ifrFullChart'])
                ->setAirport($airportEntity)
                ->setName(ChartTypeEnum::IFR_FULL)
                ->setAirac($this->currentAirac->getCycleCode())
                ->setType(ChartTypeEnum::IFR_FULL);

            if (is_null($chartEntity->getId())) {
                $this->entityManager->persist($chartEntity);
            }
        }
    }

    private function handleVfrChart(array $airport, Airport $airportEntity, SymfonyStyle $io): void
    {
        if (!empty($airport['vfrChart'])) {
            $chartEntity = $this->chartRepository->findOneBy([
                'type' => ChartTypeEnum::VFR,
                'airport' => $airportEntity
            ]);

            if (!$chartEntity) {
                $chartEntity = new Chart();
            }

            $chartEntity->setUrl($airport['vfrChart'])
                ->setAirport($airportEntity)
                ->setName(ChartTypeEnum::VFR)
                ->setAirac($this->currentAirac->getCycleCode())
                ->setType(ChartTypeEnum::VFR);

            if (is_null($chartEntity->getId())) {
                $this->entityManager->persist($chartEntity);
            }
        }
    }

    private function extractChartInfo(string $chartName): array {
        // Suppression du préfixe "AD_2_" et découpage du reste
        $name = str_replace('AD_2_', '', $chartName);
        $parts = explode('_', $name);

        $icao = $parts[0] ?? null;
        $type = $parts[1] ?? null;
        $runway = null;
        $subtype = [];

        // Recherche RWY dans les parties
        foreach ($parts as $index => $part) {
            if (strpos($part, 'RWY') === 0) {
                $runway = substr($part, 3); // Supprime "RWY"
                // Vérifie s'il y a des sous-parties après RWY
                $subtype = array_slice($parts, $index + 1);
                break;
            }
        }

        // Si pas de RWY, les sous-types sont les éléments après le type
        if (!$runway && count($parts) > 2) {
            $subtype = array_slice($parts, 2);
        }

        return [
            'icao'     => $icao,
            'type'     => $type,
            'runway'   => $runway,
            'subtype'  => !empty($subtype) ? implode('_', $subtype) : null,
        ];
    }
}
