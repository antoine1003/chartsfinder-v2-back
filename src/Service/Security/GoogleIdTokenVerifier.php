<?php
// src/Security/GoogleIdTokenVerifier.php
namespace App\Service\Security;

use Google_Client;


final readonly class GoogleIdTokenVerifier
{
    public function __construct(private string $clientId) {}

    public function verify(string $idToken): array
    {
        $client = new Google_Client(['client_id' => $this->clientId]);
        $payload = $client->verifyIdToken($idToken);
        if ($payload === false) {
            throw new \RuntimeException('Invalid Google ID token');
        }
        return $payload; // ['sub','email','email_verified','iss','aud','exp',...]
    }
}
