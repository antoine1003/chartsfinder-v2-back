<?php

namespace App\Serializer;

use App\Entity\Airport;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AirportNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
        private Security $security
    ) {}

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Airport;
    }

    /**
     * @param mixed $data
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        // Only add 'isFavorite' if 'is_search' flag is set
        if (!empty($context['is_search'])) {
            $user = $this->security->getUser();

            if ($user instanceof User) {
                $data['isFavorite'] = $user->getFavoriteAirports()->contains($object);
            } else {
                $data['isFavorite'] = false;
            }
        }

        return $data;
    }


    public function getSupportedTypes(?string $format): array
    {
        return [Airport::class => true];
    }
}
