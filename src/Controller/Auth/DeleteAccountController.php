<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Service\AuthService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/account')]
class DeleteAccountController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LoggerInterface $logger,
    )
    {
    }
    #[Route(path: '', name: 'app_delete_account', methods: ['DELETE'])]
    public function deleteAccount(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $this->authService->deleteAccount($user);
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting account: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
