<?php

namespace App\Controller;

use App\Service\AbstractRestService;
use App\Service\AirportRestService;
use App\Service\ChartRestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/charts', name: 'charts')]
class ChartController extends AbstractRestController
{
    public function __construct(
        ChartRestService $service
    )
    {
        parent::__construct($service);
    }

    function getGroupPrefix(): string
    {
        return 'chart';
    }
}
