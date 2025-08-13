<?php

namespace App\Controller;

use App\Dto\SearchCriteriaDto;
use App\Service\AbstractRestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /**
     * @throws ExceptionInterface
     */
    #[Route(path: '', name: 'create_item', methods: ['POST'])]
    public function createItem(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        Security $security,
    ): JsonResponse
    {
        $data = $request->toArray();

        $entityClass = $this->service->getEntityClass(); // Should return Feature::class
        if (!$entityClass) {
            return $this->json(['error' => 'Entity class not defined'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Deserialize with group
            $item = $serializer->deserialize(
                json_encode($data),
                $entityClass,
                'json',
                ['groups' => $this->getGroupPrefix() . ':create']
            );
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'error' => 'Invalid request data',
                'details' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        // Set the authenticated user as the creator
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        if (method_exists($item, 'setCreatedBy')) {
            $item->setCreatedBy($user);
        }

        // Validate entity
        $errors = $validator->validate($item);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = [
                    'property' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
            return $this->json([
                'errors' => $errorsArray,
            ], Response::HTTP_BAD_REQUEST);
        }

        // Save entity
        $this->service->save($item);

        return $this->json(['message' => 'Item created'], Response::HTTP_CREATED);
    }
}
