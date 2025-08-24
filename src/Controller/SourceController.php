<?php

namespace App\Controller;

use App\Service\SourceRestService;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/sources', name: 'sources')]
class SourceController extends AbstractRestController
{
    public function __construct(
        SourceRestService $service
    )
    {
        parent::__construct($service);
    }

    function getGroupPrefix(): string
    {
        return 'source';
    }
}
