<?php

namespace App\Controller\Admin;

use App\Entity\ChartReport;
use App\Repository\ChartReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api/admin/chart-reports', name: 'admin_chart_reports')]
#[IsGranted('ROLE_ADMIN')]
class ChartReportController extends AbstractController
{
    public function __construct(
        private readonly ChartReportRepository $chartReportRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route(path: '', name: '_list', methods: ['GET'])]
    public function listChartReports(): JsonResponse
    {
        $reports = $this->chartReportRepository->findAll();
        $json = $this->serializer->serialize(
            $reports,
            'json',
            [
                'groups' => ['report:detail'],
                'datetime_format' => 'Y-m-d H:i:s',
            ]

        );
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}/resolve', name: '_resolve', methods: ['POST'])]
    public function resolve(ChartReport $chartReport): JsonResponse
    {
        $chartReport->setResolved(true);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
