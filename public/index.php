<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use AmoCrm\Auth;
use AmoCrm\WebhookHandler;
use AmoCrm\NoteService;


$config = require __DIR__ . '/../config/config.php';
$auth = new Auth($config);
$handler = new WebhookHandler();
$noteService = new NoteService($config, $auth);

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
} elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
    $data = $_POST;
} else {
    $data = null;
}

if (!$data) {
    http_response_code(400);
    echo 'No data';
    exit;
}

$result = $handler->handle($data);
if ($result) {
    $ok = $noteService->addNote($result['entity_type'], $result['entity_id'], $result['note']);
    if ($ok) {
        http_response_code(200);
        echo 'Note added';
    } else {
        http_response_code(500);
        echo 'Failed to add note';
    }
} else {
    http_response_code(200);
    echo 'No action';
}