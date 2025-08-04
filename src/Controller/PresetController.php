<?php

namespace App\Controller;

use App\Dto\PresetDto;
use App\Entity\Preset;
use App\Entity\User;
use App\Security\Voter\PresetVoter;
use App\Service\PresetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api/presets', name: 'presets')]
class PresetController extends AbstractController
{
    public function __construct(
        private readonly PresetService $presetService
    )
    {
    }

    #[Route(path: '', name: '_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] PresetDto $presetDto,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $item = $this->presetService->createOrUpdate($presetDto);
        if (!$item) {
            return new JsonResponse(['error' => 'Preset not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $json = $serializer->serialize($item, 'json', ['groups' => ['preset:detail']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: '_one', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[ISGranted(PresetVoter::VIEW, subject: 'preset')]
    public function getOne(Preset $preset, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($preset, 'json', ['groups' => ['preset:detail']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route(path: '/mine', name: '_get_mine', methods: ['GET'])]
    public function getMine(SerializerInterface $serializer, Security $security): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = $security->getUser();
        $presets = $user->getPresets();

        $json = $serializer->serialize($presets, 'json', ['groups' => ['preset:detail']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: '_delete', methods: ['DELETE'])]
    #[ISGranted(PresetVoter::DELETE, subject: 'preset')]
    public function delete(Preset $preset): JsonResponse
    {
        $this->presetService->delete($preset);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
