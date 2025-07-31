<?php

namespace App\Controller;

use App\Service\MetarFetcherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/metar', name: 'metar')]
class MetarController extends AbstractController
{

    #[Route(path: '/{icao}', name: '_metar', methods: ['GET'])]
    public function getMetar(string $icao, MetarFetcherService $fetcher): JsonResponse
    {
        try {
            $metar = $fetcher->fetch(strtoupper($icao));
            return $this->json([
                'icao' => $icao,
                'metar' => $metar,
            ]);
        } catch (\RuntimeException $e) {
            return $this->json([
                'error' => 'METAR unavailable',
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}
