<?php

namespace App\Service;

use App\Dto\PresetDto;
use App\Entity\Airport;
use App\Entity\Preset;
use App\Entity\User;
use App\Repository\AirportRepository;
use App\Repository\PresetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends AbstractRestService<AirportRepository>
 */
class PresetService
{
    public function __construct(
        protected EntityManagerInterface   $entityManager,
        private readonly PresetRepository  $presetRepository,
        private readonly AirportRepository $airportRepository,
        private readonly Security          $security
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
        }

        $preset->setName($presetDto->getName());
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        $preset->setUser($user);

        $airports = $presetDto->getAirports();
        $oldAirports = $preset->getAirports()->map(fn(Airport $a) => $a->getId())->toArray();
        $newAirports = [];
        foreach ($airports as $airportId) {
            $airport = $this->airportRepository->find($airportId);
            if ($airport) {
                $newAirports[] = $airport->getId();
                $preset->addAirport($airport);
            }
        }

        // Remove airports that are no longer in the preset
        foreach ($oldAirports as $oldAirportId) {
            if (!in_array($oldAirportId, $newAirports)) {
                $airport = $this->airportRepository->find($oldAirportId);
                if ($airport) {
                    $preset->removeAirport($airport);
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
