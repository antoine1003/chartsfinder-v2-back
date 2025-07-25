<?php

namespace App\Service;

use App\Dto\PresetDto;
use App\Entity\Airport;
use App\Entity\Chart;
use App\Entity\Preset;
use App\Repository\AirportRepository;
use App\Repository\ChartRepository;
use App\Repository\PresetRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    )
    {
    }


    // Block finAll to avoid returning all airports
    public function findAll(): array
    {
        return $this->presetRepository->findAll();
    }

    public function createOrUpdate(PresetDto $presetDto)
    {
        $preset = $presetDto->getId() ? $this->presetRepository->find($presetDto->getId()) : null;

        if (!$preset) {
            $preset = new Preset();
            $preset->setName($presetDto->getName());
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
