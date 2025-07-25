<?php

namespace App\Controller;

use App\Dto\PresetDto;
use App\Entity\Preset;
use App\Service\AbstractRestService;
use App\Service\AirportRestService;
use App\Service\PresetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/presets', name: 'presets')]
class PresetController extends AbstractController
{
    public function __construct(
        private readonly PresetService $presetService
    )
    {
    }

    #[Route(path: '', name: '_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] PresetDto $presetDto
    ): JsonResponse
    {
        $item = $this->presetService->createOrUpdate($presetDto);
        if (!$item) {
            return new JsonResponse(['error' => 'Preset not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        return $this->json($item);
    }

    // get all presets
    #[Route(path: '/mine', name: '_get_mine', methods: ['GET'])]
    public function getMine(): JsonResponse
    {
        $items = $this->presetService->findMine();
        // Convert items to DTOs if necessary
        $items = array_map(fn(Preset $item) => $item->toDto(), $items);
        return $this->json($items);
    }

    #[Route(path: '/{id}', name: '_delete', methods: ['DELETE'])]
    public function delete(Preset $preset): JsonResponse
    {
        $this->presetService->delete($preset);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
