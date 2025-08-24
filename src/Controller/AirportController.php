<?php

namespace App\Controller;

use App\Service\AirportRestService;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/airports', name: 'airports')]
class AirportController extends AbstractRestController
{
    public function __construct(
        AirportRestService $service
    )
    {
        parent::__construct($service);
    }

    function getGroupPrefix(): string
    {
        return 'airport';
    }
}
