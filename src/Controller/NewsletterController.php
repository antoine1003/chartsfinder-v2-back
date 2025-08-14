<?php

namespace App\Controller;

use App\Service\BrevoNewsletterClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/api/newsletter')]
class NewsletterController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly BrevoNewsletterClient $brevo
    ) {}

    #[Route('', name: 'newsletter_get', methods: ['GET'])]
    public function getStatus(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
        if (!$email) {
            return $this->json(['error' => 'No email on user'], 400);
        }

        try {
            $subscribed = $this->brevo->isSubscribed($email);
            return $this->json(['subscribed' => $subscribed]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Brevo error', 'details' => $e->getMessage()], 502);
        }
    }

    #[Route('', name: 'newsletter_put', methods: ['PUT'])]
    public function setStatus(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
        if (!$email) {
            return $this->json(['error' => 'No email on user'], 400);
        }

        $body = json_decode($request->getContent(), true) ?? [];
        $subscribed = (bool)($body['subscribed'] ?? false);

        try {
            if ($subscribed) {
                // Optional: pass attributes to Brevo (e.g., first name)
                $attrs = [];
                if (method_exists($user, 'getFirstName') && $user->getFirstName()) {
                    $attrs['FIRSTNAME'] = $user->getFirstName();
                }
                if (method_exists($user, 'getLastName') && $user->getLastName()) {
                    $attrs['LASTNAME'] = $user->getLastName();
                }
                $this->brevo->subscribe($email, $attrs);
            } else {
                $this->brevo->unsubscribe($email);
            }

            return $this->json(['success' => true, 'subscribed' => $subscribed]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Brevo error', 'details' => $e->getMessage()], 502);
        }
    }
}

