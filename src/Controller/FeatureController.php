<?php

namespace App\Controller;

use App\Entity\Enum\FeatureStatusEnum;
use App\Entity\Feature;
use App\Entity\User;
use App\Service\FeatureRestService;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/{id}/status', methods: ['PATCH'])]
    public function updateStatus(
        Feature $feature,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;

        if (!in_array($status, FeatureStatusEnum::getValues(), true)) {
            return $this->json(['error' => 'Invalid status'], 400);
        }

        $feature->setStatus($status);
        $em->flush();

        return $this->json(['success' => true]);
    }

    function getGroupPrefix(): string
    {
        return 'feature';
    }
}
