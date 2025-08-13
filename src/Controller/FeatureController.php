<?php

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\User;
use App\Service\FeatureRestService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/features', name: 'features')]
class FeatureController extends AbstractRestController
{
    public function __construct(
        FeatureRestService $service
    )
    {
        parent::__construct($service);
    }

    #[Route('/{id}/vote', name: '_vote', methods: ['POST'])]
    public function vote(
        Feature $feature,
        Request $request
    ): JsonResponse {

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $type = $data['type'] ?? null;

        try {
            $result = $this->service->handleVote($feature, $user, $type);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return $this->json([
            'id' => $feature->getId(),
            ...$result
        ]);
    }

    function getGroupPrefix(): string
    {
        return 'feature';
    }
}
