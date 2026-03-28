# Studio

A PIN-protected personal launcher and creative tools hub with an admin panel for configuring shortcut cards.

## Features

- 4–8 digit PIN authentication (bcrypt, server-side)
- Session management with idle timeout and automatic ID rotation
- IP-based rate limiting with lockout after repeated failures
- CSRF protection on all state-changing requests
- Configurable card grid (label, URL, size, layout) via admin panel
- Light / dark theme toggle (persisted via `studio-theme` localStorage key)
- Admin panel gate with PIN re-entry and gear icon on the launcher
- Built-in studio tools: Image Studio, Collage Studio, Kanji Studio

## Setup

**Requirements:** PHP 8.0+ with session support.

1. Copy the project files to your web server.
2. Open `setup.php` in a browser (or run `php setup.php` from the CLI) and set your PIN.
3. **Delete `setup.php`** immediately after — it is not protected by the PIN.
4. Open `index.php` and enter your PIN to log in.

```
rm /path/to/studio/public/setup.php
```

## File structure

```
studio/
├── public/                      # Web root
│   ├── .htaccess                # Rewrite rules and default CSP headers
│   ├── index.php                # Main launcher (PIN-protected)
│   ├── login.php                # PIN verification API endpoint
│   ├── logout.php               # Session destruction
│   ├── admin.php                # Card configuration panel (PIN-protected)
│   ├── admin-auth.php           # Admin PIN re-entry gate
│   ├── change-pin.php           # PIN change endpoint (PIN-protected)
│   ├── config-api.php           # Card config read/write API
│   ├── router.php               # Dev server router (php -S)
│   ├── setup.php                # First-run PIN setup (delete after use)
│   ├── image-studio.php         # Image editor tool (PIN-protected)
│   ├── collage-studio.php       # Collage builder tool (PIN-protected)
│   ├── kanji-studio.php         # Kanji flashcard tool (PIN-protected)
│   └── assets/
│       └── js/
│           ├── welcome.js       # PIN entry and card rendering
│           └── admin.js         # Admin panel logic
├── studio_src/                  # PHP backend (outside web root)
│   ├── config.php               # Constants and shared helpers
│   ├── session.php              # Session bootstrap and auth check
│   ├── rate_limit.php           # IP-based lockout logic
│   └── audit_log.php            # Audit log writer
└── studio_data/                 # Runtime data (outside web root, git-ignored)
    ├── pin.hash                 # bcrypt hash of your PIN
    ├── config.json              # Card configuration
    ├── audit.log                # Auth/admin event log
    └── ratelimit/               # Per-IP failure state
```

## Security notes

- `studio_data/` is outside the web root and git-ignored — credentials and state never leave the server.
- `setup.php` has no auth guard — delete it from the server after setting your PIN.
- The PIN is verified server-side only; protected page content is never sent to unauthenticated browsers.
- Sessions are hardened: HttpOnly + SameSite=Strict cookies, strict mode, periodic ID rotation.
- The admin panel requires PIN re-entry before access, separate from the launcher session.
