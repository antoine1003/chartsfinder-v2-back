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
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractRestController extends AbstractController
{
    public function __construct(
        protected AbstractRestService $service
    )
    {
    }

    abstract function getGroupPrefix(): string;


    #[Route(path: '/{id}', name: '_get_one', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getItem(int $id, SerializerInterface $serializer): JsonResponse
    {
        $item = $this->service->find($id);
        if (!$item) {
            return new JsonResponse(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($item, 'json', ['groups' => $this->getGroupPrefix() . ':detail']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '', name: 'get_all', methods: ['GET'])]
    public function getAllItems(SerializerInterface $serializer): JsonResponse
    {
        $items = $this->service->findAll();

        $json = $serializer->serialize($items, 'json', ['groups' => $this->getGroupPrefix() . ':list']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/by', name: '_find_by', methods: ['POST'])]
    public function by(
        Request $request,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $criteria = $request->toArray();
        if (empty($criteria)) {
            return $this->json(['error' => 'No criteria provided'], Response::HTTP_BAD_REQUEST);
        }
        $items = $this->service->findBy($criteria);

        $json = $serializer->serialize($items, 'json', ['groups' => $this->getGroupPrefix() . ':list']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/search', name: '_search', methods: ['POST'])]
    public function searchItems(
        #[MapRequestPayload] SearchCriteriaDto $searchCriteriaDto,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $items = $this->service->search($searchCriteriaDto);

        $json = $serializer->serialize($items, 'json', ['groups' => $this->getGroupPrefix() . ':list']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '', name: 'create_item', methods: ['POST'])]
    public function createItem(): JsonResponse
    {
        // Logic to create an item would go here
        // For now, we return a placeholder response
        return $this->json(['message' => 'Item created'], Response::HTTP_CREATED);
    }
}
