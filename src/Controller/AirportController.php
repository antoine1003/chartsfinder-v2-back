<?php

namespace App\Controller;

use App\Entity\Airport;
use App\Entity\User;
use App\Service\AirportRestService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/airports', name: 'airports')]
class AirportController extends AbstractRestController
{
    public function __construct(
        AirportRestService $service
    )
    {
        parent::__construct($service);
    }

    function getGroupPrefix(): string
    {
        return 'airport';
    }

    #[Route(path: '/{id}/favorite', name: '_favorite', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleFavorite(Airport $airport): JsonResponse
    {
        /**
         * @var Airport $airport
         */
        $airport = $this->service->toggleFavorite($airport);
        /** @var User $user */
        $user = $this->getUser();
        return new JsonResponse(['favorite' => $airport->isFavorite($user)]);
    }

    #[Route(path: '/favorites', name: '_favorites', methods: ['GET'])]
    public function getFavorites(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        return $this->json(
            $user->getFavoriteAirports(),
            Response::HTTP_OK,
            [],
            ['groups' => $this->getGroupPrefix() . ':list']
        );
    }
}
