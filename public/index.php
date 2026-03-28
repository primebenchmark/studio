<?php
define('STUDIO_AUTH', 1);
require __DIR__ . '/../studio_src/config.php';
require __DIR__ . '/../studio_src/session.php';

studioSessionStart();
ensureCsrf();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store, no-cache, must-revalidate');

$authed  = isAuthenticated();
$csrf    = $_SESSION[CSRF_FIELD];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Studio</title>
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>" data-authed="<?= $authed ? '1' : '0' ?>" />
  <script>
    (function(){try{var t=localStorage.getItem('studio-theme')||'dark';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
  </script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: linear-gradient(160deg, #f5f3f0 0%, #ebe7e2 50%, #f0ece8 100%);
      --text: #2a2520;
      --muted: #6a6560;
      --pin-dot-border: #b0a898;
      --pin-dot-filled: #2a2520;
      --pin-dot-error: #c0392b;
      --btn-bg: #f2efe8;
      --btn-border: #d9d4c0;
      --btn-hover-bg: #ede9e0;
      --btn-hover-border: #c9b98a;
      --btn-active-bg: #e4dfd5;
      --card-bg: #f2efe8;
      --card-border: #d9d4c0;
      --card-hover-bg: #ede9e0;
      --card-hover-border: #c9b98a;
      --error-color: #c0392b;
      --accent: #8b6914;
    }

    [data-theme="dark"] {
      --bg: linear-gradient(160deg, #0d0d0d 0%, #1a1520 50%, #0d1117 100%);
      --text: #e0dcd4;
      --muted: #7a7570;
      --pin-dot-border: #5a5550;
      --pin-dot-filled: #e0dcd4;
      --pin-dot-error: #e74c3c;
      --btn-bg: rgba(255,255,255,0.06);
      --btn-border: rgba(255,255,255,0.1);
      --btn-hover-bg: rgba(255,255,255,0.1);
      --btn-hover-border: rgba(201,169,110,0.5);
      --btn-active-bg: rgba(255,255,255,0.14);
      --card-bg: rgba(255,255,255,0.04);
      --card-border: rgba(255,255,255,0.08);
      --card-hover-bg: rgba(255,255,255,0.08);
      --card-hover-border: rgba(201,169,110,0.5);
      --error-color: #e74c3c;
      --accent: #c9a96e;
    }

    body {
      min-height: 100vh;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: var(--text);
      transition: background 0.3s ease, color 0.3s ease;
    }

    /* Admin button (top-left corner) */
    .admin-btn {
      position: fixed;
      top: 16px;
      left: 16px;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid var(--btn-border);
      background: var(--btn-bg);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s, border-color 0.2s;
      color: var(--muted);
      text-decoration: none;
    }
    .admin-btn:hover {
      background: var(--btn-hover-bg);
      border-color: var(--btn-hover-border);
      color: var(--text);
    }

    /* Theme toggle (top-right corner) */
    .page-controls {
      position: fixed;
      top: 16px;
      right: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .logout-btn {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      border: 1px solid var(--btn-border);
      background: var(--btn-bg);
      cursor: pointer;
      font-size: 15px;
      line-height: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s, border-color 0.2s;
      color: var(--muted);
    }
    .logout-btn:hover {
      background: var(--btn-hover-bg);
      border-color: var(--btn-hover-border);
      color: var(--text);
    }

    .theme-toggle {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      border: 1px solid var(--btn-border);
      background: var(--btn-bg);
      cursor: pointer;
      font-size: 16px;
      line-height: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s, border-color 0.2s;
    }
    .theme-toggle:hover {
      background: var(--btn-hover-bg);
      border-color: var(--btn-hover-border);
    }

    /* PIN SCREEN */
    #pin-screen {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 24px;
    }

    #pin-screen h2 {
      font-size: 15px;
      font-weight: 500;
      color: var(--muted);
      letter-spacing: 0.05em;
    }

    .pin-dots {
      display: flex;
      gap: 14px;
    }

    .pin-dot {
      width: 14px;
      height: 14px;
      border-radius: 50%;
      border: 1.5px solid var(--pin-dot-border);
      background: transparent;
      transition: background 0.15s ease, border-color 0.15s ease;
    }

    .pin-dot.filled {
      background: var(--pin-dot-filled);
      border-color: var(--pin-dot-filled);
    }

    .pin-dot.error {
      background: var(--pin-dot-error);
      border-color: var(--pin-dot-error);
    }

    .pin-pad {
      display: grid;
      grid-template-columns: repeat(3, 72px);
      grid-template-rows: repeat(4, 72px);
      gap: 10px;
    }

    .pin-btn {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      border: 1px solid var(--btn-border);
      background: var(--btn-bg);
      font-size: 22px;
      color: var(--text);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.1s ease, box-shadow 0.1s ease, border-color 0.1s;
      box-shadow: 0 2px 6px rgba(0,0,0,0.06);
      user-select: none;
    }

    .pin-btn:hover {
      background: var(--btn-hover-bg);
      border-color: var(--btn-hover-border);
      box-shadow: 0 4px 12px rgba(0,0,0,0.10);
    }

    .pin-btn:active {
      background: var(--btn-active-bg);
      box-shadow: none;
    }

    .pin-btn.empty {
      background: transparent;
      border: none;
      box-shadow: none;
      cursor: default;
      pointer-events: none;
    }

    .pin-btn.delete {
      font-size: 18px;
    }

    .pin-error-msg {
      font-size: 13px;
      color: var(--error-color);
      min-height: 18px;
      letter-spacing: 0.02em;
    }

    /* MAIN SCREEN */
    #main-screen {
      display: none;
      flex-direction: column;
      gap: 16px;
    }

    .cards {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .card {
      width: 260px;
      height: 120px;
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      color: var(--text);
      cursor: pointer;
      text-decoration: none;
      transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      user-select: none;
    }

    .card:hover {
      background: var(--card-hover-bg);
      border-color: var(--card-hover-border);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.10);
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%       { transform: translateX(-8px); }
      40%       { transform: translateX(8px); }
      60%       { transform: translateX(-6px); }
      80%       { transform: translateX(6px); }
    }

    .shake { animation: shake 0.35s ease; }
  </style>
</head>
<body>

  <?php if ($authed): ?>
  <!-- Admin button (top-left) -->
  <a class="admin-btn" href="admin.php" title="Admin panel" aria-label="Admin panel">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
  </a>
  <?php endif; ?>

  <!-- Page controls (top-right) -->
  <div class="page-controls">
<?php if ($authed): ?>
    <button class="logout-btn" id="logout-btn" title="Log out" aria-label="Log out">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </button>
    <?php endif; ?>
    <button class="theme-toggle" id="theme-toggle" title="Toggle theme" aria-label="Toggle theme">🌙</button>
  </div>

  <!-- PIN SCREEN -->
  <div id="pin-screen"<?= $authed ? ' style="display:none"' : '' ?>>
    <h2>Enter PIN</h2>
    <div class="pin-dots" id="pin-dots">
      <div class="pin-dot" id="d0"></div>
      <div class="pin-dot" id="d1"></div>
      <div class="pin-dot" id="d2"></div>
      <div class="pin-dot" id="d3"></div>
    </div>
    <div class="pin-error-msg" id="pin-error"></div>
    <div class="pin-pad" id="pin-pad">
      <button class="pin-btn" data-n="1">1</button>
      <button class="pin-btn" data-n="2">2</button>
      <button class="pin-btn" data-n="3">3</button>
      <button class="pin-btn" data-n="4">4</button>
      <button class="pin-btn" data-n="5">5</button>
      <button class="pin-btn" data-n="6">6</button>
      <button class="pin-btn" data-n="7">7</button>
      <button class="pin-btn" data-n="8">8</button>
      <button class="pin-btn" data-n="9">9</button>
      <button class="pin-btn empty" aria-hidden="true"></button>
      <button class="pin-btn" data-n="0">0</button>
      <button class="pin-btn delete" id="pin-delete">⌫</button>
    </div>
  </div>

  <!-- MAIN SCREEN (only rendered server-side when authenticated) -->
  <?php if ($authed): ?>
  <div id="main-screen" style="display:flex">
    <div class="cards" id="cards-container"></div>
  </div>
  <?php endif; ?>

  <script src="assets/js/welcome.js" defer></script>
  <?php if ($authed): ?>
  <script>
    document.getElementById('logout-btn').addEventListener('click', async () => {
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      await fetch('logout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ csrf })
      });
      location.href = 'index.php';
    });
  </script>
  <?php endif; ?>
</body>
</html>
