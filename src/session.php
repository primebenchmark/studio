<?php
if (!defined('STUDIO_AUTH')) {
    http_response_code(403);
    exit('No direct access.');
}

/**
 * Bootstrap a hardened PHP session.
 * Must be called before any output.
 */
function studioSessionStart(): void {
    // Harden session cookie: HttpOnly, SameSite=Strict, Secure (when on HTTPS)
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

    ini_set('session.use_strict_mode',        '1'); // reject unknown session IDs
    ini_set('session.use_only_cookies',       '1'); // no session ID in URLs
    ini_set('session.cookie_httponly',        '1');
    ini_set('session.cookie_secure',          $secure ? '1' : '0');
    ini_set('session.cookie_samesite',        'Strict');
    ini_set('session.gc_maxlifetime',         (string) SESSION_LIFETIME);
    ini_set('session.sid_length',             '48');
    ini_set('session.sid_bits_per_character', '6');

    session_set_cookie_params([
        'lifetime' => 0,          // session cookie (expires on browser close)
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_name(SESSION_NAME);
    session_start();

    // Rotate session ID every SESSION_REGEN_TTL seconds to limit fixation window
    if (!empty($_SESSION['_regen_at']) && time() > $_SESSION['_regen_at']) {
        session_regenerate_id(true);
        $_SESSION['_regen_at'] = time() + SESSION_REGEN_TTL;
    } elseif (empty($_SESSION['_regen_at'])) {
        $_SESSION['_regen_at'] = time() + SESSION_REGEN_TTL;
    }
}

/**
 * Returns true if the current session has a valid, non-expired authentication.
 * Automatically destroys expired sessions.
 */
function isAuthenticated(): bool {
    if (empty($_SESSION['authenticated'])) {
        return false;
    }
    // Enforce idle timeout
    $expires = $_SESSION['expires_at'] ?? 0;
    if (time() > $expires) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        return false;
    }
    // Slide the expiry window on each authenticated request
    $_SESSION['expires_at'] = time() + SESSION_LIFETIME;
    return true;
}
