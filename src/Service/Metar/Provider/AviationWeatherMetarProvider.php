<?php
namespace App\Service\Metar\Provider;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AviationWeatherMetarProvider implements MetarProviderInterface
{
    public function __construct(private HttpClientInterface $httpClient) {}
    private const API_URL = 'https://aviationweather.gov/api/data/metar?ids=%s&format=json';

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetch(string $icao): string
    {
        $url = sprintf(self::API_URL, $icao);
        $response = $this->httpClient->request('GET', $url);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('AviationWether source failed');
        }

        $json = $response->toArray(false);
        if (empty($json) || !isset($json[0]['rawOb'])) {
            throw new \RuntimeException('No METAR data returned from AviationWeather');
        }
        return $json[0]['rawOb'];
    }

    public function getName(): string
    {
        return 'VATSIM METAR Provider';
    }
}
