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


class RefreshTokenController extends AbstractController
{
    public function __construct(
    )
    {
    }
}
