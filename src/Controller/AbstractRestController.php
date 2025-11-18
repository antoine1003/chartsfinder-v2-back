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
    private string $CREATE_ACTION = 'create';
    private string $READ_ACTION = 'read';
    private string $READ_ALL_ACTION = 'read_all';
    private string $SEARCH_ACTION = 'search';
    private string $UPDATE_ACTION = 'update';
    private string $DELETE_ACTION = 'delete';


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

        // Check if the user can read this entity
        if (!$this->isGranted($this->READ_ACTION, $this->service->getEntityClass())) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        $json = $serializer->serialize($item, 'json', ['groups' => $this->getGroupPrefix() . ':detail']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '', name: 'get_all', methods: ['GET'])]
    public function getAllItems(SerializerInterface $serializer): JsonResponse
    {
        $items = $this->service->findAll();
        if (!$this->isGranted($this->READ_ALL_ACTION, $this->service->getEntityClass())) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
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

        if (!$this->isGranted($this->READ_ALL_ACTION, $this->service->getEntityClass())) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
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

        if (!$this->isGranted($this->SEARCH_ACTION, $this->service->getEntityClass())) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $items = $this->service->search($searchCriteriaDto);

        $json = $serializer->serialize($items, 'json', ['groups' => $this->getGroupPrefix() . ':list', 'is_search' => true]);
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

        // Check if the user can create this entity
        if (!$this->isGranted($this->CREATE_ACTION, $item)) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
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

        $serialized = $serializer->serialize(
            $item,
            'json',
            ['groups' => $this->getGroupPrefix() . ':detail']
        );

        return $this->json($serialized, Response::HTTP_CREATED);
    }

    #[Route(path: '/{id}', name: 'delete_item', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteItem(int $id ): JsonResponse
    {
        $item = $this->service->find($id);
        if (!$item) {
            return new JsonResponse(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the user can delete this entity
        if (!$this->isGranted($this->DELETE_ACTION, $item)) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $this->service->delete($item);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '', name: 'update_item', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function createUpdateItem(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        Security $security
    ): JsonResponse
    {
        $data = $request->toArray();

        $id = $data['id'] ?? null;
        if (!$id) {
            $class = $this->service->getEntityClass();
            $item = new $class();
        } else {
            $item = $this->service->find($id);
        }

        if (!$item) {
            return new JsonResponse(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the user can update this entity
        if ($id) {
            if (!$this->isGranted($this->UPDATE_ACTION, $item)) {
                return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
            }
        } else {
            // If creating a new item, check create permission
            if (!$this->isGranted($this->CREATE_ACTION, $item)) {
                return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
            }
        }
        try {
            // Deserialize with group
            $item = $serializer->deserialize(
                json_encode($data),
                get_class($item),
                'json',
                [
                    'groups' => $this->getGroupPrefix() . ':update',
                    'object_to_populate' => $item
                ]
            );
        } catch (NotEncodableValueException $e) {
            return new JsonResponse(['error' => 'Invalid request data'], Response::HTTP_BAD_REQUEST);
        }

        // Set the authenticated user as the updater
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        if (method_exists($item, 'setUpdatedBy')) {
            $item->setUpdatedBy($user);
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
            return new JsonResponse(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
        }
        // Save entity
        $this->service->save($item);

        return new JsonResponse(['message' => 'Item updated'], Response::HTTP_OK);
    }
}
