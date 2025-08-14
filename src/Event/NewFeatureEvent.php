<?php

namespace App\Event;

use App\Entity\Feature;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class NewFeatureEvent extends Event
{
    public const NAME = 'feature.new';

    public function __construct(
        private readonly Feature $feature
    ) {}

    public function getFeature(): Feature
    {
        return $this->feature;
    }
}
