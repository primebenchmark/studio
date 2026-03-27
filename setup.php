<?php
/**
 * Studio PIN Setup
 * ─────────────────────────────────────────────────────────────────────────────
 * Run this file ONCE to set your PIN, then DELETE it from the server.
 * The PIN is stored only as a bcrypt hash — never in plaintext.
 *
 * Usage:
 *   Browser : http://your-server/studio/setup.php
 *   CLI     : php setup.php
 *
 * After completing setup, delete this file:
 *   rm /path/to/studio/setup.php
 * ─────────────────────────────────────────────────────────────────────────────
 */

define('STUDIO_AUTH', 1);
define('PIN_HASH_FILE', __DIR__ . '/auth/pin.hash');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin     = $_POST['pin']     ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!preg_match('/^\d{4,8}$/', $pin)) {
        $error = 'PIN must be 4–8 digits.';
    } elseif ($pin !== $confirm) {
        $error = 'PINs do not match.';
    } else {
        // Cost 12: ~250-400 ms on modern hardware — slow enough to deter brute force
        $hash = password_hash($pin, PASSWORD_BCRYPT, ['cost' => 12]);

        $dir = dirname(PIN_HASH_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        if (file_put_contents(PIN_HASH_FILE, $hash, LOCK_EX) !== false) {
            chmod(PIN_HASH_FILE, 0600);
            $success = 'PIN saved successfully. <strong>Delete setup.php now.</strong>';
        } else {
            $error = 'Could not write ' . htmlspecialchars(PIN_HASH_FILE) . '. Check directory permissions.';
        }
    }
}

$hashExists = file_exists(PIN_HASH_FILE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Studio Setup</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      background: #f5f3f0;
      display: flex; align-items: center; justify-content: center;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: #2a2520;
    }
    .card {
      background: #fff;
      border: 1px solid #e0dcd4;
      border-radius: 16px;
      padding: 36px 40px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
    h1 { font-size: 20px; font-weight: 600; margin-bottom: 6px; }
    .sub { font-size: 13px; color: #6a6560; margin-bottom: 28px; line-height: 1.5; }
    .warn {
      background: #fff8e6; border: 1px solid #f5d080;
      border-radius: 8px; padding: 12px 14px;
      font-size: 13px; color: #7a5c00; margin-bottom: 20px;
    }
    label { display: block; font-size: 12px; font-weight: 600;
            letter-spacing: 0.05em; text-transform: uppercase;
            color: #6a6560; margin-bottom: 6px; margin-top: 16px; }
    input[type="password"] {
      width: 100%; height: 40px; padding: 0 12px;
      border-radius: 8px; border: 1px solid #d0ccc4;
      background: #faf9f8; font-size: 16px; outline: none;
      transition: border-color 0.15s;
    }
    input[type="password"]:focus { border-color: #8b6914; }
    .error   { margin-top: 14px; font-size: 13px; color: #c0392b; }
    .success { margin-top: 14px; font-size: 13px; color: #217a3c; }
    button {
      margin-top: 24px; width: 100%; height: 42px;
      border-radius: 10px; border: none;
      background: linear-gradient(135deg, #b8860b 0%, #8b6914 100%);
      color: #fff; font-size: 14px; font-weight: 600;
      cursor: pointer; transition: opacity 0.15s;
    }
    button:hover { opacity: 0.9; }
    .delete-note {
      margin-top: 20px; padding: 12px 14px;
      background: #fdf0f0; border: 1px solid #f5c0c0;
      border-radius: 8px; font-size: 12px; color: #c0392b;
    }
    code { font-family: monospace; background: rgba(0,0,0,0.06); padding: 2px 5px; border-radius: 4px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Studio Setup</h1>
    <p class="sub">Set a PIN to protect your Studio. The PIN is stored as a bcrypt hash — never in plaintext.</p>

    <?php if ($hashExists && !$success): ?>
      <div class="warn">⚠ A PIN hash already exists. Submitting this form will <strong>overwrite</strong> it.</div>
    <?php endif; ?>

    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p class="success"><?= $success ?></p>
      <div class="delete-note">
        <strong>Security reminder:</strong> Delete this file immediately.<br>
        <code>rm <?= htmlspecialchars(__FILE__) ?></code>
      </div>
    <?php else: ?>
      <form method="POST">
        <label for="pin">New PIN (4–8 digits)</label>
        <input type="password" id="pin" name="pin" inputmode="numeric"
               pattern="\d{4,8}" maxlength="8" autocomplete="new-password" required />

        <label for="confirm">Confirm PIN</label>
        <input type="password" id="confirm" name="confirm" inputmode="numeric"
               pattern="\d{4,8}" maxlength="8" autocomplete="new-password" required />

        <button type="submit">Save PIN &amp; continue</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
