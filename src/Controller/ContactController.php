<?php

namespace App\Controller;

use App\Service\ContactService;
use App\Service\Security\CaptchaVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/contact', name: 'contact')]
class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContactService $contactService,
        private readonly CaptchaVerifier $captchaVerifier,
    )
    {
    }

    #[Route(path: '', methods: ['POST'])]
    public function sendContactEmail(
        Request $request
    ): JsonResponse
    {
        $data = $request->toArray();
        $subject = $data['subject'] ?? '';
        $email = $data['email'] ?? '';
        $body = $data['body'] ?? '';
        $token = $data['captchaToken'] ?? '';

        if (empty($subject) || empty($email) || empty($body) || empty($token)) {
            return new JsonResponse(['error' => 'All fields are required'], Response::HTTP_BAD_REQUEST);
        }

        $userIp = $request->getClientIp();
        if (!$this->captchaVerifier->verify($token, $userIp)) {
            return new JsonResponse(['error' => 'badCaptcha'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->contactService->sendContactEmail($subject, $email, $body);
            return new JsonResponse(['status' => 'Email sent successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }
}
