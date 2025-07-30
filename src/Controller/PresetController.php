<?php

namespace App\Controller;

use App\Dto\PresetDto;
use App\Entity\Preset;
use App\Service\PresetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
        #[MapRequestPayload] PresetDto $presetDto,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $item = $this->presetService->createOrUpdate($presetDto);
        if (!$item) {
            return new JsonResponse(['error' => 'Preset not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $json = $serializer->serialize($item, 'json', ['groups' => ['preset:detail']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: '_one', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getOne(Preset $preset, SerializerInterface $serializer): JsonResponse
    {
        $items = $this->presetService->formatByAirport($preset);
        $json = $serializer->serialize($items, 'json', ['groups' => ['preset:detail']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // get all presets
    #[Route(path: '/mine', name: '_get_mine', methods: ['GET'])]
    public function getMine(SerializerInterface $serializer): JsonResponse
    {
        $presets = $this->presetService->findMine();
        $result = [];
        foreach ($presets as $preset) {
            $result[] = $this->presetService->formatByAirport($preset);
        }

        $json = $serializer->serialize($result, 'json', ['groups' => ['preset:detail']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: '_delete', methods: ['DELETE'])]
    public function delete(Preset $preset,): JsonResponse
    {
        $this->presetService->delete($preset);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
