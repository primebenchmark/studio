# Studio

A PIN-protected personal launcher page with an admin panel for configuring shortcut cards.

## Features

- 4–8 digit PIN authentication (bcrypt, server-side)
- Session management with idle timeout and automatic ID rotation
- IP-based rate limiting with lockout after repeated failures
- CSRF protection on all state-changing requests
- Configurable card grid (label, URL, size, layout) via admin panel
- Light / dark theme toggle

## Setup

**Requirements:** PHP 8.0+ with session support.

1. Copy the project files to your web server.
2. Open `setup.php` in a browser (or run `php setup.php` from the CLI) and set your PIN.
3. **Delete `setup.php`** immediately after — it is not protected by the PIN.
4. Open `welcome.php` and enter your PIN to log in.

```
rm /path/to/studio/setup.php
```

## File structure

```
studio/
├── auth/
│   ├── config.php       # Constants and shared helpers
│   ├── session.php      # Session bootstrap and auth check
│   ├── rate_limit.php   # IP-based lockout logic
│   ├── pin.hash         # bcrypt hash of your PIN (git-ignored)
│   └── ratelimit/       # Per-IP failure state (git-ignored)
├── js/
│   ├── welcome.js       # PIN entry and card rendering
│   └── admin.js         # Admin panel logic
├── welcome.php          # Main launcher (PIN-protected)
├── admin.php            # Card configuration panel (PIN-protected)
├── login.php            # PIN verification API endpoint
├── logout.php           # Session destruction
└── setup.php            # First-run PIN setup (delete after use)
```

## Security notes

- `auth/pin.hash` and `auth/ratelimit/` are git-ignored and should never be committed.
- `setup.php` has no auth guard — delete it from the server after setting your PIN.
- The PIN is verified server-side only; protected page content is never sent to unauthenticated browsers.
- Sessions are hardened: HttpOnly + SameSite=Strict cookies, strict mode, periodic ID rotation.
