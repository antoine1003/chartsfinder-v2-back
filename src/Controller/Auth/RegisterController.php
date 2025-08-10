<?php

namespace App\Controller\Auth;

use App\Dto\RegisterDto;
use App\Repository\UserRepository;
use App\Service\AirportRestService;
use App\Service\AuthService;
use App\Service\Security\CaptchaVerifier;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/register', name: 'register')]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CaptchaVerifier $captchaVerifier,
        private readonly LoggerInterface $logger,
    )
    {
    }

    // Register a new user

    /**
     * @throws RandomException
     */
    #[Route(path: '', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterDto $registerDto,
        Request $request,
    ): JsonResponse {
        // Here you would typically handle user registration logic
        // For example, validate the data, create a user entity, and persist it to the database

        // Verify the captcha token
        $userIp = $request->getClientIp();
        if (!$this->captchaVerifier->verify($registerDto->getCaptchaToken(), $userIp)) {
            return new JsonResponse(['error' => 'badCaptcha'], Response::HTTP_BAD_REQUEST);
        }
        try {
            $this->authService->register($registerDto);
        } catch (\Exception $e) {
            $this->logger->error('Registration error: ' . $e->getMessage(), ['exception' => $e]);
            // Handle exceptions, such as email already exists
            if ($e instanceof \App\Exception\EmailAlreadyExistsException) {
                return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
            }
            throw $e;
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
