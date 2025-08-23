<?php

namespace App\Controller\Auth;

use App\Exception\EmailAlreadyExistsException;
use App\Service\AuthService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/auth/google')]
class GoogleController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LoggerInterface $logger,
    )
    {
    }

    #[Route('', name: 'auth_google', methods: ['POST'])]
    public function google(
        Request $request
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true);
        $idToken = $data['token'] ?? null;

        try {
            $data =  $this->authService->registerGoogle($idToken);
        } catch (\Exception $e) {
            $this->logger->error('Registration error: ' . $e->getMessage(), ['exception' => $e]);
            // Handle exceptions, such as email already exists
            if ($e instanceof EmailAlreadyExistsException) {
                return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
            }
            throw $e;
        }
        return $this->json($data);
    }
}
