const STORAGE_KEY = 'welcome-config';

const DEFAULT_CARDS = [
  { label: 'Kanji Studio', href: '#' },
  { label: 'Image Studio', href: 'index.html' },
];

const DEFAULTS = {
  numCards: 2,
  layout: 'column',
  cardWidth: 260,
  cardHeight: 120,
  cards: DEFAULT_CARDS,
  theme: 'light',
};

// ── Theme ──────────────────────────────────────────────────────────────────
const html = document.documentElement;
const themeToggle = document.getElementById('theme-toggle');
const themeLabel  = document.getElementById('theme-label');

function applyTheme(t) {
  html.setAttribute('data-theme', t);
  themeLabel.textContent = t === 'dark' ? 'Dark' : 'Light';
}

themeToggle.addEventListener('click', () => {
  const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  applyTheme(next);
  try { localStorage.setItem('admin-theme', next); } catch {}
});

const savedAdminTheme = (() => { try { return localStorage.getItem('admin-theme') || 'light'; } catch { return 'light'; } })();
applyTheme(savedAdminTheme);

// ── Load/Save config ───────────────────────────────────────────────────────
function loadConfig() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (raw) return { ...DEFAULTS, ...JSON.parse(raw) };
  } catch {}
  return { ...DEFAULTS, cards: DEFAULT_CARDS.map(c => ({ ...c })) };
}

function saveConfig(cfg) {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(cfg)); } catch {}
}

// ── DOM refs ───────────────────────────────────────────────────────────────
const numCardsInput  = document.getElementById('num-cards');
const layoutSelect   = document.getElementById('layout');
const cardWidthInput = document.getElementById('card-width');
const cardHeightInput = document.getElementById('card-height');
const cardsList      = document.getElementById('cards-list');
const previewStrip   = document.getElementById('preview-strip');
const saveBadge      = document.getElementById('save-badge');

let config = loadConfig();

function clamp(val, min, max) { return Math.min(max, Math.max(min, val)); }

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function sanitizeUrl(url) {
  if (!url) return '#';
  const s = String(url).trim();
  if (/^(javascript|data|vbscript):/i.test(s)) return '#';
  return s;
}

// ── Render card editors ────────────────────────────────────────────────────
function renderCards() {
  const n = clamp(parseInt(numCardsInput.value) || 2, 1, 12);
  while (config.cards.length < n) config.cards.push({ label: `Card ${config.cards.length + 1}`, href: '#' });
  config.cards = config.cards.slice(0, n);

  cardsList.innerHTML = '';
  for (let i = 0; i < n; i++) {
    const card = config.cards[i];
    const el = document.createElement('div');
    el.className = 'card-editor';
    el.innerHTML = `
      <div class="card-editor-header">
        <span class="card-num">Card ${i + 1}</span>
        <button class="btn-icon remove-card" data-i="${i}" title="Remove card">✕</button>
      </div>
      <div class="card-fields">
        <div class="field-group full-width">
          <label>Label</label>
          <input type="text" class="f-label" data-i="${i}" value="${escHtml(card.label || '')}" placeholder="Card label" />
        </div>
        <div class="field-group full-width">
          <label>Link (href)</label>
          <input type="url" class="f-href" data-i="${i}" value="${escHtml(card.href || '#')}" placeholder="https://... or relative path" />
        </div>
        <div class="field-group">
          <label>Width override (px, blank = global)</label>
          <input type="number" class="f-width" data-i="${i}" value="${card.width !== undefined ? card.width : ''}" min="40" max="800" placeholder="—" />
        </div>
        <div class="field-group">
          <label>Height override (px, blank = global)</label>
          <input type="number" class="f-height" data-i="${i}" value="${card.height !== undefined ? card.height : ''}" min="30" max="600" placeholder="—" />
        </div>
      </div>
    `;
    cardsList.appendChild(el);
  }

  cardsList.querySelectorAll('.f-label').forEach(inp => {
    inp.addEventListener('input', e => { config.cards[+e.target.dataset.i].label = e.target.value; updatePreview(); });
  });
  cardsList.querySelectorAll('.f-href').forEach(inp => {
    inp.addEventListener('input', e => { config.cards[+e.target.dataset.i].href = sanitizeUrl(e.target.value); });
  });
  cardsList.querySelectorAll('.f-width').forEach(inp => {
    inp.addEventListener('input', e => {
      const v = parseInt(e.target.value);
      if (isNaN(v)) delete config.cards[+e.target.dataset.i].width;
      else config.cards[+e.target.dataset.i].width = v;
      updatePreview();
    });
  });
  cardsList.querySelectorAll('.f-height').forEach(inp => {
    inp.addEventListener('input', e => {
      const v = parseInt(e.target.value);
      if (isNaN(v)) delete config.cards[+e.target.dataset.i].height;
      else config.cards[+e.target.dataset.i].height = v;
      updatePreview();
    });
  });
  cardsList.querySelectorAll('.remove-card').forEach(btn => {
    btn.addEventListener('click', e => {
      const i = +e.currentTarget.dataset.i;
      config.cards.splice(i, 1);
      numCardsInput.value = config.cards.length;
      renderCards();
      updatePreview();
    });
  });

  updatePreview();
}

