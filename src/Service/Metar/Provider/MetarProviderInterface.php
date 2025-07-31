<?php
namespace App\Service\Metar\Provider;

interface MetarProviderInterface
{
    /**
     * @throws \RuntimeException if the provider fails
     */
    public function fetch(string $icao): string;

    public function getName(): string;
}
