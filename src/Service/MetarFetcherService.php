<?php

namespace App\Service;

use App\Repository\AirportRepository;
use App\Service\Metar\Provider\MetarProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * @extends AbstractRestService<AirportRepository>
 */
readonly class MetarFetcherService
{
    /**
     * @param iterable<MetarProviderInterface> $metarProviders
     */
    public function __construct(
        #[AutowireIterator('app.metar_provider')]
        private iterable $metarProviders
    ) {}


    public function fetch(string $icao): string
    {
        foreach ($this->metarProviders as $provider) {
            try {
                return $provider->fetch($icao);
            } catch (\Throwable $e) {
                // log or continue
                continue;
            }
        }

        throw new \RuntimeException('All METAR sources failed.');
    }
}
