<?php

namespace App\Controller\Admin;

use App\Controller\AbstractRestController;
use App\Service\Admin\NoticeRestService;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/admin/notices', name: 'admin_notice_')]
class NoticeController extends AbstractRestController
{
    public function __construct(
        NoticeRestService $service
    )
    {
        parent::__construct($service);
    }
    function getGroupPrefix(): string
    {
        return 'notice';
    }
}
