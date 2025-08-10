<?php

namespace App\Controller\Auth;

use App\Dto\RegisterDto;
use App\Repository\UserRepository;
use App\Service\AirportRestService;
use App\Service\AuthService;
use App\Service\Security\CaptchaVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;


class VerifyEmailController extends AbstractController
{
    public function __construct(
    )
    {
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verify(
        string $token,
        UserRepository $userRepo,
        EntityManagerInterface $em,
        ParameterBagInterface $params,
    ): RedirectResponse
    {
        $user = $userRepo->findOneBy(['emailValidationToken' => $token]);

        $frontendUrl = $params->get('frontendUrl');

        if (!$user) {
            // redirect to the frontend with an error message
            return new RedirectResponse($frontendUrl . '/login?emailValidation=error');

        }

        $user->setEmailValidationToken(null);
        $user->setIsEmailValidated(true);

        $em->flush();

        return new RedirectResponse($frontendUrl . '/login?emailValidation=success');
    }
}
