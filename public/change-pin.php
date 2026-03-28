<?php
define('STUDIO_AUTH', 1);
require __DIR__ . '/../studio_src/config.php';
require __DIR__ . '/../studio_src/session.php';
require __DIR__ . '/../studio_src/rate_limit.php';
require __DIR__ . '/../studio_src/audit_log.php';

studioSessionStart();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

$raw  = (string) file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_request']);
    exit;
}

// CSRF verification
$csrf = isset($body['csrf']) ? (string) $body['csrf'] : '';
$sessionCsrf = $_SESSION[CSRF_FIELD] ?? '';
if ($sessionCsrf === '' || !hash_equals($sessionCsrf, $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'invalid_request']);
    exit;
}

$currentPin = isset($body['current_pin']) ? (string) $body['current_pin'] : '';
$newPin     = isset($body['new_pin'])     ? (string) $body['new_pin']     : '';
$confirmPin = isset($body['confirm_pin']) ? (string) $body['confirm_pin'] : '';

// Rate limit PIN change attempts too
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$lockedFor = rlLockedFor($ip);
if ($lockedFor > 0) {
    http_response_code(429);
    header('Retry-After: ' . $lockedFor);
    echo json_encode(['ok' => false, 'error' => 'locked', 'retry_after' => $lockedFor]);
    exit;
}

// Validate current PIN
if (!preg_match('/^\d{4,8}$/', $currentPin) || !password_verify($currentPin, getPinHash())) {
    auditLog('pin_change_fail', $ip, 'wrong current PIN');
    rlRecordFailure($ip);
    echo json_encode(['ok' => false, 'error' => 'wrong_current_pin']);
    exit;
}

// Validate new PIN
if (!preg_match('/^\d{4,8}$/', $newPin)) {
    echo json_encode(['ok' => false, 'error' => 'invalid_new_pin']);
    exit;
}

if ($newPin !== $confirmPin) {
    echo json_encode(['ok' => false, 'error' => 'pins_dont_match']);
    exit;
}

// Hash and save new PIN
$hash = password_hash($newPin, PASSWORD_BCRYPT, ['cost' => 12]);
if (file_put_contents(PIN_HASH_FILE, $hash, LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'write_failed']);
    exit;
}
chmod(PIN_HASH_FILE, 0600);

auditLog('pin_change_ok', $ip);

// Clear rate limit on success
rlReset($ip);

// Rotate CSRF token
$_SESSION[CSRF_FIELD] = bin2hex(random_bytes(32));

echo json_encode(['ok' => true, 'csrf' => $_SESSION[CSRF_FIELD]]);
