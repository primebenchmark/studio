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

// Only POST is accepted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

// Parse JSON body
$raw  = (string) file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_request']);
    exit;
}

$pin  = isset($body['pin'])  ? (string) $body['pin']  : '';
$csrf = isset($body['csrf']) ? (string) $body['csrf'] : '';

// ── CSRF verification ───────────────────────────────────────────────────────
$sessionCsrf = $_SESSION[CSRF_FIELD] ?? '';
if ($sessionCsrf === '' || !hash_equals($sessionCsrf, $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'invalid_request']);
    exit;
}

// ── Rate-limit check ────────────────────────────────────────────────────────
// Use REMOTE_ADDR only — do NOT trust X-Forwarded-For unless behind a known proxy
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

rlCleanup(); // opportunistic stale-file cleanup

$lockedFor = rlLockedFor($ip);
if ($lockedFor > 0) {
    auditLog('login_locked', $ip, "retry_after={$lockedFor}s");
    http_response_code(429);
    header('Retry-After: ' . $lockedFor);
    echo json_encode(['ok' => false, 'error' => 'locked', 'retry_after' => $lockedFor]);
    exit;
}

// ── PIN format check ────────────────────────────────────────────────────────
// Accept 4–8 digit PINs; reject anything else before hitting bcrypt
if (!preg_match('/^\d{4,8}$/', $pin)) {
    rlRecordFailure($ip);
    echo json_encode(['ok' => false, 'error' => 'invalid']);
    exit;
}

// ── Verify PIN against stored hash ─────────────────────────────────────────
if (password_verify($pin, getPinHash())) {
    // Success: clear failures, regenerate session, issue auth flag
    auditLog('login_ok', $ip);
    rlReset($ip);
    session_regenerate_id(true);
    $_SESSION['authenticated'] = true;
    $_SESSION['expires_at']    = time() + SESSION_LIFETIME;
    $_SESSION['_regen_at']     = time() + SESSION_REGEN_TTL;
    // Rotate CSRF token so the pre-login token cannot be reused
    $_SESSION[CSRF_FIELD]      = bin2hex(random_bytes(32));

    echo json_encode(['ok' => true]);
} else {
    auditLog('login_fail', $ip);
    rlRecordFailure($ip);
    $remaining = rlLockedFor($ip);
    if ($remaining > 0) {
        auditLog('lockout', $ip, "locked for {$remaining}s");
        http_response_code(429);
        header('Retry-After: ' . $remaining);
        echo json_encode(['ok' => false, 'error' => 'locked', 'retry_after' => $remaining]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'invalid']);
    }
}
