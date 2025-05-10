<?php

namespace AmoCrm;

use GuzzleHttp\Client;

class NoteService
{
    private $config;
    private $auth;
    private $client;

    public function __construct(array $config, Auth $auth)
    {
        $this->config = $config;
        $this->auth = $auth;
        $this->client = new Client(['base_uri' => 'https://' . $config['domain']]);
    }

    public function addNote(string $entityType, int $entityId, string $text): bool
    {
        $accessToken = $this->auth->getAccessToken();
        if (!$accessToken) {
            return false;
        }
        $url = "/api/v4/{$entityType}/{$entityId}/notes";
        $body = [
            [
                'note_type' => 'common',
                'params' => [
                    'text' => $text
                ]
            ]
        ];
        $response = $this->client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $body
        ]);
        return $response->getStatusCode() === 200 || $response->getStatusCode() === 201;
    }
} 