// ── Preview ────────────────────────────────────────────────────────────────
function updatePreview() {
  const w = parseInt(cardWidthInput.value) || 260;
  const h = parseInt(cardHeightInput.value) || 120;
  const layout = layoutSelect.value;

  previewStrip.style.flexDirection = layout === 'column' ? 'column' : 'row';
  previewStrip.style.flexWrap = layout.startsWith('grid') ? 'wrap' : 'nowrap';

  previewStrip.innerHTML = '';
  const scale = 0.55;
  config.cards.forEach(card => {
    const cw = (card.width || w) * scale;
    const ch = (card.height || h) * scale;
    const div = document.createElement('div');
    div.className = 'preview-card';
    div.style.width = cw + 'px';
    div.style.height = ch + 'px';
    div.textContent = card.label || '—';
    previewStrip.appendChild(div);
  });
}

// ── Populate from config ───────────────────────────────────────────────────
function populate() {
  numCardsInput.value  = config.numCards;
  layoutSelect.value   = config.layout;
  cardWidthInput.value = config.cardWidth;
  cardHeightInput.value = config.cardHeight;
  renderCards();
}

numCardsInput.addEventListener('change', () => { config.numCards = parseInt(numCardsInput.value) || 2; renderCards(); });
layoutSelect.addEventListener('change', () => { config.layout = layoutSelect.value; updatePreview(); });
cardWidthInput.addEventListener('input', () => { config.cardWidth = parseInt(cardWidthInput.value) || 260; updatePreview(); });
cardHeightInput.addEventListener('input', () => { config.cardHeight = parseInt(cardHeightInput.value) || 120; updatePreview(); });

document.getElementById('btn-save').addEventListener('click', () => {
  config.numCards   = parseInt(numCardsInput.value) || 2;
  config.layout     = layoutSelect.value;
  config.cardWidth  = parseInt(cardWidthInput.value) || 260;
  config.cardHeight = parseInt(cardHeightInput.value) || 120;
  saveConfig(config);
  saveBadge.classList.add('visible');
  setTimeout(() => saveBadge.classList.remove('visible'), 2200);
});

document.getElementById('btn-reset').addEventListener('click', () => {
  if (!confirm('Reset all welcome screen settings to defaults?')) return;
  config = { ...DEFAULTS, cards: DEFAULT_CARDS.map(c => ({ ...c })) };
  saveConfig(config);
  populate();
});

populate();

// ── PIN ── (server-side validation via fetch) ──────────────────────────────
(function() {
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
  const overlay    = document.getElementById('pin-overlay');
  const pinDots    = document.getElementById('pin-dots');
  const dots       = [0,1,2,3].map(i => document.getElementById('d' + i));
  const errorMsg   = document.getElementById('pin-error');
  const pinPad     = document.getElementById('pin-pad');

  let entered    = '';
  let submitting = false;

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
    overlay.classList.add('hidden');
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
    if (btn.id === 'pin-delete') { entered = entered.slice(0, -1); updateDots(); return; }
    const n = btn.dataset.n;
    if (n === undefined || entered.length >= 4) return;
    entered += n;
    updateDots();
    if (entered.length === 4) handlePinSubmit();
  });

  document.addEventListener('keydown', e => {
    if (overlay.classList.contains('hidden') || submitting) return;
    if (e.key >= '0' && e.key <= '9' && entered.length < 4) {
      entered += e.key; updateDots();
      if (entered.length === 4) handlePinSubmit();
    } else if (e.key === 'Backspace') {
      entered = entered.slice(0, -1); updateDots();
    }
  });
})();
