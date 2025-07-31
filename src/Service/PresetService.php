<?php

namespace App\Service;

use App\Dto\PresetDto;
use App\Entity\Airport;
use App\Entity\Chart;
use App\Entity\Preset;
use App\Entity\User;
use App\Repository\AirportRepository;
use App\Repository\ChartRepository;
use App\Repository\PresetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @extends AbstractRestService<AirportRepository>
 */
class PresetService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        private readonly PresetRepository $presetRepository,
        private readonly ChartRepository $chartRepository,
        private readonly Security $security
    )
    {
    }


    // Block finAll to avoid returning all airports
    public function findAll(): array
    {
        return $this->presetRepository->findAll();
    }

    public function formatByAirport(Preset $preset): array
    {
        $formatted = [
            'id' => $preset->getId(),
            'name' => $preset->getName(),
            'airports' => new ArrayCollection(),
        ];

        // I want to keep all as entities, not just ids
        foreach ($preset->getCharts() as $chart) {
            $airport = clone $chart->getAirport();
            /**
             * @var ArrayCollection<Airport> $airportsInResult
             */
            $airportsInResult = $formatted['airports'];
            $airportInResult = $airportsInResult->filter(fn(Airport $a) => $a->getId() === $airport->getId())->first();
            if ($airportInResult) {
                // If the airport already exists in the result, add the chart to it
                $airportInResult->addChart($chart);
            } else {
                // If the airport does not exist, create a new entry
                $airport->addChart($chart);
                $formatted['airports']->add($airport);
            }
        }

        return $formatted;
    }

    public function createOrUpdate(PresetDto $presetDto)
    {
        $preset = $presetDto->getId() ? $this->presetRepository->find($presetDto->getId()) : null;

        if (!$preset) {
            $preset = new Preset();
            $preset->setName($presetDto->getName());
            /**
             * @var User $user
             */
            $user = $this->security->getUser();
            $preset->setUser($user);
        }

        $charts = $presetDto->getCharts();
        $oldCharts = $preset->getCharts()->map(fn(Chart $chart) => $chart->getId())->toArray();
        $newCharts = [];
        foreach ($charts as $chartId) {
            $chart = $this->chartRepository->find($chartId);
            if ($chart) {
                $newCharts[] = $chart->getId();
                $preset->addChart($chart);
            }
        }

        // Remove charts that are no longer in the preset
        foreach ($oldCharts as $oldChartId) {
            if (!in_array($oldChartId, $newCharts)) {
                $chart = $this->chartRepository->find($oldChartId);
                if ($chart) {
                    $preset->removeChart($chart);
                }
            }
        }

        // Persist the preset
        $this->entityManager->persist($preset);
        $this->entityManager->flush();

        return $preset;
    }

    public function findMine(): array
    {
        return $this->presetRepository->findMine();
    }

    public function delete(Preset $preset): void
    {
        $this->entityManager->remove($preset);
        $this->entityManager->flush();
    }
}
