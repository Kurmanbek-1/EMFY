<?php

namespace AmoCrm;

use GuzzleHttp\Client;

class Auth
{
    private $config;
    private $client;
    private $tokenFile;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client(['base_uri' => 'https://' . $config['domain']]);
        $this->tokenFile = $config['token_file'];
    }

    public function getAccessToken(): ?string
    {
        $tokens = $this->loadTokens();
        if (!$tokens) {
            return null;
        }
        if ($this->isTokenExpired($tokens)) {
            $tokens = $this->refreshToken($tokens['refresh_token']);
        }
        return $tokens['access_token'] ?? null;
    }

    public function saveTokens(array $tokens): void
    {
        file_put_contents($this->tokenFile, json_encode($tokens));
    }

    public function loadTokens(): ?array
    {
        if (!file_exists($this->tokenFile)) {
            return null;
        }
        $data = json_decode(file_get_contents($this->tokenFile), true);
        return $data ?: null;
    }

    public function isTokenExpired(array $tokens): bool
    {
        return isset($tokens['expires_at']) && $tokens['expires_at'] < time();
    }

    public function refreshToken(string $refreshToken): array
    {
        $response = $this->client->post('/oauth2/access_token', [
            'json' => [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'redirect_uri' => $this->config['redirect_uri'],
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        $data['expires_at'] = time() + $data['expires_in'];
        $this->saveTokens($data);
        return $data;
    }

    public function fetchTokenByCode(string $code): array
    {
        $response = $this->client->post('/oauth2/access_token', [
            'json' => [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->config['redirect_uri'],
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        $data['expires_at'] = time() + $data['expires_in'];
        $this->saveTokens($data);
        return $data;
    }
} 