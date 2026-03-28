<?php
if (!defined('STUDIO_AUTH')) {
    http_response_code(403);
    exit('No direct access.');
}

define('AUDIT_LOG_FILE', __DIR__ . '/../studio_data/audit.log');
define('AUDIT_LOG_MAX_SIZE', 1048576); // 1 MB — rotate when exceeded

/**
 * Appends a line to the audit log.
 *
 * @param string $event  Event type (e.g. login_success, login_fail, pin_change, lockout)
 * @param string $ip     Client IP address
 * @param string $detail Optional extra detail
 */
function auditLog(string $event, string $ip, string $detail = ''): void {
    $dir = dirname(AUDIT_LOG_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }

    // Simple rotation: rename current log when it exceeds max size
    if (file_exists(AUDIT_LOG_FILE) && filesize(AUDIT_LOG_FILE) > AUDIT_LOG_MAX_SIZE) {
        @rename(AUDIT_LOG_FILE, AUDIT_LOG_FILE . '.1');
    }

    $ts   = gmdate('Y-m-d\TH:i:s\Z');
    $line = sprintf("[%s] %-14s %-16s %s\n", $ts, $event, $ip, $detail);
    @file_put_contents(AUDIT_LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}
