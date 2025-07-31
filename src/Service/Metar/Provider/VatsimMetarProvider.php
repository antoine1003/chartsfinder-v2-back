<?php
namespace App\Service\Metar\Provider;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VatsimMetarProvider implements MetarProviderInterface
{
    public function __construct(private HttpClientInterface $httpClient) {}
    private const API_URL = 'https://metar.vatsim.net/metar.php?id=%s';

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetch(string $icao): string
    {
        $url = sprintf(self::API_URL, $icao);
        // Only the METAR is returned, no JSON or XML wrapping
        $response = $this->httpClient->request('GET', $url);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('VATSIM source failed');
        }
        $content = $response->getContent(false); // disable exception on 4xx/5xx
        if (empty($content)) {
            throw new \RuntimeException('No METAR data returned from VATSIM');
        }
        return $content;
    }

    public function getName(): string
    {
        return 'VATSIM METAR Provider';
    }
}
