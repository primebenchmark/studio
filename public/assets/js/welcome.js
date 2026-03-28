// ── Theme ──────────────────────────────────────────────────────────────────
const THEME_KEY = 'welcome-theme';
const html = document.documentElement;

function applyTheme(t) {
  html.setAttribute('data-theme', t);
  const btn = document.getElementById('theme-toggle');
  if (btn) btn.textContent = t === 'dark' ? '☀️' : '🌙';
  try { localStorage.setItem(THEME_KEY, t); } catch {}
}

document.getElementById('theme-toggle').addEventListener('click', () => {
  const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  applyTheme(next);
});

const savedTheme = (() => { try { return localStorage.getItem(THEME_KEY) || 'light'; } catch { return 'light'; } })();
applyTheme(savedTheme);

// ── URL sanitization ───────────────────────────────────────────────────────
function sanitizeUrl(url) {
  if (!url) return '#';
  const s = String(url).trim();
  if (/^(javascript|data|vbscript):/i.test(s)) return '#';
  return s;
}

// ── Card config ────────────────────────────────────────────────────────────
const STORAGE_KEY = 'welcome-config-v2';
const DEFAULT_CARDS = [
  { label: 'Kanji Studio', href: 'kanji-studio.php' },
  { label: 'Image Studio', href: 'image-studio.php' },
  { label: 'Collage Studio', href: 'collage-studio.php' },
];
const DEFAULTS = {
  numCards: 3,
  layout: 'column',
  cardWidth: 260,
  cardHeight: 120,
  cards: DEFAULT_CARDS,
};

function loadConfigLocal() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (raw) return { ...DEFAULTS, ...JSON.parse(raw) };
  } catch {}
  return { ...DEFAULTS };
}

function buildCards(cfg) {
  const container = document.getElementById('cards-container');
  if (!container) return;
  container.innerHTML = '';

  const layout = cfg.layout || 'column';
  if (layout === 'column') {
    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.flexWrap = 'nowrap';
    container.style.gap = '16px';
  } else if (layout === 'row') {
    container.style.display = 'flex';
    container.style.flexDirection = 'row';
    container.style.flexWrap = 'nowrap';
    container.style.gap = '16px';
  } else if (layout === 'grid-2') {
    container.style.display = 'grid';
    container.style.gridTemplateColumns = 'repeat(2, auto)';
    container.style.gap = '16px';
  } else if (layout === 'grid-3') {
    container.style.display = 'grid';
    container.style.gridTemplateColumns = 'repeat(3, auto)';
    container.style.gap = '16px';
  }

  const cards = (cfg.cards || DEFAULT_CARDS).slice(0, cfg.numCards || DEFAULT_CARDS.length);
  const gw = cfg.cardWidth || 260;
  const gh = cfg.cardHeight || 120;

  const frag = document.createDocumentFragment();
  cards.forEach(card => {
    const a = document.createElement('a');
    a.className = 'card';
    a.href = sanitizeUrl(card.href || '#');
    a.textContent = card.label || '';
    a.style.width = (card.width || gw) + 'px';
    a.style.height = (card.height || gh) + 'px';
    frag.appendChild(a);
  });
  container.appendChild(frag);
}

// Load config from server, fall back to localStorage
(async function initCards() {
  let cfg = loadConfigLocal();
  try {
    const res = await fetch('config-api.php', { credentials: 'same-origin' });
    if (res.ok) {
      const data = await res.json();
      if (data.ok && data.config) cfg = { ...DEFAULTS, ...data.config };
    }
  } catch {}
  buildCards(cfg);
})();

// ── PIN ── (server-side validation via fetch) ──────────────────────────────
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
let entered    = '';
let submitting = false;

const dots     = [0,1,2,3].map(i => document.getElementById('d' + i));
const errorMsg = document.getElementById('pin-error');
const pinScreen  = document.getElementById('pin-screen');
const mainScreen = document.getElementById('main-screen');
const pinDots    = document.getElementById('pin-dots');
const pinPad     = document.getElementById('pin-pad');

function updateDots() {
  dots.forEach((d, i) => {
    d.classList.toggle('filled', i < entered.length);
    d.classList.remove('error');
  });
}

function showError(msg) {
  dots.forEach(d => { d.classList.remove('filled'); d.classList.add('error'); });
  pinDots.classList.add('shake');
  errorMsg.textContent = msg || 'Incorrect PIN';
  pinDots.addEventListener('animationend', () => {
    pinDots.classList.remove('shake');
    dots.forEach(d => d.classList.remove('error'));
    if (!errorMsg.textContent.startsWith('Too many')) errorMsg.textContent = '';
  }, { once: true });
}

function unlock() {
  window.location.reload();
}

async function handlePinSubmit() {
  if (submitting) return;
  submitting = true;
  pinPad.style.pointerEvents = 'none';

  try {
    const res = await fetch('login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ pin: entered, csrf: CSRF_TOKEN }),
    });
    const data = await res.json();

    if (data.ok) {
      setTimeout(unlock, 120);
    } else if (data.error === 'locked') {
      const secs = data.retry_after || 300;
      showError(`Too many attempts. Try in ${secs}s`);
    } else {
      showError('Incorrect PIN');
    }
  } catch {
    showError('Connection error. Retry.');
  } finally {
    submitting = false;
    pinPad.style.pointerEvents = '';
    entered = '';
    updateDots();
  }
}

pinPad.addEventListener('click', e => {
  const btn = e.target.closest('.pin-btn');
  if (!btn || submitting) return;

  if (btn.id === 'pin-delete') {
    entered = entered.slice(0, -1);
    updateDots();
    return;
  }

  const n = btn.dataset.n;
  if (n === undefined || entered.length >= 4) return;

  entered += n;
  updateDots();

  if (entered.length === 4) handlePinSubmit();
});

document.addEventListener('keydown', e => {
  if ((mainScreen && mainScreen.style.display === 'flex') || submitting) return;
  if (e.key >= '0' && e.key <= '9' && entered.length < 4) {
    entered += e.key;
    updateDots();
    if (entered.length === 4) handlePinSubmit();
  } else if (e.key === 'Backspace') {
    entered = entered.slice(0, -1);
    updateDots();
  }
});
