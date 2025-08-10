<?php

namespace App\Service;

use App\Dto\PresetDto;
use App\Dto\RegisterDto;
use App\Entity\Airport;
use App\Entity\Preset;
use App\Entity\User;
use App\Event\UserRegisteredEvent;
use App\Exception\EmailAlreadyExistsException;
use App\Repository\AirportRepository;
use App\Repository\PresetRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\SecurityBundle\Security;
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
        private readonly UserPasswordHasherInterface $passwordHasher
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
}
