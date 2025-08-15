<?php

namespace App\Controller;

use App\Entity\Enum\FeatureStatusEnum;
use App\Entity\Feature;
use App\Entity\User;
use App\Service\FeatureRestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/sources', name: 'sources')]
class SourceController extends AbstractRestController
{
    public function __construct(
        FeatureRestService $service
    )
    {
        parent::__construct($service);
    }

    function getGroupPrefix(): string
    {
        return 'source';
    }
}
