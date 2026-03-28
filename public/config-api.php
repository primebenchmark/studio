<?php
define('STUDIO_AUTH', 1);
require __DIR__ . '/../studio_src/config.php';
require __DIR__ . '/../studio_src/session.php';

studioSessionStart();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

$configFile = __DIR__ . '/../studio_data/config.json';

// GET — load config
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($configFile)) {
        $data = json_decode((string) file_get_contents($configFile), true);
        echo json_encode(['ok' => true, 'config' => is_array($data) ? $data : null]);
    } else {
        echo json_encode(['ok' => true, 'config' => null]);
    }
    exit;
}

// POST — save config
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    $config = $body['config'] ?? null;
    if (!is_array($config)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'invalid_config']);
        exit;
    }

    // Sanitize config: only allow known keys
    $clean = [];
    if (isset($config['numCards'])) $clean['numCards'] = max(1, min(12, (int) $config['numCards']));
    if (isset($config['layout']) && in_array($config['layout'], ['column', 'row', 'grid-2', 'grid-3'], true)) {
        $clean['layout'] = $config['layout'];
    }
    if (isset($config['cardWidth'])) $clean['cardWidth'] = max(80, min(800, (int) $config['cardWidth']));
    if (isset($config['cardHeight'])) $clean['cardHeight'] = max(40, min(600, (int) $config['cardHeight']));

    if (isset($config['cards']) && is_array($config['cards'])) {
        $clean['cards'] = [];
        foreach (array_slice($config['cards'], 0, 12) as $card) {
            if (!is_array($card)) continue;
            $c = [];
            $c['label'] = mb_substr((string) ($card['label'] ?? ''), 0, 100);
            $href = (string) ($card['href'] ?? '#');
            if (preg_match('/^(javascript|data|vbscript):/i', $href)) $href = '#';
            $c['href'] = mb_substr($href, 0, 500);
            if (isset($card['width'])) $c['width'] = max(40, min(800, (int) $card['width']));
            if (isset($card['height'])) $c['height'] = max(30, min(600, (int) $card['height']));
            $clean['cards'][] = $c;
        }
    }

    $dir = dirname($configFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }

    if (file_put_contents($configFile, json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'write_failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
