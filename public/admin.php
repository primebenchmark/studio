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

$authed = isAuthenticated();
$csrf   = $_SESSION[CSRF_FIELD];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Panel</title>
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: linear-gradient(160deg, #f5f3f0 0%, #ebe7e2 50%, #f0ece8 100%);
      --text: #2a2520;
      --muted: #6a6560;
      --panel-bg: rgba(255,255,255,0.7);
      --panel-border: rgba(0,0,0,0.1);
      --input-bg: rgba(255,255,255,0.9);
      --input-border: rgba(0,0,0,0.15);
      --input-focus: rgba(0,0,0,0.35);
      --card-bg: rgba(255,255,255,0.6);
      --card-border: rgba(0,0,0,0.1);
      --btn-primary-bg: linear-gradient(135deg, #b8860b 0%, #8b6914 100%);
      --btn-primary-text: #fff;
      --btn-secondary-bg: rgba(0,0,0,0.05);
      --btn-secondary-border: rgba(139,105,20,0.35);
      --btn-secondary-text: #8b6914;
      --danger: #c0392b;
      --danger-light: rgba(192,57,43,0.08);
      --accent: #8b6914;
      --shadow: rgba(0,0,0,0.08);
    }

    [data-theme="dark"] {
      --bg: linear-gradient(160deg, #0d0d0d 0%, #1a1520 50%, #0d1117 100%);
      --text: #e0dcd4;
      --muted: #7a7570;
      --panel-bg: rgba(255,255,255,0.04);
      --panel-border: rgba(255,255,255,0.08);
      --input-bg: rgba(0,0,0,0.3);
      --input-border: rgba(255,255,255,0.1);
      --input-focus: rgba(255,255,255,0.25);
      --card-bg: rgba(255,255,255,0.03);
      --card-border: rgba(255,255,255,0.08);
      --btn-primary-bg: linear-gradient(135deg, #c9a96e 0%, #a67c52 100%);
      --btn-primary-text: #0d0d0d;
      --btn-secondary-bg: rgba(255,255,255,0.06);
      --btn-secondary-border: rgba(201,169,110,0.3);
      --btn-secondary-text: #c9a96e;
      --danger: #e74c3c;
      --danger-light: rgba(231,76,60,0.1);
      --accent: #c9a96e;
      --shadow: rgba(0,0,0,0.3);
    }

    body {
      min-height: 100vh;
      background: var(--bg);
      color: var(--text);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      padding: 32px 16px;
      transition: background 0.3s ease, color 0.3s ease;
    }

    .container {
      max-width: 760px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    header h1 {
      font-size: 22px;
      font-weight: 600;
      letter-spacing: -0.02em;
    }

    header .header-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .back-link {
      font-size: 13px;
      color: var(--muted);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 5px;
      transition: color 0.15s;
    }
    .back-link:hover { color: var(--text); }

    .logout-btn {
      height: 32px;
      padding: 0 12px;
      border-radius: 8px;
      border: 1px solid var(--input-border);
      background: transparent;
      color: var(--muted);
      font-size: 13px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background 0.15s, color 0.15s, border-color 0.15s;
    }
    .logout-btn:hover {
      background: var(--danger-light);
      color: var(--danger);
      border-color: var(--danger);
    }

    /* Theme toggle */
    .page-controls {
      position: fixed;
      top: 16px;
      right: 16px;
      z-index: 1001;
    }

    .theme-toggle {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      border: 1px solid var(--input-border);
      background: var(--input-bg);
      cursor: pointer;
      font-size: 16px;
      line-height: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s, border-color 0.2s;
    }
    .theme-toggle:hover {
      background: var(--btn-secondary-bg);
      border-color: var(--btn-secondary-border);
    }

    /* Panel */
    .panel {
      background: var(--panel-bg);
      border: 1px solid var(--panel-border);
      border-radius: 16px;
      padding: 24px;
      backdrop-filter: blur(12px);
      box-shadow: 0 2px 16px var(--shadow);
    }

    .panel-title {
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 20px;
    }

    /* Form rows */
    .form-row {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }

    .form-row label {
      font-size: 13px;
      color: var(--muted);
      width: 130px;
      flex-shrink: 0;
    }

    .form-row input[type="number"],
    .form-row input[type="password"],
    .form-row select {
      flex: 1;
      height: 36px;
      padding: 0 10px;
      border-radius: 8px;
      border: 1px solid var(--input-border);
      background: var(--input-bg);
      color: var(--text);
      font-size: 14px;
      outline: none;
      transition: border-color 0.15s;
    }
    .form-row input[type="number"]:focus,
    .form-row input[type="password"]:focus,
    .form-row select:focus {
      border-color: var(--input-focus);
    }

    /* Cards list */
    .cards-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .card-editor {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 12px;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .card-editor-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .card-num {
      font-size: 12px;
      font-weight: 600;
      color: var(--accent);
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .btn-icon {
      width: 28px;
      height: 28px;
      border-radius: 7px;
      border: 1px solid var(--card-border);
      background: transparent;
      color: var(--muted);
      cursor: pointer;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.15s, color 0.15s;
    }
    .btn-icon:hover {
      background: var(--danger-light);
      color: var(--danger);
      border-color: var(--danger);
    }

    .card-fields {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .field-group {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .field-group.full-width {
      grid-column: 1 / -1;
    }

    .field-group label {
      font-size: 11px;
      color: var(--muted);
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .field-group input[type="text"],
    .field-group input[type="url"],
    .field-group input[type="number"],
    .field-group select {
      height: 34px;
      padding: 0 10px;
      border-radius: 8px;
      border: 1px solid var(--input-border);
      background: var(--input-bg);
      color: var(--text);
      font-size: 13px;
      outline: none;
      transition: border-color 0.15s;
      width: 100%;
    }
    .field-group input:focus,
    .field-group select:focus {
      border-color: var(--input-focus);
    }

    /* Buttons */
    .btn-primary {
      height: 40px;
      padding: 0 22px;
      border-radius: 10px;
      border: none;
      background: var(--btn-primary-bg);
      color: var(--btn-primary-text);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity 0.15s, transform 0.1s;
      white-space: nowrap;
    }
    .btn-primary:hover { opacity: 0.9; }
    .btn-primary:active { transform: scale(0.97); }

    .btn-secondary {
      height: 38px;
      padding: 0 18px;
      border-radius: 10px;
      border: 1px solid var(--btn-secondary-border);
      background: var(--btn-secondary-bg);
      color: var(--btn-secondary-text);
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: opacity 0.15s;
    }
    .btn-secondary:hover { opacity: 0.8; }

    .actions-row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
    }

    .save-badge {
      font-size: 12px;
      color: var(--accent);
      opacity: 0;
      transition: opacity 0.3s;
    }
    .save-badge.visible { opacity: 1; }

    /* Preview strip */
    .preview-strip {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .preview-card {
      border-radius: 12px;
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      color: var(--text);
      overflow: hidden;
      word-break: break-all;
      text-align: center;
      padding: 8px;
    }

    select option { background: #1a1a1a; color: #eee; }
    [data-theme="dark"] select option { background: #1a1a1a; color: #eee; }

    @media (max-width: 520px) {
      .card-fields { grid-template-columns: 1fr; }
      .field-group.full-width { grid-column: auto; }
      .form-row { flex-wrap: wrap; }
      .form-row label { width: 100%; }
    }

    /* PIN overlay */
    #pin-overlay {
      position: fixed;
      inset: 0;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      transition: background 0.3s ease;
    }

    #pin-overlay.hidden { display: none; }

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

    .pin-dots { display: flex; gap: 14px; }

    .pin-dot {
      width: 14px;
      height: 14px;
      border-radius: 50%;
      border: 1.5px solid var(--input-border);
      background: transparent;
      transition: background 0.15s ease, border-color 0.15s ease;
    }
    .pin-dot.filled { background: var(--text); border-color: var(--text); }
    .pin-dot.error  { background: var(--danger); border-color: var(--danger); }

    .pin-pad {
      display: grid;
      grid-template-columns: repeat(3, 72px);
      grid-template-rows: repeat(4, 72px);
      gap: 10px;
    }

    .pin-btn {
      width: 72px; height: 72px;
      border-radius: 50%;
      border: 1px solid var(--input-border);
      background: var(--panel-bg);
      font-size: 22px;
      color: var(--text);
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.1s, box-shadow 0.1s, border-color 0.1s;
      box-shadow: 0 2px 6px var(--shadow);
      user-select: none;
      backdrop-filter: blur(12px);
    }
    .pin-btn:hover  { background: var(--btn-secondary-bg); border-color: var(--btn-secondary-border); box-shadow: 0 4px 12px var(--shadow); }
    .pin-btn:active { opacity: 0.7; box-shadow: none; }
    .pin-btn.empty  { background: transparent; border: none; box-shadow: none; cursor: default; pointer-events: none; }
    .pin-btn.delete { font-size: 18px; }

    .pin-error-msg {
      font-size: 13px;
      color: var(--danger);
      min-height: 18px;
      letter-spacing: 0.02em;
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
  <!-- Theme toggle — always in DOM so JS can find it regardless of auth state -->
  <div class="page-controls">
    <button class="theme-toggle" id="theme-toggle" title="Toggle theme" aria-label="Toggle theme">🌙</button>
  </div>

  <!-- PIN OVERLAY — hidden server-side when already authenticated -->
  <div id="pin-overlay"<?= $authed ? ' class="hidden"' : '' ?>>
    <div id="pin-screen">
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
  </div>

  <?php if ($authed): ?>
  <div class="container">
    <header>
      <h1>Welcome Screen Admin</h1>
      <div class="header-actions">
        <a href="index.php" class="back-link">← Preview</a>
        <button class="logout-btn" id="logout-btn" title="Log out" aria-label="Log out">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Log out
        </button>
      </div>
    </header>

    <!-- Layout settings -->
    <div class="panel">
      <div class="panel-title">Layout</div>
      <div class="form-row">
        <label>Number of cards</label>
        <input type="number" id="num-cards" min="1" max="12" value="2" />
      </div>
      <div class="form-row">
        <label>Card layout</label>
        <select id="layout">
          <option value="column">Column (vertical stack)</option>
          <option value="row">Row (horizontal)</option>
          <option value="grid-2">Grid 2 columns</option>
          <option value="grid-3">Grid 3 columns</option>
        </select>
      </div>
      <div class="form-row">
        <label>Card width (px)</label>
        <input type="number" id="card-width" min="80" max="800" value="260" />
      </div>
      <div class="form-row">
        <label>Card height (px)</label>
        <input type="number" id="card-height" min="40" max="600" value="120" />
      </div>
    </div>

    <!-- Cards -->
    <div class="panel">
      <div class="panel-title">Cards</div>
      <div class="cards-list" id="cards-list"></div>
    </div>

    <!-- Preview -->
    <div class="panel">
      <div class="panel-title">Preview</div>
      <div class="preview-strip" id="preview-strip"></div>
    </div>

    <!-- Actions -->
    <div class="actions-row">
      <span class="save-badge" id="save-badge">✓ Saved</span>
      <div style="display:flex;gap:10px;margin-left:auto;">
        <button class="btn-secondary" id="btn-reset">Reset to defaults</button>
        <button class="btn-primary" id="btn-save">Save changes</button>
      </div>
    </div>

    <!-- Change PIN -->
    <div class="panel">
      <div class="panel-title">Change PIN</div>
      <div class="form-row">
        <label for="current-pin">Current PIN</label>
        <input type="password" id="current-pin" inputmode="numeric" pattern="\d{4,8}" maxlength="8" autocomplete="current-password" />
      </div>
      <div class="form-row">
        <label for="new-pin">New PIN (4–8 digits)</label>
        <input type="password" id="new-pin" inputmode="numeric" pattern="\d{4,8}" maxlength="8" autocomplete="new-password" />
      </div>
      <div class="form-row">
        <label for="confirm-pin">Confirm new PIN</label>
        <input type="password" id="confirm-pin" inputmode="numeric" pattern="\d{4,8}" maxlength="8" autocomplete="new-password" />
      </div>
      <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
        <button class="btn-primary" id="btn-change-pin">Change PIN</button>
        <span class="pin-change-msg" id="pin-change-msg" style="font-size:13px;min-height:18px;"></span>
      </div>
    </div>
  </div>

  <?php endif; ?>

  <script src="assets/js/admin.js" defer></script>
  <script>
    document.getElementById('logout-btn')?.addEventListener('click', async () => {
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      await fetch('logout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ csrf })
      });
      location.href = 'index.php';
    });

    document.getElementById('btn-change-pin')?.addEventListener('click', async () => {
      const msg = document.getElementById('pin-change-msg');
      const currentPin = document.getElementById('current-pin').value;
      const newPin = document.getElementById('new-pin').value;
      const confirmPin = document.getElementById('confirm-pin').value;

      if (!/^\d{4,8}$/.test(newPin)) {
        msg.style.color = 'var(--danger)';
        msg.textContent = 'PIN must be 4–8 digits.';
        return;
      }
      if (newPin !== confirmPin) {
        msg.style.color = 'var(--danger)';
        msg.textContent = 'New PINs do not match.';
        return;
      }

      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      try {
        const res = await fetch('change-pin.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ csrf, current_pin: currentPin, new_pin: newPin, confirm_pin: confirmPin })
        });
        const data = await res.json();
        if (data.ok) {
          msg.style.color = 'var(--accent)';
          msg.textContent = 'PIN changed successfully.';
          // Update CSRF token if rotated
          if (data.csrf) document.querySelector('meta[name="csrf-token"]').content = data.csrf;
          document.getElementById('current-pin').value = '';
          document.getElementById('new-pin').value = '';
          document.getElementById('confirm-pin').value = '';
        } else {
          msg.style.color = 'var(--danger)';
          const errors = {
            wrong_current_pin: 'Current PIN is incorrect.',
            invalid_new_pin: 'New PIN must be 4–8 digits.',
            pins_dont_match: 'New PINs do not match.',
            locked: 'Too many attempts. Try again later.',
          };
          msg.textContent = errors[data.error] || 'Failed to change PIN.';
        }
      } catch {
        msg.style.color = 'var(--danger)';
        msg.textContent = 'Connection error.';
      }
    });
  </script>
</body>
</html>
