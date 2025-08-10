<?php

namespace App\Service\Security;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class CaptchaVerifier
{
    public function __construct(
        private HttpClientInterface $http,
        private string              $secret
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function verify(string $token, ?string $ip = null): bool
    {
        $response = $this->http->request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $this->secret,
                'response' => $token,
                'remoteip' => $ip,
            ],
        ]);

        $data = $response->toArray(false);
        return ($data['success'] ?? false) === true;
    }
}
