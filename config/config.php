<?php


return [
    'client_id' => $_ENV['AMO_CLIENT_ID'] ?? $_SERVER['AMO_CLIENT_ID'],
    'client_secret' => $_ENV['AMO_CLIENT_SECRET'] ?? $_SERVER['AMO_CLIENT_SECRET'],
    'redirect_uri' => $_ENV['AMO_REDIRECT_URI'] ?? $_SERVER['AMO_REDIRECT_URI'],
    'domain' => $_ENV['AMO_DOMAIN'] ?? $_SERVER['AMO_DOMAIN'],
    'token_file' => __DIR__ . '/../token.json',
];
