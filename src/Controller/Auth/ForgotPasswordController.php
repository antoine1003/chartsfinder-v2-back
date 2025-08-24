<?php

namespace App\Controller\Auth;

use App\Entity\PasswordResetToken;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use App\Service\Auth\PasswordResetMailer;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
class ForgotPasswordController extends AbstractController
{
    /**
     * @throws RandomException
     */
    #[Route('/forgot-password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        UserRepository $users,
        PasswordResetTokenRepository $tokens,
        EntityManagerInterface $em,
        PasswordResetMailer $mailer
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $email = trim((string)($payload['email'] ?? ''));

        // Always return success to avoid user enumeration
        $okResponse = $this->json(['success' => true]);

        if (!$email) return $okResponse;

        $user = $users->findOneBy(['email' => $email]);
        if (!$user) return $okResponse;

        // Generate token (random + short)
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        // Expire in 2 hours
        $expiresAt = (new \DateTimeImmutable())->modify('+2 hours');

        // Persist
        $reset = new PasswordResetToken($user, $token, $expiresAt);
        $em->persist($reset);
        $em->flush();

        // Send email
        $mailer->send($email, $token);

        return $okResponse;
    }

    #[Route('/reset-password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        PasswordResetTokenRepository $tokens,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $token = (string)($payload['token'] ?? '');
        $new = (string)($payload['password'] ?? '');

        if (!$token) {
            return $this->json(['error' => 'Invalid request'], 400);
        }

        /** @var PasswordResetToken|null $entry */
        $entry = $tokens->findOneBy(['token' => $token]);
        if (!$entry || !$entry->isValid()) {
            return $this->json(['error' => 'Invalid or expired token'], 400);
        }

        $user = $entry->getUser();
        $user->setPassword($hasher->hashPassword($user, $new));
        $entry->markUsed();

        $em->flush();

        return $this->json(['success' => true]);
    }
}
