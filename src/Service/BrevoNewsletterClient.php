<?php
// src/Service/BrevoNewsletterClient.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoNewsletterClient
{
    private string $apiKey;
    private int $listId;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        string $brevoApiKey,
        int $brevoListId
    ) {
        $this->apiKey = $brevoApiKey;
        $this->listId = $brevoListId;
    }

    private function client() {
        return $this->httpClient->withOptions([
            'base_uri' => 'https://api.brevo.com/v3/',
            'headers' => [
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ],
            'timeout' => 10,
        ]);
    }

    /** Returns true if email exists and is in list */
    public function isSubscribed(string $email): bool
    {
        $res = $this->client()->request('GET', 'contacts/'.rawurlencode($email));
        if ($res->getStatusCode() === 404) {
            return false;
        }
        $data = $res->toArray(false);
        $lists = $data['listIds'] ?? [];
        return in_array($this->listId, $lists, true);
    }

    /** Create or update contact & add to the list */
    public function subscribe(string $email, array $attributes = []): void
    {
        // Try create (idempotent upsert by POST /contacts with updateEnabled=true)
        $payload = [
            'email' => $email,
            'listIds' => [$this->listId],
            'updateEnabled' => true
        ];
        if (!empty($attributes)) {
            $payload['attributes'] = (object)$attributes;
        }
        $res = $this->client()->request('POST', 'contacts', [
            'json' => $payload
        ]);

        $code = $res->getStatusCode();
        if ($code >= 400) {
            throw new \RuntimeException('Brevo subscribe failed: '.$res->getContent(false));
        }
    }

    /** Remove from list (keeps contact in Brevo, just unsubscribes from this list) */
    public function unsubscribe(string $email): void
    {
        $res = $this->client()->request(
            'POST',
            'contacts/lists/'.$this->listId.'/contacts/remove',
            ['json' => ['emails' => [$email]]]
        );
        if ($res->getStatusCode() >= 400) {
            throw new \RuntimeException('Brevo unsubscribe failed: '.$res->getContent(false));
        }
    }
}
