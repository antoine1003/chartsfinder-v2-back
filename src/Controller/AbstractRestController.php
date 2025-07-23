<?php

namespace App\Controller;

use App\Dto\SearchCriteriaDto;
use App\Service\AbstractRestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

abstract class AbstractRestController extends AbstractController
{
    public function __construct(
        protected AbstractRestService $service
    )
    {
    }


    #[Route(path: '/{id}', name: '_get_one', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getItem(int $id): JsonResponse
    {
        $item = $this->service->find($id);
        if (!$item) {
            return new JsonResponse(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($item);
    }

    #[Route(path: '', name: 'get_all', methods: ['GET'])]
    public function getAllItems(): JsonResponse
    {
        $items = $this->service->findAll();
        return $this->json($items);
    }

    #[Route(path: '/by', name: '_find_by', methods: ['POST'])]
    public function by(
        Request $request,
    ): JsonResponse
    {
        $criteria = $request->toArray();
        if (empty($criteria)) {
            return $this->json(['error' => 'No criteria provided'], Response::HTTP_BAD_REQUEST);
        }
        $items = $this->service->findBy($criteria);
        return $this->json($items);
    }

    #[Route(path: '/search', name: '_search', methods: ['POST'])]
    public function searchItems(
        #[MapRequestPayload] SearchCriteriaDto $searchCriteriaDto
    ): JsonResponse
    {
        $items = $this->service->search($searchCriteriaDto);
        return $this->json($items);
    }

    #[Route(path: '', name: 'create_item', methods: ['POST'])]
    public function createItem(): JsonResponse
    {
        // Logic to create an item would go here
        // For now, we return a placeholder response
        return $this->json(['message' => 'Item created'], Response::HTTP_CREATED);
    }
}
