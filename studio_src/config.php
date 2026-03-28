<?php
if (!defined('STUDIO_AUTH')) {
    http_response_code(403);
    exit('No direct access.');
}

// ── Error reporting ──────────────────────────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// ── PIN ────────────────────────────────────────────────────────────────────
// The PIN is stored only as a bcrypt hash — never in plaintext.
// To set or change the PIN, run setup.php once, then delete it.
define('PIN_HASH_FILE',     __DIR__ . '/../studio_data/pin.hash');

// ── Session ────────────────────────────────────────────────────────────────
define('SESSION_NAME',      'studio_sid');
define('SESSION_LIFETIME',  3600);   // idle timeout in seconds (1 hour)
define('SESSION_REGEN_TTL', 900);    // rotate session ID every 15 minutes

// ── Rate limiting ──────────────────────────────────────────────────────────
define('MAX_ATTEMPTS',      5);
define('LOCKOUT_SECONDS',   3600);   // 1-hour lockout after MAX_ATTEMPTS failures
define('RATE_LIMIT_DIR',    __DIR__ . '/../studio_data/ratelimit/');

// ── CSRF ───────────────────────────────────────────────────────────────────
define('CSRF_FIELD',        '_csrf');

// ── Helpers ────────────────────────────────────────────────────────────────
function getPinHash(): string {
    static $cached = null;
    if ($cached !== null) return $cached;

    if (!file_exists(PIN_HASH_FILE)) {
        http_response_code(503);
        exit('Studio is not configured. Please run setup.php first.');
    }
    $hash = trim((string) file_get_contents(PIN_HASH_FILE));
    if ($hash === '') {
        http_response_code(503);
        exit('PIN hash file is empty. Please run setup.php again.');
    }
    $cached = $hash;
    return $hash;
}

function ensureCsrf(): string {
    if (empty($_SESSION[CSRF_FIELD])) {
        $_SESSION[CSRF_FIELD] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_FIELD];
}
