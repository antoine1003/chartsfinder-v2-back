<?php

namespace App\Service;

use App\Dto\RegisterDto;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Event\UserRegisteredEvent;
use App\Exception\EmailAlreadyExistsException;
use App\Exception\EmailNotValidatedException;
use App\Repository\AirportRepository;
use App\Repository\UserRepository;
use App\Service\Security\GoogleIdTokenVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends AbstractRestService<AirportRepository>
 */
class AuthService
{
    public function __construct(
        protected EntityManagerInterface   $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UserRepository    $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly GoogleIdTokenVerifier $verifier,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly RefreshTokenManagerInterface $refreshTokenManager
    )
    {
    }


    /**
     * @throws RandomException
     * @throws EmailAlreadyExistsException
     */
    function register(RegisterDto $registerDto): User
    {
        $user = $this->userRepository->findOneBy(['email' => $registerDto->getEmail()]);
        if ($user) {
            throw new EmailAlreadyExistsException();
        }

        $user = new User();
        $user->setEmail($registerDto->getEmail());
        // hash the password
        $password = $this->passwordHasher->hashPassword($user, $registerDto->getPassword());
        $user->setPassword($password);
        $user->setRoles(['ROLE_USER']);
        $user->setIsEmailValidated(false);
        $user->setEmailValidationToken(bin2hex(random_bytes(64)));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Dispatch the user registered event
        $event = new UserRegisteredEvent($user);
        $this->dispatcher->dispatch($event, UserRegisteredEvent::NAME);

        return $user;
    }


    function registerGoogle(string $token): array
    {
        if (!$token) {
            throw new \InvalidArgumentException('Missing idToken');
        }

        $payload = $this->verifier->verify($token);

        // Optional: further checks
        if (($payload['email_verified'] ?? false) !== true) {
            // If email is not verified, you can return an error or handle it as needed
            throw new EmailNotValidatedException();
        }

        $googleId = $payload['sub'];
        $email    = $payload['email'] ?? null;

        // Find or create user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleId])
            ?? ($email ? $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]) : null);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setGoogleId($googleId);
            $user->setRoles(['ROLE_USER']);
            $user->setIsEmailValidated(true);
            // set other profile fields if you want: name, avatar, etc.
            $this->entityManager->persist($user);

            // Dispatch the user registered event
            $event = new UserRegisteredEvent($user);
            $this->dispatcher->dispatch($event, UserRegisteredEvent::NAME);

        } else {
            // ensure linkage for returning users
            if (method_exists($user, 'setGoogleId') && !$user->getGoogleId()) {
                $user->setGoogleId($googleId);
                $user->setIsEmailValidated(true);
                $user->setEmailValidationToken(null);
            }
        }
        $this->entityManager->flush();

        // Issue YOUR app JWT
        $jwt = $this->jwtManager->create($user);
        $refreshTokenString = bin2hex(random_bytes(64));

        // Create and save RefreshToken entity
        $refreshToken = new RefreshToken();
        $refreshToken->setRefreshToken($refreshTokenString);
        $refreshToken->setUsername($user->getUserIdentifier());
        $refreshToken->setValid((new \DateTime())->modify('+1 month')); // Match your TTL config

        $this->refreshTokenManager->save($refreshToken);

        return [
            'token' => $jwt,
            'refresh_token' => $refreshTokenString,
        ];
    }

    public function deleteAccount(User $user): void
    {
        // Remove associated refresh tokens
        $refreshTokens = $this->entityManager->getRepository(RefreshToken::class)
            ->findBy(['username' => $user->getUserIdentifier()]);

        foreach ($refreshTokens as $token) {
            $this->refreshTokenManager->delete($token);
        }

        // Remove the user
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
