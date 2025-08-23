<?php

namespace App\Controller;

use App\Entity\Chart;
use App\Entity\User;
use App\Exception\ChartAlreadyReportedException;
use App\Service\ChartRestService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route(path: '/{id}/report', name: '_report', methods: ['POST'])]
    public function reportChart(
        Chart $chart,
    ): JsonResponse {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        try {
            $this->service->reportChart($chart);
        } catch (ChartAlreadyReportedException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode() ?: Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
