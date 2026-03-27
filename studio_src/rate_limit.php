<?php
if (!defined('STUDIO_AUTH')) {
    http_response_code(403);
    exit('No direct access.');
}

/**
 * Returns a filesystem-safe key for the given IP address.
 */
function _rlKey(string $ip): string {
    return RATE_LIMIT_DIR . hash('sha256', $ip) . '.json';
}

/**
 * Reads the rate-limit record for the given IP.
 * Returns ['attempts' => int, 'lockout_until' => int].
 */
function _rlRead(string $ip): array {
    $file = _rlKey($ip);
    if (!file_exists($file)) {
        return ['attempts' => 0, 'lockout_until' => 0];
    }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : ['attempts' => 0, 'lockout_until' => 0];
}

/**
 * Writes the rate-limit record for the given IP.
 */
function _rlWrite(string $ip, array $data): void {
    $dir = RATE_LIMIT_DIR;
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    file_put_contents(_rlKey($ip), json_encode($data), LOCK_EX);
}

/**
 * Returns the number of seconds remaining in a lockout, or 0 if not locked.
 */
function rlLockedFor(string $ip): int {
    $rec = _rlRead($ip);
    $remaining = $rec['lockout_until'] - time();
    return $remaining > 0 ? (int) $remaining : 0;
}

/**
 * Records a failed login attempt. Applies lockout if threshold reached.
 */
function rlRecordFailure(string $ip): void {
    $rec = _rlRead($ip);
    // If currently locked, don't increment (already locked)
    if ($rec['lockout_until'] > time()) {
        return;
    }
    $rec['attempts']++;
    if ($rec['attempts'] >= MAX_ATTEMPTS) {
        $rec['lockout_until'] = time() + LOCKOUT_SECONDS;
        $rec['attempts']      = 0; // reset counter so it's fresh after lockout
    }
    _rlWrite($ip, $rec);
}

/**
 * Clears the rate-limit record for the given IP after a successful login.
 */
function rlReset(string $ip): void {
    $file = _rlKey($ip);
    if (file_exists($file)) {
        unlink($file);
    }
}

/**
 * Deletes stale lockout files (older than 2x LOCKOUT_SECONDS).
 * Called opportunistically on login attempts to avoid accumulation.
 */
function rlCleanup(): void {
    // Run cleanup only ~10% of the time to avoid glob() on every login attempt
    if (random_int(1, 10) !== 1) return;

    $dir = RATE_LIMIT_DIR;
    if (!is_dir($dir)) return;
    $cutoff = time() - (LOCKOUT_SECONDS * 2);
    foreach (glob($dir . '*.json') ?: [] as $file) {
        if (filemtime($file) < $cutoff) {
            @unlink($file);
        }
    }
}
