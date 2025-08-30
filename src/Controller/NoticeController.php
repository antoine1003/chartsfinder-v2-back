<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserNotice;
use App\Repository\UserNoticeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '', name: 'notices_')]
class NoticeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly UserNoticeRepository $userNoticeRepository,
    )
    {
    }
    // api/user-notices
    #[Route(path: '/api/notices/unread', name: 'notices_list_unread', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }


        $dismissedNotices = $this->userNoticeRepository->getUnreadNoticesForUser($user);

        $json = $this->serializer->serialize($dismissedNotices, 'json', ['groups' => ['notice:list']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }


    #[Route(path: '/api/notices/{id}/dismiss', name: 'notices_dismiss', methods: ['POST'])]
    public function dismiss(
        UserNotice $notice,
    ): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $user->dismissNotice($notice);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
