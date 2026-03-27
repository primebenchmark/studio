<?php
define('STUDIO_AUTH', 1);
require __DIR__ . '/../studio_src/config.php';
require __DIR__ . '/../studio_src/session.php';

studioSessionStart();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$raw  = (string) file_get_contents('php://input');
$body = json_decode($raw, true);
$csrf = isset($body['csrf']) ? (string) $body['csrf'] : '';

$sessionCsrf = $_SESSION[CSRF_FIELD] ?? '';
if ($sessionCsrf === '' || !hash_equals($sessionCsrf, $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'invalid_request']);
    exit;
}

// Invalidate the session cookie
$params = session_get_cookie_params();
setcookie(
    session_name(), '', time() - 42000,
    $params['path'], $params['domain'],
    $params['secure'], $params['httponly']
);

$_SESSION = [];
session_destroy();

echo json_encode(['ok' => true]);
