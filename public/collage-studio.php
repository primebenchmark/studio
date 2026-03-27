<?php
define('STUDIO_AUTH', true);
require_once __DIR__ . '/../studio_src/config.php';
require_once __DIR__ . '/../studio_src/session.php';
studioSessionStart();
if (!isAuthenticated()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Collage Studio</title>
  <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
  <div id="root"></div>
  <script type="text/babel">
    const { useState, useRef, useEffect, useCallback } = React;

    // ── Presets imported from index.html ──────────────────────────────────────
    const PRESETS_SOLID = [
      { label: "Ink on Paper", bg: "#FAF6F0" },
      { label: "Chalk Board", bg: "#2d3a2d" },
      { label: "Red Seal", bg: "#f5f0e8" },
      { label: "Neon Night", bg: "#0f0f1a" },
      { label: "Minimal", bg: "#ffffff" },
      { label: "Washi", bg: "#e8ddd0" },
      { label: "Bamboo Forest", bg: "#1a2e1a" },
      { label: "Cherry Blossom", bg: "#fff0f5" },
      { label: "Ocean Depths", bg: "#0a1628" },
      { label: "Golden Temple", bg: "#2a1f0e" },
      { label: "Snow Mountain", bg: "#f0f4f8" },
      { label: "Autumn Leaves", bg: "#3e1f0d" },
      { label: "Twilight", bg: "#1a1033" },
      { label: "Matcha", bg: "#2d4a2d" },
      { label: "Sunrise", bg: "#fff3e0" },
      { label: "Midnight Blue", bg: "#0d1b2a" },
      { label: "Zen Garden", bg: "#f5f5dc" },
      { label: "Volcanic", bg: "#1a0a0a" },
      { label: "Arctic", bg: "#e8f4fd" },
      { label: "Jade", bg: "#0d2818" },
      { label: "Rose Gold", bg: "#2a1a1a" },
      { label: "Lavender", bg: "#f3e5f5" },
      { label: "Charcoal", bg: "#1c1c1c" },
      { label: "Peach", bg: "#fff8e1" },
      { label: "Deep Forest", bg: "#0a1a0a" },
      { label: "Royal Purple", bg: "#1a0a2e" },
      { label: "Copper", bg: "#1a120a" },
      { label: "Frost", bg: "#e0f7fa" },
      { label: "Ember", bg: "#280a0a" },
      { label: "Steel", bg: "#263238" },
      { label: "Sakura Dark", bg: "#1a0a14" },
      { label: "Moss", bg: "#1b2d1b" },
      { label: "Ivory", bg: "#fffff0" },
      { label: "Cobalt", bg: "#0a0a28" },
      { label: "Sand", bg: "#f5e6d3" },
      { label: "Plum", bg: "#2a0a2a" },
      { label: "Teal", bg: "#0a2828" },
      { label: "Cream", bg: "#fefcf0" },
      { label: "Graphite", bg: "#212121" },
      { label: "Honey", bg: "#2a1f0a" },
      { label: "Wine", bg: "#1a0a0f" },
      { label: "Pine", bg: "#0a1a14" },
      { label: "Sunset", bg: "#1a0f0a" },
      { label: "Moonlight", bg: "#0f0f1e" },
      { label: "Coral", bg: "#fff0ee" },
      { label: "Slate", bg: "#2d3436" },
      { label: "Olive", bg: "#1a1a0a" },
      { label: "Amethyst", bg: "#1a0a28" },
      { label: "Parchment", bg: "#f0e6d2" },
      { label: "Electric", bg: "#0a0a1e" },
      { label: "Obsidian", bg: "#0a0a0a" },
      { label: "Crimson Lake", bg: "#1a0005" },
      { label: "Aurora", bg: "#001a14" },
      { label: "Dusk", bg: "#1e1428" },
      { label: "Glacier", bg: "#e8f0f8" },
      { label: "Ember Glow", bg: "#200a00" },
      { label: "Seafoam", bg: "#e0f2f1" },
      { label: "Midnight Rose", bg: "#0d0010" },
      { label: "Desert Sand", bg: "#f5e9d0" },
      { label: "Abyssal", bg: "#00000f" },
      { label: "Meadow", bg: "#e8f5e9" },
      { label: "Solstice", bg: "#fff8e1" },
      { label: "Nebula", bg: "#0d001a" },
      { label: "Old Paper", bg: "#f0e0c0" },
      { label: "Aqua", bg: "#e0f7fa" },
      { label: "Inkwell", bg: "#080808" },
      { label: "Blossom", bg: "#fce4ec" },
      { label: "Thunder", bg: "#1a1a00" },
      { label: "Cobalt Frost", bg: "#e8eaf6" },
      { label: "Velvet", bg: "#12001e" },
      { label: "Savanna", bg: "#2a1e08" },
      { label: "Mist", bg: "#eceff1" },
      { label: "Sulfur", bg: "#1a1800" },
      { label: "Fossil", bg: "#e8e4dc" },
      { label: "Indigo Night", bg: "#090028" },
      { label: "Polar", bg: "#f0f8ff" },
      { label: "Lava", bg: "#120000" },
      { label: "Spring", bg: "#f1f8e9" },
      { label: "Twilight Gold", bg: "#0a0800" },
      { label: "Carbon", bg: "#121212" },
      { label: "Papaya", bg: "#fff3e0" },
      { label: "Gunmetal", bg: "#1c2028" },
      { label: "Tangerine", bg: "#1a0800" },
      { label: "Silver Screen", bg: "#f5f5f5" },
      { label: "Deep Sapphire", bg: "#001030" },
      { label: "Petal", bg: "#fdf0f8" },
      { label: "Rainforest", bg: "#001a0a" },
      { label: "Ash", bg: "#f0ede8" },
      { label: "Midnight Teal", bg: "#00141a" },
      { label: "Bronze", bg: "#1a0e00" },
      { label: "Lavender Mist", bg: "#f3e8ff" },
      { label: "Midnight Green", bg: "#001a14" },
      { label: "Candle", bg: "#fff9f0" },
      { label: "Space", bg: "#000814" },
      { label: "Garnet", bg: "#0e0006" },
      { label: "Birch", bg: "#f5f0e8" },
      { label: "Viridian", bg: "#001a12" },
      { label: "Amber Night", bg: "#100800" },
      { label: "Pearl", bg: "#fafafa" },
      { label: "Shadow", bg: "#050505" },
    ];

    const PRESETS_GRADIENT = [
      { label: "Sunrise Bloom", bg: "linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%)" },
      { label: "Ocean Breeze", bg: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)" },
      { label: "Mint Dream", bg: "linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)" },
      { label: "Purple Haze", bg: "linear-gradient(135deg, #7b2ff7 0%, #c471f5 100%)" },
      { label: "Golden Hour", bg: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" },
      { label: "Northern Lights", bg: "linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)" },
      { label: "Deep Sea", bg: "linear-gradient(135deg, #0c3483 0%, #a2b6df 100%)" },
      { label: "Peach Sunset", bg: "linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)" },
      { label: "Cosmic Dust", bg: "linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%)" },
      { label: "Cherry Cola", bg: "linear-gradient(135deg, #eb3349 0%, #f45c43 100%)" },
      { label: "Forest Canopy", bg: "linear-gradient(135deg, #134e5e 0%, #71b280 100%)" },
      { label: "Lavender Fields", bg: "linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%)" },
      { label: "Ember Fade", bg: "linear-gradient(135deg, #f83600 0%, #f9d423 100%)" },
      { label: "Arctic Aurora", bg: "linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%)" },
      { label: "Rose Quartz", bg: "linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%)" },
      { label: "Midnight City", bg: "linear-gradient(135deg, #232526 0%, #414345 100%)" },
      { label: "Mango Tango", bg: "linear-gradient(135deg, #ffe259 0%, #ffa751 100%)" },
      { label: "Sapphire Sky", bg: "linear-gradient(135deg, #0052d4 0%, #4364f7 50%, #6fb1fc 100%)" },
      { label: "Cotton Candy", bg: "linear-gradient(135deg, #f3e7e9 0%, #e3eeff 100%)" },
      { label: "Volcanic Ash", bg: "linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)" },
      { label: "Spring Meadow", bg: "linear-gradient(135deg, #c6ea8d 0%, #fe90af 100%)" },
      { label: "Blueberry", bg: "linear-gradient(135deg, #4568dc 0%, #b06ab3 100%)" },
      { label: "Sand Dune", bg: "linear-gradient(135deg, #d4a574 0%, #e8cba8 100%)" },
      { label: "Neon Pulse", bg: "linear-gradient(135deg, #00f260 0%, #0575e6 100%)" },
      { label: "Dusty Rose", bg: "linear-gradient(135deg, #d299c2 0%, #fef9d7 100%)" },
      { label: "Storm Cloud", bg: "linear-gradient(135deg, #373b44 0%, #4286f4 100%)" },
      { label: "Honey Drip", bg: "linear-gradient(135deg, #f7971e 0%, #ffd200 100%)" },
      { label: "Twilight Zone", bg: "linear-gradient(135deg, #141e30 0%, #243b55 100%)" },
      { label: "Watermelon", bg: "linear-gradient(135deg, #ff6b6b 0%, #556270 100%)" },
      { label: "Sage Mist", bg: "linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%)" },
      { label: "Tangerine Dream", bg: "linear-gradient(135deg, #ff5f6d 0%, #ffc371 100%)" },
      { label: "Galactic", bg: "linear-gradient(135deg, #654ea3 0%, #eaafc8 100%)" },
      { label: "Bamboo Shade", bg: "linear-gradient(135deg, #2d5016 0%, #89a83a 100%)" },
      { label: "Coral Reef", bg: "linear-gradient(135deg, #ff9966 0%, #ff5e62 100%)" },
      { label: "Winter Frost", bg: "linear-gradient(135deg, #e6dada 0%, #274046 100%)" },
      { label: "Plum Wine", bg: "linear-gradient(135deg, #360033 0%, #0b8793 100%)" },
      { label: "Lemon Zest", bg: "linear-gradient(135deg, #f7ff00 0%, #db36a4 100%)" },
      { label: "Shadow Play", bg: "linear-gradient(135deg, #000000 0%, #434343 100%)" },
      { label: "Tropical Punch", bg: "linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%)" },
      { label: "Moonstone", bg: "linear-gradient(135deg, #c9d6ff 0%, #e2e2e2 100%)" },
      { label: "Jade Temple", bg: "linear-gradient(135deg, #0f9b0f 0%, #000000 100%)" },
      { label: "Flamingo", bg: "linear-gradient(135deg, #f54ea2 0%, #ff7676 100%)" },
      { label: "Glacier Blue", bg: "linear-gradient(135deg, #74ebd5 0%, #acb6e5 100%)" },
      { label: "Obsidian Flame", bg: "linear-gradient(135deg, #000000 0%, #e74c3c 100%)" },
      { label: "Pastel Sky", bg: "linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%)" },
      { label: "Ruby Glow", bg: "linear-gradient(135deg, #870000 0%, #190a05 100%)" },
      { label: "Seafoam Wave", bg: "linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" },
      { label: "Blush", bg: "linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%)" },
      { label: "Iron Gray", bg: "linear-gradient(135deg, #485563 0%, #29323c 100%)" },
      { label: "Citrus Burst", bg: "linear-gradient(135deg, #f9d423 0%, #ff4e50 100%)" },
      { label: "Amethyst Cave", bg: "linear-gradient(135deg, #9d50bb 0%, #6e48aa 100%)" },
      { label: "Palm Leaf", bg: "linear-gradient(135deg, #0ba360 0%, #3cba92 100%)" },
      { label: "Copper Rust", bg: "linear-gradient(135deg, #b79891 0%, #94716b 100%)" },
      { label: "Electric Violet", bg: "linear-gradient(135deg, #4776e6 0%, #8e54e9 100%)" },
      { label: "Warm Ember", bg: "linear-gradient(135deg, #c94b4b 0%, #4b134f 100%)" },
      { label: "Fern Glow", bg: "linear-gradient(135deg, #56ab2f 0%, #a8e063 100%)" },
      { label: "Bluebell", bg: "linear-gradient(135deg, #396afc 0%, #2948ff 100%)" },
      { label: "Sandstorm", bg: "linear-gradient(135deg, #c2b280 0%, #d4a76a 100%)" },
      { label: "Orchid Dream", bg: "linear-gradient(135deg, #da22ff 0%, #9733ee 100%)" },
      { label: "Ash Cloud", bg: "linear-gradient(135deg, #606c88 0%, #3f4c6b 100%)" },
      { label: "Lava Flow", bg: "linear-gradient(135deg, #f12711 0%, #f5af19 100%)" },
      { label: "Tidal Wave", bg: "linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)" },
      { label: "Velvet Night", bg: "linear-gradient(135deg, #1f1c2c 0%, #928dab 100%)" },
      { label: "Apricot Glow", bg: "linear-gradient(135deg, #fceabb 0%, #f8b500 100%)" },
      { label: "Mystic Forest", bg: "linear-gradient(135deg, #0d3b0d 0%, #3e8e41 100%)" },
      { label: "Pink Lemonade", bg: "linear-gradient(135deg, #ffc0cb 0%, #fffacd 100%)" },
      { label: "Thunder Strike", bg: "linear-gradient(135deg, #1a1a2e 0%, #e94560 100%)" },
      { label: "Silver Lining", bg: "linear-gradient(135deg, #bdc3c7 0%, #ecf0f1 100%)" },
      { label: "Frozen Berry", bg: "linear-gradient(135deg, #6a3093 0%, #a044ff 100%)" },
      { label: "Burnt Sienna", bg: "linear-gradient(135deg, #7b4397 0%, #dc2430 100%)" },
      { label: "Pacific Rim", bg: "linear-gradient(135deg, #1cb5e0 0%, #000046 100%)" },
      { label: "Champagne", bg: "linear-gradient(135deg, #f7e7ce 0%, #d4a76a 100%)" },
      { label: "Dark Matter", bg: "linear-gradient(135deg, #000000 0%, #0f0c29 50%, #302b63 100%)" },
      { label: "Lime Soda", bg: "linear-gradient(135deg, #a8ff78 0%, #78ffd6 100%)" },
      { label: "Crimson Tide", bg: "linear-gradient(135deg, #642b73 0%, #c6426e 100%)" },
      { label: "Sky at Dusk", bg: "linear-gradient(135deg, #2980b9 0%, #6dd5fa 50%, #ffffff 100%)" },
      { label: "Caramel Swirl", bg: "linear-gradient(135deg, #c0392b 0%, #f39c12 100%)" },
      { label: "Misty Mountain", bg: "linear-gradient(135deg, #606c88 0%, #3f4c6b 100%)" },
      { label: "Electric Lime", bg: "linear-gradient(135deg, #b4ec51 0%, #429321 100%)" },
      { label: "Starlight", bg: "linear-gradient(135deg, #0f0c29 0%, #24243e 100%)" },
      { label: "Bubblegum", bg: "linear-gradient(135deg, #ff61d2 0%, #fe9090 100%)" },
      { label: "Deep Ocean", bg: "linear-gradient(135deg, #000428 0%, #004e92 100%)" },
      { label: "Autumn Blaze", bg: "linear-gradient(135deg, #d38312 0%, #a83279 100%)" },
      { label: "Zen Stone", bg: "linear-gradient(135deg, #757f9a 0%, #d7dde8 100%)" },
      { label: "Solar Flare", bg: "linear-gradient(135deg, #f37335 0%, #fdc830 100%)" },
      { label: "Wisteria", bg: "linear-gradient(135deg, #c471f5 0%, #fa71cd 100%)" },
      { label: "Frozen Lake", bg: "linear-gradient(135deg, #c2e9fb 0%, #a1c4fd 100%)" },
      { label: "Redwood", bg: "linear-gradient(135deg, #3e0000 0%, #7f1d1d 100%)" },
      { label: "Pistachio", bg: "linear-gradient(135deg, #93f9b9 0%, #1d976c 100%)" },
      { label: "Midnight Jazz", bg: "linear-gradient(135deg, #1a002e 0%, #4a148c 100%)" },
      { label: "Sunrise Peak", bg: "linear-gradient(135deg, #ff512f 0%, #f09819 100%)" },
      { label: "Steel Blue", bg: "linear-gradient(135deg, #2c3e50 0%, #3498db 100%)" },
      { label: "Blossom Rain", bg: "linear-gradient(135deg, #fce4ec 0%, #f8bbd0 50%, #f48fb1 100%)" },
      { label: "Charcoal Gold", bg: "linear-gradient(135deg, #1a1a1a 0%, #3d3d3d 100%)" },
      { label: "Aqua Marine", bg: "linear-gradient(135deg, #1a2980 0%, #26d0ce 100%)" },
      { label: "Sakura Dusk", bg: "linear-gradient(135deg, #2a0a14 0%, #c2185b 100%)" },
      { label: "Emerald Isle", bg: "linear-gradient(135deg, #005c1a 0%, #00c853 100%)" },
      { label: "Cosmic Latte", bg: "linear-gradient(135deg, #fff8e7 0%, #d4c5a9 100%)" },
    ];

    function parseGradientStr(cssStr) {
      const m = cssStr.match(/linear-gradient\((\d+)deg,\s*(.+)\)/);
      if (!m) return null;
      const angle = parseInt(m[1], 10);
      const stopParts = m[2].split(/,\s*(?=#)/);
      const stops = stopParts.map(s => {
        const sp = s.trim().match(/(#[0-9a-fA-F]{3,8})\s+([\d.]+)%/);
        if (!sp) return null;
        return { color: sp[1], pos: parseInt(sp[2], 10) };
      }).filter(Boolean);
      return { angle, stops };
    }

    const WM_POSITIONS = ["Center", "Top Left", "Top Center", "Top Right", "Bottom Left", "Bottom Center", "Bottom Right"];
    const WM_FONTS = [
      "sans-serif", "serif", "monospace", "cursive", "fantasy",
      "Arial", "Arial Black", "Georgia", "Times New Roman", "Palatino Linotype",
      "Courier New", "Lucida Console", "Verdana", "Tahoma", "Trebuchet MS",
    ];

    function getWmCoords(pos, cw, ch, ew, eh, margin = 10) {
      const m = margin;
      switch (pos) {
        case "Center":        return [(cw - ew) / 2, (ch - eh) / 2];
        case "Top Left":      return [m, m];
        case "Top Center":    return [(cw - ew) / 2, m];
        case "Top Right":     return [cw - ew - m, m];
        case "Bottom Left":   return [m, ch - eh - m];
        case "Bottom Center": return [(cw - ew) / 2, ch - eh - m];
        case "Bottom Right":  return [cw - ew - m, ch - eh - m];
        default:              return [cw - ew - m, ch - eh - m];
      }
    }

    const ASPECT_RATIOS = [
      { label: "1:1",  w: 1,  h: 1  },
      { label: "4:3",  w: 4,  h: 3  },
      { label: "3:4",  w: 3,  h: 4  },
      { label: "16:9", w: 16, h: 9  },
      { label: "9:16", w: 9,  h: 16 },
      { label: "3:2",  w: 3,  h: 2  },
      { label: "2:3",  w: 2,  h: 3  },
      { label: "Custom", w: null, h: null },
    ];

    const THEMES = {
      dark: {
        bg: "linear-gradient(160deg, #0d0d0d 0%, #1a1520 50%, #0d1117 100%)",
        text: "#e0dcd4",
        panelBg: "rgba(255,255,255,0.03)",
        panelBorder: "rgba(255,255,255,0.06)",
        inputBg: "rgba(0,0,0,0.3)",
        inputBorder: "rgba(255,255,255,0.08)",
        inputFocusBorder: "rgba(255,255,255,0.2)",
        muted: "#7a7570",
        dimmed: "#5a5550",
        stickyBg: "#0d0d0d",
        accent: "#c9a96e",
        accentGradient: "linear-gradient(135deg, #c9a96e 0%, #a67c52 100%)",
        accentBtnText: "#0d0d0d",
        scrollThumb: "#333",
        cardBg: "rgba(255,255,255,0.02)",
        cardBorder: "rgba(255,255,255,0.06)",
        cardHoverBorder: "rgba(255,255,255,0.15)",
        presetBg: "rgba(255,255,255,0.04)",
        presetBorder: "rgba(255,255,255,0.08)",
        presetHoverBg: "rgba(255,255,255,0.08)",
        presetActiveBg: "rgba(255,255,255,0.12)",
        presetActiveBorder: "rgba(255,255,255,0.2)",
        toggleTrack: "rgba(255,255,255,0.1)",
        toggleKnob: "#e0dcd4",
        badgeBg: "rgba(201,169,110,0.15)",
        secondaryBtnBg: "rgba(255,255,255,0.06)",
        secondaryBtnBorder: "rgba(201,169,110,0.3)",
        canvasBg: "rgba(0,0,0,0.25)",
        placeholderBorder: "rgba(255,255,255,0.4)",
        placeholderBg: "rgba(255,255,255,0.08)",
        placeholderText: "rgba(255,255,255,0.4)",
      },
      light: {
        bg: "linear-gradient(160deg, #f5f3f0 0%, #ebe7e2 50%, #f0ece8 100%)",
        text: "#2a2520",
        panelBg: "rgba(255,255,255,0.7)",
        panelBorder: "rgba(0,0,0,0.08)",
        inputBg: "rgba(255,255,255,0.8)",
        inputBorder: "rgba(0,0,0,0.12)",
        inputFocusBorder: "rgba(0,0,0,0.3)",
        muted: "#6a6560",
        dimmed: "#8a8580",
        accent: "#8b6914",
        accentGradient: "linear-gradient(135deg, #b8860b 0%, #8b6914 100%)",
        accentBtnText: "#ffffff",
        scrollThumb: "#ccc",
        cardBg: "rgba(255,255,255,0.6)",
        cardBorder: "rgba(0,0,0,0.08)",
        cardHoverBorder: "rgba(0,0,0,0.2)",
        presetBg: "rgba(0,0,0,0.03)",
        presetBorder: "rgba(0,0,0,0.1)",
        presetHoverBg: "rgba(0,0,0,0.06)",
        presetActiveBg: "rgba(0,0,0,0.1)",
        presetActiveBorder: "rgba(0,0,0,0.25)",
        toggleTrack: "rgba(0,0,0,0.15)",
        toggleKnob: "#8b6914",
        badgeBg: "rgba(139,105,20,0.12)",
        secondaryBtnBg: "rgba(0,0,0,0.04)",
        secondaryBtnBorder: "rgba(139,105,20,0.3)",
        canvasBg: "rgba(0,0,0,0.06)",
        placeholderBorder: "rgba(0,0,0,0.35)",
        placeholderBg: "rgba(0,0,0,0.07)",
        placeholderText: "rgba(0,0,0,0.35)",
        stickyBg: "#f0ece8",
      },
    };

    const CACHE_KEY = "collage-studio-prefs";
    function loadCache() { try { const r = localStorage.getItem(CACHE_KEY); return r ? JSON.parse(r) : {}; } catch { return {}; } }
    function saveCache(p) { try { localStorage.setItem(CACHE_KEY, JSON.stringify(p)); } catch {} }

    function GradientEditor({ gradient, onChange, t }) {
      const addStop = () => onChange({ ...gradient, stops: [...gradient.stops, { color: "#ffffff", pos: 100 }] });
      const removeStop = (i) => { if (gradient.stops.length <= 2) return; onChange({ ...gradient, stops: gradient.stops.filter((_, idx) => idx !== i) }); };
      const updateStop = (i, field, val) => onChange({ ...gradient, stops: gradient.stops.map((s, idx) => idx === i ? { ...s, [field]: val } : s) });
      return (
        <div>
          <div style={{ display: "flex", gap: 10, alignItems: "center", marginBottom: 12 }}>
            <span style={{ fontSize: 12, color: t.muted, minWidth: 38 }}>Angle</span>
            <input type="range" min={0} max={360} value={gradient.angle}
              onChange={e => onChange({ ...gradient, angle: Number(e.target.value) })}
              style={{ flex: 1 }} />
            <span style={{ fontSize: 12, color: t.text, width: 36, textAlign: "right" }}>{gradient.angle}°</span>
          </div>
          {gradient.stops.map((stop, i) => (
            <div key={i} style={{ display: "flex", gap: 8, alignItems: "center", marginBottom: 8 }}>
              <input type="color" value={stop.color} onChange={e => updateStop(i, "color", e.target.value)} />
              <input type="range" min={0} max={100} value={stop.pos} onChange={e => updateStop(i, "pos", Number(e.target.value))} style={{ flex: 1 }} />
              <span style={{ fontSize: 11, color: t.muted, width: 30 }}>{stop.pos}%</span>
              <button onClick={() => removeStop(i)} className="page-btn" style={{ padding: "2px 8px" }}>×</button>
            </div>
          ))}
          <button onClick={addStop} className="page-btn" style={{ marginTop: 4, fontSize: 12 }}>+ Add Stop</button>
        </div>
      );
    }

    function CollagePreview({ config, cards, onCardClick, onCardDrop, onReposition, canvasRef }) {
      const { aspectW, aspectH, cols, rows, outerPad, innerGap, bgRadius, cardRadius, bgType, bgColor, bgGradient, labelEnabled, labelStyle, labelPos, labelTone, labelBgEnabled, labelBgShape, labelBgColor, labelBgOpacity, labelSize, labelBgSize, wmImage, wmImagePos, wmImageOpacity, wmImageScale, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity } = config;

      const MAX_W = 680, MAX_H = 520;
      const ratio = aspectW / aspectH;
      let cw, ch;
      if (ratio >= 1) { cw = Math.min(MAX_W, MAX_H * ratio); ch = cw / ratio; }
      else { ch = Math.min(MAX_H, MAX_W / ratio); cw = ch * ratio; }

      const bgStyle = bgType === "solid"
        ? { background: bgColor }
        : { background: `linear-gradient(${bgGradient.angle}deg, ${bgGradient.stops.map(s => `${s.color} ${s.pos}%`).join(", ")})` };

      const cardW = (cw - outerPad * 2 - innerGap * (cols - 1)) / cols;
      const cardH = (ch - outerPad * 2 - innerGap * (rows - 1)) / rows;

      const panRef = useRef(null);
      const [dragOverIdx, setDragOverIdx] = useState(null);

      const handlePointerDown = useCallback((idx, card, e) => {
        if (!card?.img) return;
        e.preventDefault();
        const startX = e.clientX, startY = e.clientY;
        const startOx = card.ox ?? 50, startOy = card.oy ?? 50;
        let moved = false;
        panRef.current = { idx, startX, startY, startOx, startOy, moved };
        e.currentTarget.setPointerCapture(e.pointerId);
      }, []);

      const handlePointerMove = useCallback((idx, card, e) => {
        if (!panRef.current || panRef.current.idx !== idx) return;
        const dx = e.clientX - panRef.current.startX;
        const dy = e.clientY - panRef.current.startY;
        if (Math.abs(dx) > 3 || Math.abs(dy) > 3) panRef.current.moved = true;
        if (!panRef.current.moved) return;
        // Convert pixel delta to percentage offset (invert because panning right reveals left = ox decreases)
        const ox = Math.max(0, Math.min(100, panRef.current.startOx - (dx / cardW) * 100));
        const oy = Math.max(0, Math.min(100, panRef.current.startOy - (dy / cardH) * 100));
        onReposition(idx, ox, oy);
      }, [cardW, cardH, onReposition]);

      const handlePointerUp = useCallback((idx, e) => {
        if (!panRef.current || panRef.current.idx !== idx) return;
        const wasDrag = panRef.current.moved;
        panRef.current = null;
        if (!wasDrag) onCardClick(idx);
      }, [onCardClick]);

      const getLabel = (idx) => {
        if (labelStyle === "numbers") return String(idx + 1);
        let n = idx, label = "";
        do { label = String.fromCharCode(97 + (n % 26)) + label; n = Math.floor(n / 26) - 1; } while (n >= 0);
        return label;
      };

      const getLabelPosStyle = (pos) => {
        const pad = 6;
        const base = { position: "absolute", pointerEvents: "none", userSelect: "none" };
        switch (pos) {
          case "top-left":     return { ...base, top: pad, left: pad + 2 };
          case "top-right":    return { ...base, top: pad, right: pad + 2 };
          case "bottom-left":  return { ...base, bottom: pad, left: pad + 2 };
          case "bottom-right": return { ...base, bottom: pad, right: pad + 2 };
          case "center":       return { ...base, top: "50%", left: "50%", transform: "translate(-50%,-50%)" };
          default:             return { ...base, bottom: pad, right: pad + 2 };
        }
      };

      const cells = [];
      for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
          const idx = r * cols + c;
          const x = outerPad + c * (cardW + innerGap);
          const y = outerPad + r * (cardH + innerGap);
          const card = cards[idx];
          const ox = card?.ox ?? 50, oy = card?.oy ?? 50;
          const isDragOver = dragOverIdx === idx;
          cells.push(
            <div key={idx}
              className="card-cell"
              onDragOver={e => { e.preventDefault(); setDragOverIdx(idx); }}
              onDragLeave={() => setDragOverIdx(null)}
              onDrop={e => { e.preventDefault(); setDragOverIdx(null); onCardDrop(idx, e); }}
              onPointerDown={e => handlePointerDown(idx, card, e)}
              onPointerMove={e => handlePointerMove(idx, card, e)}
              onPointerUp={e => handlePointerUp(idx, e)}
              style={{
                position: "absolute", left: x, top: y,
                width: Math.max(0, cardW), height: Math.max(0, cardH),
                borderRadius: cardRadius,
                overflow: "hidden",
                cursor: card?.img ? "grab" : "pointer",
                boxShadow: isDragOver
                  ? "inset 0 0 0 2.5px rgba(201,169,110,0.9)"
                  : card?.img ? "none" : "inset 0 0 0 1.5px rgba(128,128,128,0.45)",
                outline: isDragOver ? "2px dashed rgba(201,169,110,0.7)" : "none",
              }}>
              {card?.img
                ? <img src={card.img} draggable={false} style={{ width: "100%", height: "100%", objectFit: "cover", objectPosition: `${ox}% ${oy}%`, display: "block", userSelect: "none" }} />
                : <div className="card-placeholder" style={{ width: "100%", height: "100%", display: "flex", alignItems: "center", justifyContent: "center" }}>
                    <span style={{ fontSize: Math.max(12, Math.min(cardW, cardH) * 0.22), lineHeight: 1 }}>{isDragOver ? "↓" : "+"}</span>
                  </div>
              }
              {labelEnabled && (() => {
                const fontSize = Math.max(10, Math.min(cardW, cardH) * 0.14) * labelSize;
                const pad = labelBgEnabled ? fontSize * 0.45 * labelBgSize : 0;
                const isCircle = labelBgEnabled && labelBgShape === "circle";
                const size = fontSize + pad * 2;
                return (
                  <div style={{
                    ...getLabelPosStyle(labelPos),
                    fontSize,
                    fontWeight: 700, lineHeight: 1,
                    color: labelTone === "light" ? "rgba(255,255,255,0.92)" : "rgba(0,0,0,0.82)",
                    textShadow: labelBgEnabled ? "none" : (labelTone === "light" ? "0 1px 4px rgba(0,0,0,0.65)" : "0 1px 3px rgba(255,255,255,0.5)"),
                    fontFamily: "monospace",
                    ...(labelBgEnabled ? {
                      background: (() => { const r=parseInt(labelBgColor.slice(1,3),16),g=parseInt(labelBgColor.slice(3,5),16),b=parseInt(labelBgColor.slice(5,7),16); return `rgba(${r},${g},${b},${labelBgOpacity})`; })(),
                      borderRadius: isCircle ? "50%" : fontSize * 0.3,
                      width: isCircle ? size : undefined,
                      height: isCircle ? size : undefined,
                      padding: isCircle ? 0 : `${pad * 0.6}px ${pad}px`,
                      display: "flex", alignItems: "center", justifyContent: "center",
                    } : {}),
                  }}>
                    {getLabel(idx)}
                  </div>
                );
              })()}
              {wmImage && wmImage.src && (() => {
                const ww = cardW * wmImageScale;
                const posMap = {
                  "Center":        { top: "50%", left: "50%", transform: `translate(-50%,-50%)` },
                  "Top Left":      { top: 10, left: 10 },
                  "Top Center":    { top: 10, left: "50%", transform: "translateX(-50%)" },
                  "Top Right":     { top: 10, right: 10 },
                  "Bottom Left":   { bottom: 10, left: 10 },
                  "Bottom Center": { bottom: 10, left: "50%", transform: "translateX(-50%)" },
                  "Bottom Right":  { bottom: 10, right: 10 },
                };
                return (
                  <img src={wmImage.src} draggable={false}
                    style={{ position: "absolute", pointerEvents: "none", width: ww, opacity: wmImageOpacity, ...(posMap[wmImagePos] || posMap["Bottom Right"]) }} />
                );
              })()}
              {wmText.trim() && (() => {
                const fontSize = wmTextSize;
                const posMap = {
                  "Center":        { top: "50%", left: "50%", transform: "translate(-50%,-50%)" },
                  "Top Left":      { top: 10, left: 10 },
                  "Top Center":    { top: 10, left: "50%", transform: "translateX(-50%)" },
                  "Top Right":     { top: 10, right: 10 },
                  "Bottom Left":   { bottom: 10, left: 10 },
                  "Bottom Center": { bottom: 10, left: "50%", transform: "translateX(-50%)" },
                  "Bottom Right":  { bottom: 10, right: 10 },
                };
                return (
                  <div style={{ position: "absolute", pointerEvents: "none", fontSize, fontFamily: wmTextFont, color: wmTextColor, opacity: wmTextOpacity, whiteSpace: "pre", ...(posMap[wmTextPos] || posMap["Bottom Center"]) }}>
                    {wmText}
                  </div>
                );
              })()}
            </div>
          );
        }
      }

      return (
        <div ref={canvasRef}
          style={{
            width: cw, height: ch, borderRadius: bgRadius,
            position: "relative", overflow: "hidden", flexShrink: 0,
            ...bgStyle,
          }}>
          {cells}
        </div>
      );
    }

    function App() {
      const cached = React.useMemo(() => loadCache(), []);
      const c = (k, fb) => cached[k] !== undefined ? cached[k] : fb;

      const [theme, setTheme] = useState(c("theme", "dark"));
      const [aspectPreset, setAspectPreset] = useState(c("aspectPreset", 0));
      const [customW, setCustomW] = useState(c("customW", 4));
      const [customH, setCustomH] = useState(c("customH", 3));
      const roundTenth = v => Math.round(v * 10) / 10;
      const [cols, setCols] = useState(c("cols", 3));
      const [rows, setRows] = useState(c("rows", 2));
      const [outerPad, setOuterPad] = useState(c("outerPad", 20));
      const [innerGap, setInnerGap] = useState(c("innerGap", 10));
      const [bgRadius, setBgRadius] = useState(c("bgRadius", 16));
      const [cardRadius, setCardRadius] = useState(c("cardRadius", 8));
      const [bgType, setBgType] = useState(c("bgType", "solid"));
      const [bgColor, setBgColor] = useState(c("bgColor", "#1e1e2e"));
      const [bgGradient, setBgGradient] = useState(c("bgGradient", {
        angle: 135,
        stops: [{ color: "#4f46e5", pos: 0 }, { color: "#7c3aed", pos: 100 }]
      }));
      const [cards, setCards] = useState({});
      const [labelEnabled, setLabelEnabled] = useState(c("labelEnabled", false));
      const [labelStyle, setLabelStyle] = useState(c("labelStyle", "letters"));
      const [labelPos, setLabelPos] = useState(c("labelPos", "bottom-right"));
      const [labelTone, setLabelTone] = useState(c("labelTone", "light"));
      const [labelBgEnabled, setLabelBgEnabled] = useState(c("labelBgEnabled", false));
      const [labelBgShape, setLabelBgShape] = useState(c("labelBgShape", "circle"));
      const [labelBgColor, setLabelBgColor] = useState(c("labelBgColor", "#8B5E3C"));
      const [labelBgOpacity, setLabelBgOpacity] = useState(c("labelBgOpacity", 1));
      const [labelSize, setLabelSize] = useState(c("labelSize", 1));
      const [labelBgSize, setLabelBgSize] = useState(c("labelBgSize", 1));
      const [zoom, setZoom] = useState(1);

      // Watermark state
      const [wmText, setWmText] = useState(c("wmText", ""));
      const [wmTextPos, setWmTextPos] = useState(c("wmTextPos", "Bottom Center"));
      const [wmTextSize, setWmTextSize] = useState(c("wmTextSize", 14));
      const [wmTextFont, setWmTextFont] = useState(c("wmTextFont", "sans-serif"));
      const [wmTextColor, setWmTextColor] = useState(c("wmTextColor", "#ffffff"));
      const [wmTextOpacity, setWmTextOpacity] = useState(c("wmTextOpacity", 0.5));
      const [wmImage, setWmImage] = useState(c("wmImage", null));
      const [wmImageUrl, setWmImageUrl] = useState(c("wmImageUrl", ""));
      const [wmImagePos, setWmImagePos] = useState(c("wmImagePos", "Bottom Right"));
      const [wmImageOpacity, setWmImageOpacity] = useState(c("wmImageOpacity", 0.5));
      const [wmImageScale, setWmImageScale] = useState(c("wmImageScale", 0.2));

      const fileInputRef = useRef(null);
      const pendingIdx = useRef(null);
      const canvasRef = useRef(null);
      const t = THEMES[theme];

      useEffect(() => {
        saveCache({ theme, aspectPreset, customW, customH, cols, rows, outerPad, innerGap, bgRadius, cardRadius, bgType, bgColor, bgGradient, exportScale, labelEnabled, labelStyle, labelPos, labelTone, labelBgEnabled, labelBgShape, labelBgColor, labelBgOpacity, labelSize, labelBgSize, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity, wmImage, wmImageUrl, wmImagePos, wmImageOpacity, wmImageScale });
      }, [theme, aspectPreset, customW, customH, cols, rows, outerPad, innerGap, bgRadius, cardRadius, bgType, bgColor, bgGradient, exportScale, labelEnabled, labelStyle, labelPos, labelTone, labelBgEnabled, labelBgShape, labelBgColor, labelBgOpacity, labelSize, labelBgSize, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity, wmImage, wmImageUrl, wmImagePos, wmImageOpacity, wmImageScale]);

      const preset = ASPECT_RATIOS[aspectPreset];
      const aspectW = preset.w ?? customW;
      const aspectH = preset.h ?? customH;
      const config = { aspectW, aspectH, cols, rows, outerPad, innerGap, bgRadius, cardRadius, bgType, bgColor, bgGradient, labelEnabled, labelStyle, labelPos, labelTone, labelBgEnabled, labelBgShape, labelBgColor, labelBgOpacity, labelSize, labelBgSize, wmImage, wmImagePos, wmImageOpacity, wmImageScale, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity };

      const ratio = aspectW / aspectH;
      const previewW = ratio >= 1 ? Math.min(680, 520 * ratio) : 520 * ratio;
      const previewH = ratio >= 1 ? previewW / ratio : Math.min(520, 680 / ratio);


      const handleCardClick = (idx) => { pendingIdx.current = idx; fileInputRef.current.click(); };
      const handleFile = (e) => {
        const file = e.target.files[0]; if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => setCards(prev => ({ ...prev, [pendingIdx.current]: { img: ev.target.result, ox: 50, oy: 50 } }));
        reader.readAsDataURL(file);
        e.target.value = "";
      };
      const handleCardDrop = useCallback((idx, e) => {
        const file = e.dataTransfer.files[0];
        if (!file || !file.type.startsWith("image/")) return;
        const reader = new FileReader();
        reader.onload = ev => setCards(prev => ({ ...prev, [idx]: { img: ev.target.result, ox: 50, oy: 50 } }));
        reader.readAsDataURL(file);
      }, []);
      const handleReposition = useCallback((idx, ox, oy) => {
        setCards(prev => ({ ...prev, [idx]: { ...prev[idx], ox, oy } }));
      }, []);

      const clearCard = (idx, e) => { e.stopPropagation(); setCards(prev => { const n = { ...prev }; delete n[idx]; return n; }); };

      const [exportScale, setExportScale] = useState(c("exportScale", 2));
      const [showClearModal, setShowClearModal] = useState(false);
      const [clearConfirmText, setClearConfirmText] = useState("");
      const clearCache = () => {
        try { localStorage.removeItem(CACHE_KEY); } catch {}
        window.location.reload();
      };

      const clearAllCards = () => setCards({});

      const handleExport = async () => {
        const el = canvasRef.current; if (!el) return;
        const scale = exportScale;
        const w = el.offsetWidth, h = el.offsetHeight;

        // Build background style
        const { bgType, bgColor, bgGradient, bgRadius } = config;
        const bgStyle = bgType === "solid"
          ? bgColor
          : `linear-gradient(${bgGradient.angle}deg, ${bgGradient.stops.map(s => `${s.color} ${s.pos}%`).join(", ")})`;

        const canvas = document.createElement("canvas");
        canvas.width = w * scale;
        canvas.height = h * scale;
        const ctx = canvas.getContext("2d");
        ctx.scale(scale, scale);

        // Draw background
        if (bgType === "solid") {
          ctx.fillStyle = bgColor;
          roundRect(ctx, 0, 0, w, h, bgRadius);
          ctx.fill();
        } else {
          const grad = ctx.createLinearGradient(
            ...gradientVector(bgGradient.angle, w, h)
          );
          bgGradient.stops.forEach(s => grad.addColorStop(s.pos / 100, s.color));
          ctx.fillStyle = grad;
          roundRect(ctx, 0, 0, w, h, bgRadius);
          ctx.fill();
        }

        // Draw cards
        const { cols, rows, outerPad, innerGap, cardRadius } = config;
        const cardW = (w - outerPad * 2 - innerGap * (cols - 1)) / cols;
        const cardH = (h - outerPad * 2 - innerGap * (rows - 1)) / rows;

        const { labelEnabled, labelStyle, labelPos, labelTone, labelBgEnabled, labelBgShape, labelBgColor, labelBgOpacity, labelSize, labelBgSize } = config;
        const getExportLabel = (idx) => {
          if (labelStyle === "numbers") return String(idx + 1);
          let n = idx, label = "";
          do { label = String.fromCharCode(97 + (n % 26)) + label; n = Math.floor(n / 26) - 1; } while (n >= 0);
          return label;
        };
        const getExportLabelXY = (pos, x, y, cardW, cardH) => {
          const pad = 8;
          switch (pos) {
            case "top-left":     return { tx: x + pad,           ty: y + pad,           align: "left",   base: "top" };
            case "top-right":    return { tx: x + cardW - pad,   ty: y + pad,           align: "right",  base: "top" };
            case "bottom-left":  return { tx: x + pad,           ty: y + cardH - pad,   align: "left",   base: "bottom" };
            case "bottom-right": return { tx: x + cardW - pad,   ty: y + cardH - pad,   align: "right",  base: "bottom" };
            case "center":       return { tx: x + cardW / 2,     ty: y + cardH / 2,     align: "center", base: "middle" };
            default:             return { tx: x + cardW - pad,   ty: y + cardH - pad,   align: "right",  base: "bottom" };
          }
        };

        // Pre-load watermark image once so each card can draw it
        let wmImgLoaded = null;
        if (wmImage && wmImage.src) {
          wmImgLoaded = await new Promise(resolve => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = wmImage.src;
          });
        }

        const drawCard = (idx) => new Promise(resolve => {
          const r = Math.floor(idx / cols), c = idx % cols;
          const x = outerPad + c * (cardW + innerGap);
          const y = outerPad + r * (cardH + innerGap);
          const card = cards[idx];
          if (card?.img) {
            const img = new Image();
            img.onload = () => {
              ctx.save();
              roundRect(ctx, x, y, cardW, cardH, cardRadius);
              ctx.clip();
              // Cover fit with position offset
              const iw = img.naturalWidth, ih = img.naturalHeight;
              const scale2 = Math.max(cardW / iw, cardH / ih);
              const dw = iw * scale2, dh = ih * scale2;
              const ox = (card.ox ?? 50) / 100, oy = (card.oy ?? 50) / 100;
              const dx = x + (cardW - dw) * ox, dy = y + (cardH - dh) * oy;
              ctx.drawImage(img, dx, dy, dw, dh);
              if (labelEnabled) {
                const fontSize = Math.max(10, Math.min(cardW, cardH) * 0.14) * labelSize;
                const text = getExportLabel(idx);
                const { tx, ty, align, base } = getExportLabelXY(labelPos, x, y, cardW, cardH);
                ctx.font = `bold ${fontSize}px monospace`;
                if (labelBgEnabled) {
                  ctx.shadowBlur = 0;
                  const pad = fontSize * 0.45 * labelBgSize;
                  const metrics = ctx.measureText(text);
                  const tw = metrics.width;
                  const th = fontSize;
                  let bgX, bgY;
                  if (align === "left")  bgX = tx;
                  else if (align === "right") bgX = tx - tw;
                  else bgX = tx - tw / 2;
                  if (base === "top")    bgY = ty;
                  else if (base === "bottom") bgY = ty - th;
                  else bgY = ty - th / 2;
                  { const r=parseInt(labelBgColor.slice(1,3),16),g=parseInt(labelBgColor.slice(3,5),16),b=parseInt(labelBgColor.slice(5,7),16); ctx.fillStyle=`rgba(${r},${g},${b},${labelBgOpacity})`; }
                  if (labelBgShape === "circle") {
                    const r = (Math.max(tw, th) / 2) + pad;
                    ctx.beginPath();
                    ctx.arc(bgX + tw / 2, bgY + th / 2, r, 0, Math.PI * 2);
                    ctx.fill();
                  } else {
                    const rx = fontSize * 0.3;
                    const bx = bgX - pad, by = bgY - pad * 0.6, bw = tw + pad * 2, bh = th + pad * 1.2;
                    ctx.beginPath();
                    ctx.moveTo(bx + rx, by);
                    ctx.lineTo(bx + bw - rx, by);
                    ctx.arcTo(bx + bw, by, bx + bw, by + bh, rx);
                    ctx.lineTo(bx + bw, by + bh - rx);
                    ctx.arcTo(bx + bw, by + bh, bx, by + bh, rx);
                    ctx.lineTo(bx + rx, by + bh);
                    ctx.arcTo(bx, by + bh, bx, by, rx);
                    ctx.lineTo(bx, by + rx);
                    ctx.arcTo(bx, by, bx + bw, by, rx);
                    ctx.closePath();
                    ctx.fill();
                  }
                  ctx.fillStyle = labelTone === "light" ? "rgba(255,255,255,0.92)" : "rgba(0,0,0,0.82)";
                  ctx.textAlign = align;
                  ctx.textBaseline = base;
                  ctx.fillText(text, tx, ty);
                } else {
                  ctx.fillStyle = labelTone === "light" ? "rgba(255,255,255,0.92)" : "rgba(0,0,0,0.82)";
                  ctx.shadowColor = labelTone === "light" ? "rgba(0,0,0,0.65)" : "rgba(255,255,255,0.5)";
                  ctx.shadowBlur = 4;
                  ctx.textAlign = align;
                  ctx.textBaseline = base;
                  ctx.fillText(text, tx, ty);
                }
              }
              // Draw watermark image on this card
              if (wmImgLoaded) {
                const ww = cardW * wmImageScale;
                const wh = (wmImgLoaded.naturalHeight / wmImgLoaded.naturalWidth) * ww;
                const [wix, wiy] = getWmCoords(wmImagePos, cardW, cardH, ww, wh);
                ctx.save();
                ctx.globalAlpha = wmImageOpacity;
                ctx.drawImage(wmImgLoaded, x + wix, y + wiy, ww, wh);
                ctx.restore();
              }
              // Draw watermark text on this card
              if (wmText.trim()) {
                ctx.save();
                ctx.globalAlpha = wmTextOpacity;
                ctx.font = `${wmTextSize}px ${wmTextFont}`;
                ctx.fillStyle = wmTextColor;
                const lineHeight = wmTextSize * 1.3;
                const wmLines = wmText.split("\n");
                const maxWidth = Math.max(...wmLines.map(l => ctx.measureText(l).width));
                const totalHeight = lineHeight * wmLines.length;
                const [wtx, wty] = getWmCoords(wmTextPos, cardW, cardH, maxWidth, totalHeight);
                wmLines.forEach((line, i) => {
                  const lw = ctx.measureText(line).width;
                  const lx = x + wtx + (maxWidth - lw) / 2;
                  ctx.fillText(line, lx, y + wty + wmTextSize + i * lineHeight);
                });
                ctx.restore();
              }
              ctx.restore();
              resolve();
            };
            img.onerror = resolve;
            img.src = card.img;
          } else {
            resolve();
          }
        });

        const indices = [];
        for (let i = 0; i < rows * cols; i++) indices.push(i);
        await Promise.all(indices.map(drawCard));

        canvas.toBlob(blob => {
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a"); a.href = url; a.download = `collage_${new Date().toISOString().replace(/[:.]/g, "-")}.png`; a.click();
          URL.revokeObjectURL(url);
        }, "image/png");
      };

      function roundRect(ctx, x, y, w, h, r) {
        r = Math.min(r, w / 2, h / 2);
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h - r);
        ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        ctx.lineTo(x + r, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - r);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
      }

      function gradientVector(angle, w, h) {
        const rad = (angle - 90) * Math.PI / 180;
        const len = Math.sqrt(w * w + h * h);
        const cx = w / 2, cy = h / 2;
        const dx = Math.cos(rad) * len / 2, dy = Math.sin(rad) * len / 2;
        return [cx - dx, cy - dy, cx + dx, cy + dy];
      }

      return (
        <div style={{ height: "100vh", overflow: "hidden", background: t.bg, color: t.text, fontFamily: "'Segoe UI', system-ui, sans-serif", transition: "background 0.3s, color 0.3s" }}>
          <style>{`
            * { box-sizing: border-box; margin: 0; padding: 0; }
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: ${t.scrollThumb}; border-radius: 3px; }
            .panel { background: ${t.panelBg}; border: 1px solid ${t.panelBorder}; border-radius: 14px; padding: 20px 22px; }
            .label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.6px; color: ${t.muted}; margin-bottom: 10px; font-weight: 600; }
            input[type="range"] { -webkit-appearance: none; width: 100%; height: 4px; background: ${t.toggleTrack}; border-radius: 2px; outline: none; }
            input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: ${t.toggleKnob}; cursor: pointer; }
            input[type="color"] { -webkit-appearance: none; border: 2px solid ${t.inputBorder}; border-radius: 8px; width: 40px; height: 36px; cursor: pointer; overflow: hidden; padding: 2px; background: transparent; }
            input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
            input[type="color"]::-webkit-color-swatch { border: none; border-radius: 5px; }
            input[type="number"] { background: ${t.inputBg}; border: 1px solid ${t.inputBorder}; border-radius: 8px; color: ${t.text}; padding: 6px 10px; font-size: 13px; width: 64px; outline: none; transition: border-color 0.2s; }
            input[type="number"]:focus { border-color: ${t.inputFocusBorder}; }
            .preset-btn { padding: 6px 13px; border-radius: 8px; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.muted}; font-size: 12px; cursor: pointer; transition: all 0.2s; font-weight: 500; }
            .preset-btn:hover { background: ${t.presetHoverBg}; color: ${t.text}; }
            .preset-btn.active { background: ${t.presetActiveBg}; color: ${theme === "dark" ? "#fff" : "#000"}; border-color: ${t.presetActiveBorder}; }
            .gen-btn { width: 100%; padding: 14px; border-radius: 12px; border: none; font-size: 14px; font-weight: 700; cursor: pointer; letter-spacing: 0.5px; transition: all 0.25s; }
            .gen-btn.primary { background: ${t.accentGradient}; color: ${t.accentBtnText}; }
            .gen-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 8px 30px rgba(201,169,110,0.25); }
            .gen-btn.secondary { background: ${t.secondaryBtnBg}; color: ${t.accent}; border: 1px solid ${t.secondaryBtnBorder}; }
            .gen-btn.secondary:hover { background: rgba(201,169,110,0.1); }
            .badge { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; background: ${t.badgeBg}; color: ${t.accent}; }
            .page-btn { padding: 4px 10px; border-radius: 6px; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.muted}; font-size: 12px; cursor: pointer; transition: all 0.2s; }
            .page-btn:hover { background: ${t.presetHoverBg}; color: ${t.text}; }
            .theme-toggle-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.muted}; font-size: 16px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; padding: 0; }
            .theme-toggle-btn:hover { background: ${t.presetHoverBg}; color: ${t.text}; }
            .card-placeholder { background: ${t.placeholderBg}; border: 1.5px dashed ${t.placeholderBorder}; color: ${t.placeholderText}; transition: background 0.2s, border-color 0.2s; }
            .card-cell:hover .card-placeholder { border-color: ${t.accent}; color: ${t.accent}; }
            .card-cell:active { cursor: grabbing !important; }
            .slider-row { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
            .slider-label { font-size: 12px; color: ${t.muted}; min-width: 110px; }
            .slider-value { font-size: 12px; color: ${t.text}; width: 38px; text-align: right; font-variant-numeric: tabular-nums; }
            .clear-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 9999; }
            .clear-modal { background: ${theme === "dark" ? "#1a1520" : "#f5f3f0"}; border: 1px solid ${t.presetBorder}; border-radius: 12px; padding: 28px 32px; max-width: 380px; width: 90%; }
            .clear-modal h3 { margin: 0 0 10px; color: ${t.text}; font-size: 17px; }
          `}</style>

          {/* Header */}
          <div style={{ padding: "18px 40px", maxWidth: 1440, margin: "0 auto" }}>
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <div>
                <div style={{ display: "flex", alignItems: "center", gap: 14, marginBottom: 4 }}>
                  <div style={{ fontSize: 28, fontWeight: 700, color: t.text, letterSpacing: "-0.5px" }}>
                    Collage <span style={{ color: t.accent }}>Studio</span>
                  </div>
                  <span className="badge">v1.7</span>
                </div>
                <p style={{ color: t.dimmed, fontSize: 13, letterSpacing: "0.3px" }}>
                  Compose image collages with full layout control
                </p>
              </div>
              <div style={{ display: "flex", gap: 8 }}>
                <button className="theme-toggle-btn" onClick={() => setTheme(theme === "dark" ? "light" : "dark")} title={theme === "dark" ? "Switch to light mode" : "Switch to dark mode"}>
                  {theme === "dark" ? "☀️" : "🌙"}
                </button>
                <button className="theme-toggle-btn" onClick={() => { setClearConfirmText(""); setShowClearModal(true); }} title="Clear all saved preferences">
                  🗑️
                </button>
                <a href="/" className="theme-toggle-btn" title="Back to homepage" style={{ textDecoration: "none", display: "inline-flex", alignItems: "center", justifyContent: "center" }}>
                  🏠
                </a>
              </div>

              {showClearModal && (
                <div className="clear-modal-overlay" onClick={() => setShowClearModal(false)}>
                  <div className="clear-modal" onClick={e => e.stopPropagation()}>
                    <h3>Clear saved preferences?</h3>
                    <p style={{ color: t.muted, fontSize: 13, marginBottom: 16 }}>
                      This will erase all your saved settings and reload the page. Type <strong>CLEAR</strong> to confirm.
                    </p>
                    <input
                      type="text"
                      value={clearConfirmText}
                      onChange={e => setClearConfirmText(e.target.value)}
                      placeholder="Type CLEAR"
                      style={{ width: "100%", padding: "8px 12px", borderRadius: 8, border: `1px solid ${t.inputBorder}`, background: t.inputBg, color: t.text, fontSize: 14, marginBottom: 14, outline: "none" }}
                    />
                    <div style={{ display: "flex", gap: 8, justifyContent: "flex-end" }}>
                      <button className="page-btn" onClick={() => setShowClearModal(false)}>Cancel</button>
                      <button className="gen-btn primary" style={{ width: "auto", padding: "8px 18px" }}
                        disabled={clearConfirmText !== "CLEAR"}
                        onClick={clearCache}>
                        Clear & Reload
                      </button>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Body */}
          <div style={{ display: "grid", gridTemplateColumns: "360px 1fr", gap: 24, padding: "0 40px 0", maxWidth: 1440, margin: "0 auto", height: "calc(100vh - 96px)" }}>

            {/* Left Panel */}
            <div style={{ display: "flex", flexDirection: "column", overflow: "hidden" }}>
            <div style={{ overflowY: "auto", paddingRight: 6, display: "flex", flexDirection: "column", gap: 16, paddingBottom: 8, flex: 1 }}>

              {/* Aspect Ratio */}
              <div className="panel">
                <div className="label">Aspect Ratio</div>
                <div style={{ display: "flex", flexWrap: "wrap", gap: 6, marginBottom: preset.w === null ? 12 : 0 }}>
                  {ASPECT_RATIOS.map((r, i) => (
                    <button key={i} className={"preset-btn" + (aspectPreset === i ? " active" : "")} onClick={() => setAspectPreset(i)}>
                      {r.label}
                    </button>
                  ))}
                </div>
                {preset.w === null && (
                  <div style={{ display: "flex", alignItems: "center", gap: 10, marginTop: 10 }}>
                    <input type="number" min={0.1} max={32} step={0.1} value={customW} onChange={e => setCustomW(Math.max(0.1, roundTenth(Number(e.target.value))))} />
                    <span style={{ color: t.dimmed, fontSize: 16 }}>:</span>
                    <input type="number" min={0.1} max={32} step={0.1} value={customH} onChange={e => setCustomH(Math.max(0.1, roundTenth(Number(e.target.value))))} />
                    <span style={{ fontSize: 12, color: t.dimmed }}>W : H</span>
                  </div>
                )}
              </div>

              {/* Grid */}
              <div className="panel">
                <div className="label">Grid Layout</div>
                <div className="slider-row">
                  <span className="slider-label">Columns</span>
                  <input type="range" min={1} max={8} value={cols} onChange={e => setCols(Number(e.target.value))} />
                  <span className="slider-value">{cols}</span>
                </div>
                <div className="slider-row" style={{ marginBottom: 8 }}>
                  <span className="slider-label">Rows</span>
                  <input type="range" min={1} max={8} value={rows} onChange={e => setRows(Number(e.target.value))} />
                  <span className="slider-value">{rows}</span>
                </div>
                <div style={{ fontSize: 12, color: t.dimmed }}>{cols * rows} card{cols * rows !== 1 ? "s" : ""} total</div>
              </div>

              {/* Spacing */}
              <div className="panel">
                <div className="label">Spacing</div>
                <div className="slider-row">
                  <span className="slider-label">Outer Padding</span>
                  <input type="range" min={0} max={80} value={outerPad} onChange={e => setOuterPad(Number(e.target.value))} />
                  <span className="slider-value">{outerPad}px</span>
                </div>
                <div className="slider-row" style={{ marginBottom: 0 }}>
                  <span className="slider-label">Card Gap</span>
                  <input type="range" min={0} max={60} value={innerGap} onChange={e => setInnerGap(Number(e.target.value))} />
                  <span className="slider-value">{innerGap}px</span>
                </div>
              </div>

              {/* Corner Radius */}
              <div className="panel">
                <div className="label">Corner Radius</div>
                <div className="slider-row">
                  <span className="slider-label">Background</span>
                  <input type="range" min={0} max={80} value={bgRadius} onChange={e => setBgRadius(Number(e.target.value))} />
                  <span className="slider-value">{bgRadius}px</span>
                </div>
                <div className="slider-row" style={{ marginBottom: 0 }}>
                  <span className="slider-label">Cards</span>
                  <input type="range" min={0} max={80} value={cardRadius} onChange={e => setCardRadius(Number(e.target.value))} />
                  <span className="slider-value">{cardRadius}px</span>
                </div>
              </div>

              {/* Background */}
              <div className="panel">
                <div className="label">Background</div>
                <div style={{ display: "flex", gap: 8, marginBottom: 14 }}>
                  {["solid", "gradient"].map(tp => (
                    <button key={tp} className={"preset-btn" + (bgType === tp ? " active" : "")}
                      onClick={() => setBgType(tp)}
                      style={{ textTransform: "capitalize", flex: 1 }}>
                      {tp}
                    </button>
                  ))}
                </div>
                {bgType === "solid"
                  ? (
                    <div>
                      <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 12 }}>
                        <input type="color" value={bgColor} onChange={e => setBgColor(e.target.value)} />
                        <span style={{ fontSize: 13, color: t.muted, fontFamily: "monospace" }}>{bgColor}</span>
                      </div>
                      <div style={{ display: "flex", flexWrap: "wrap", gap: 6, maxHeight: 120, overflowY: "auto", paddingRight: 4 }}>
                        {PRESETS_SOLID.map((p, i) => (
                          <button key={i} title={p.label} onClick={() => setBgColor(p.bg)}
                            style={{
                              width: 24, height: 24, borderRadius: 6, border: bgColor === p.bg ? `2px solid ${t.accent}` : `1px solid ${t.inputBorder}`,
                              background: p.bg, cursor: "pointer", flexShrink: 0, padding: 0,
                            }} />
                        ))}
                      </div>
                    </div>
                  )
                  : (
                    <div>
                      <GradientEditor gradient={bgGradient} onChange={setBgGradient} t={t} />
                      <div style={{ marginTop: 12 }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 8, textTransform: "uppercase", letterSpacing: "1.2px", fontWeight: 600 }}>Presets</div>
                        <div style={{ display: "flex", flexWrap: "wrap", gap: 6, maxHeight: 120, overflowY: "auto", paddingRight: 4 }}>
                          {PRESETS_GRADIENT.map((p, i) => {
                            const parsed = parseGradientStr(p.bg);
                            const isActive = parsed && bgGradient.angle === parsed.angle && bgGradient.stops.length === parsed.stops.length && bgGradient.stops.every((s, j) => s.color === parsed.stops[j]?.color && s.pos === parsed.stops[j]?.pos);
                            return (
                              <button key={i} title={p.label}
                                onClick={() => { const g = parseGradientStr(p.bg); if (g) setBgGradient(g); }}
                                style={{
                                  width: 24, height: 24, borderRadius: 6,
                                  border: isActive ? `2px solid ${t.accent}` : `1px solid ${t.inputBorder}`,
                                  background: p.bg, cursor: "pointer", flexShrink: 0, padding: 0,
                                }} />
                            );
                          })}
                        </div>
                      </div>
                    </div>
                  )
                }
              </div>

              {/* Image Labels */}
              <div className="panel">
                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: labelEnabled ? 12 : 0 }}>
                  <div className="label" style={{ marginBottom: 0 }}>Image Labels</div>
                  <button
                    onClick={() => setLabelEnabled(v => !v)}
                    style={{
                      width: 40, height: 22, borderRadius: 11, border: "none", cursor: "pointer", position: "relative",
                      background: labelEnabled ? t.accent : t.inputBorder, transition: "background 0.2s",
                    }}>
                    <span style={{
                      position: "absolute", top: 3, left: labelEnabled ? 20 : 3,
                      width: 16, height: 16, borderRadius: "50%", background: "#fff",
                      transition: "left 0.2s", display: "block",
                    }} />
                  </button>
                </div>
                {labelEnabled && (
                  <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                    {/* Style */}
                    <div style={{ display: "flex", gap: 8 }}>
                      {[["letters", "a, b, c…"], ["numbers", "1, 2, 3…"]].map(([val, lbl]) => (
                        <button key={val} className={"preset-btn" + (labelStyle === val ? " active" : "")}
                          onClick={() => setLabelStyle(val)}
                          style={{ flex: 1, fontSize: 12 }}>
                          {lbl}
                        </button>
                      ))}
                    </div>
                    {/* Label Size */}
                    <div className="slider-row" style={{ marginBottom: 0 }}>
                      <span style={{ fontSize: 11, color: t.muted, textTransform: "uppercase", letterSpacing: "1.1px", fontWeight: 600, whiteSpace: "nowrap" }}>Size</span>
                      <input type="range" min={0.5} max={3} step={0.1} value={labelSize}
                        onChange={e => setLabelSize(Number(e.target.value))}
                        style={{ flex: 1 }} />
                      <span className="slider-value" style={{ width: 36 }}>{labelSize.toFixed(1)}×</span>
                    </div>
                    {/* Position */}
                    <div>
                      <div style={{ fontSize: 11, color: t.muted, marginBottom: 6, textTransform: "uppercase", letterSpacing: "1.1px", fontWeight: 600 }}>Position</div>
                      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 5 }}>
                        {[
                          ["top-left", "↖"], ["", ""], ["top-right", "↗"],
                          ["", ""], ["center", "⊙"], ["", ""],
                          ["bottom-left", "↙"], ["", ""], ["bottom-right", "↘"],
                        ].map(([val, icon], i) =>
                          val ? (
                            <button key={val} className={"preset-btn" + (labelPos === val ? " active" : "")}
                              onClick={() => setLabelPos(val)}
                              title={val.replace("-", " ")}
                              style={{ fontSize: 14, padding: "4px 0", lineHeight: 1 }}>
                              {icon}
                            </button>
                          ) : <div key={i} />
                        )}
                      </div>
                    </div>
                    {/* Tone */}
                    <div style={{ display: "flex", gap: 8 }}>
                      {[["light", "Light"], ["dark", "Dark"]].map(([val, lbl]) => (
                        <button key={val} className={"preset-btn" + (labelTone === val ? " active" : "")}
                          onClick={() => setLabelTone(val)}
                          style={{ flex: 1, fontSize: 12 }}>
                          {lbl}
                        </button>
                      ))}
                    </div>
                    {/* Background */}
                    <div>
                      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: labelBgEnabled ? 8 : 0 }}>
                        <div style={{ fontSize: 11, color: t.muted, textTransform: "uppercase", letterSpacing: "1.1px", fontWeight: 600 }}>Background</div>
                        <button
                          onClick={() => setLabelBgEnabled(v => !v)}
                          style={{
                            width: 32, height: 18, borderRadius: 9, border: "none", cursor: "pointer", position: "relative",
                            background: labelBgEnabled ? t.accent : t.inputBorder, transition: "background 0.2s",
                          }}>
                          <span style={{
                            position: "absolute", top: 2, left: labelBgEnabled ? 14 : 2,
                            width: 14, height: 14, borderRadius: "50%", background: "#fff",
                            transition: "left 0.2s", display: "block",
                          }} />
                        </button>
                      </div>
                      {labelBgEnabled && (
                        <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                          <div style={{ display: "flex", gap: 8 }}>
                            {[["circle", "● Circle"], ["rect", "▬ Rect"]].map(([val, lbl]) => (
                              <button key={val} className={"preset-btn" + (labelBgShape === val ? " active" : "")}
                                onClick={() => setLabelBgShape(val)}
                                style={{ flex: 1, fontSize: 12 }}>
                                {lbl}
                              </button>
                            ))}
                          </div>
                          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                            <div style={{ fontSize: 11, color: t.muted, textTransform: "uppercase", letterSpacing: "1.1px", fontWeight: 600, whiteSpace: "nowrap" }}>Color</div>
                            <input type="color" value={labelBgColor} onChange={e => setLabelBgColor(e.target.value)}
                              style={{ width: 32, height: 24, border: `1px solid ${t.inputBorder}`, borderRadius: 4, cursor: "pointer", padding: 1, background: "none" }} />
                            <span style={{ fontSize: 11, color: t.muted, fontFamily: "monospace" }}>{labelBgColor}</span>
                          </div>
                          <div className="slider-row" style={{ marginBottom: 0 }}>
                            <span style={{ fontSize: 11, color: t.muted, textTransform: "uppercase", letterSpacing: "1.1px", fontWeight: 600, whiteSpace: "nowrap" }}>Opacity</span>
                            <input type="range" min={0} max={1} step={0.05} value={labelBgOpacity}
                              onChange={e => setLabelBgOpacity(Number(e.target.value))}
                              style={{ flex: 1 }} />
                            <span className="slider-value" style={{ width: 36 }}>{Math.round(labelBgOpacity * 100)}%</span>
                          </div>
                          <div className="slider-row" style={{ marginBottom: 0 }}>
                            <span style={{ fontSize: 11, color: t.muted, textTransform: "uppercase", letterSpacing: "1.1px", fontWeight: 600, whiteSpace: "nowrap" }}>Size</span>
                            <input type="range" min={0.5} max={3} step={0.1} value={labelBgSize}
                              onChange={e => setLabelBgSize(Number(e.target.value))}
                              style={{ flex: 1 }} />
                            <span className="slider-value" style={{ width: 36 }}>{labelBgSize.toFixed(1)}×</span>
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                )}
              </div>

              {/* Watermark */}
              <div className="panel">
                <div className="label">Watermark</div>
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  {/* Text watermark */}
                  <div>
                    <div style={{ fontSize: 12, color: t.muted, marginBottom: 4 }}>Text</div>
                    <textarea
                      value={wmText}
                      onChange={e => setWmText(e.target.value)}
                      placeholder="e.g. © My Company"
                      style={{ width: "100%", minHeight: 54, padding: "6px 8px", borderRadius: 6, fontSize: 11, background: t.inputBg, border: `1px solid ${t.inputBorder}`, color: t.text, fontFamily: "monospace", resize: "vertical" }}
                    />
                  </div>
                  {wmText.trim() && (
                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10 }}>
                      <div>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Position</div>
                        <select value={wmTextPos} onChange={e => setWmTextPos(e.target.value)} style={{ width: "100%", background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "6px", fontSize: 12 }}>
                          {WM_POSITIONS.map(p => <option key={p} value={p}>{p}</option>)}
                        </select>
                      </div>
                      <div>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Color</div>
                        <input type="color" value={wmTextColor} onChange={e => setWmTextColor(e.target.value)} style={{ width: 36, height: 36 }} />
                      </div>
                      <div style={{ gridColumn: "1 / -1" }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Font</div>
                        <select value={wmTextFont} onChange={e => setWmTextFont(e.target.value)} style={{ width: "100%", background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "6px", fontSize: 12, fontFamily: wmTextFont }}>
                          {WM_FONTS.map(f => <option key={f} value={f} style={{ fontFamily: f }}>{f}</option>)}
                        </select>
                      </div>
                      <div style={{ gridColumn: "1 / -1" }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Size — {wmTextSize}px</div>
                        <input type="range" min={0} max={72} step={0.25} value={wmTextSize} onChange={e => setWmTextSize(+e.target.value)} style={{ width: "100%" }} />
                      </div>
                      <div style={{ gridColumn: "1 / -1" }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Opacity — {Math.round(wmTextOpacity * 100)}%</div>
                        <input type="range" min={0} max={100} step={1} value={Math.round(wmTextOpacity * 100)} onChange={e => setWmTextOpacity(+e.target.value / 100)} style={{ width: "100%" }} />
                      </div>
                    </div>
                  )}
                  {/* Image watermark */}
                  <div>
                    <div style={{ fontSize: 12, color: t.muted, marginBottom: 4 }}>Logo / Image</div>
                    <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                      <div style={{ display: "flex", gap: 6 }}>
                        <input
                          type="text"
                          placeholder="Image URL"
                          value={wmImageUrl}
                          onChange={e => setWmImageUrl(e.target.value)}
                          onBlur={e => {
                            const url = e.target.value.trim();
                            if (url) setWmImage({ src: url, name: url.split("/").pop() });
                            else setWmImage(null);
                          }}
                          style={{ flex: 1, background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "6px 8px", fontSize: 11 }}
                        />
                        <button onClick={() => { setWmImage(null); setWmImageUrl(""); }} style={{ background: "none", border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.muted, cursor: "pointer", fontSize: 11, padding: "4px 8px" }}>✕</button>
                      </div>
                      <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                        <label style={{ padding: "5px 10px", borderRadius: 6, border: `1px solid ${t.inputBorder}`, background: t.inputBg, color: t.muted, fontSize: 11, cursor: "pointer" }}>
                          Choose File
                          <input type="file" accept="image/*" style={{ display: "none" }} onChange={e => {
                            const file = e.target.files[0];
                            if (file) {
                              const reader = new FileReader();
                              reader.onload = ev => { setWmImage({ src: ev.target.result, name: file.name }); setWmImageUrl(file.name); };
                              reader.readAsDataURL(file);
                            }
                          }} />
                        </label>
                        {wmImage && <span style={{ fontSize: 10, color: t.muted, flex: 1, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{wmImage.name}</span>}
                      </div>
                    </div>
                  </div>
                  {wmImage && (
                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10 }}>
                      <div style={{ gridColumn: "1 / -1" }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Position</div>
                        <select value={wmImagePos} onChange={e => setWmImagePos(e.target.value)} style={{ width: "100%", background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "6px", fontSize: 12 }}>
                          {WM_POSITIONS.map(p => <option key={p} value={p}>{p}</option>)}
                        </select>
                      </div>
                      <div>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Scale — {Math.round(wmImageScale * 100)}%</div>
                        <input type="range" min={1} max={100} step={1} value={Math.round(wmImageScale * 100)} onChange={e => setWmImageScale(+e.target.value / 100)} style={{ width: "100%" }} />
                      </div>
                      <div>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Opacity — {Math.round(wmImageOpacity * 100)}%</div>
                        <input type="range" min={0} max={100} step={1} value={Math.round(wmImageOpacity * 100)} onChange={e => setWmImageOpacity(+e.target.value / 100)} style={{ width: "100%" }} />
                      </div>
                    </div>
                  )}
                </div>
              </div>

              {/* Export Scale */}
              <div className="panel">
                <div className="label">Export Scale</div>
                <div className="slider-row" style={{ marginBottom: 0 }}>
                  <input type="range" min={0.5} max={8} step={0.5} value={exportScale}
                    onChange={e => setExportScale(Number(e.target.value))}
                    style={{ flex: 1 }} />
                  <span className="slider-value" style={{ width: 44 }}>{exportScale}×</span>
                </div>
              </div>

            </div>
              {/* Actions */}
              <div style={{ position: "sticky", bottom: 0, display: "flex", flexDirection: "column", gap: 10, paddingTop: 12, paddingBottom: 16, paddingRight: 8, background: t.stickyBg, zIndex: 1 }}>
                <button className="gen-btn secondary" onClick={clearAllCards}>Clear</button>
                <button className="gen-btn primary" onClick={handleExport}>Export</button>
              </div>
            </div>

            {/* Preview Area */}
            <div style={{
              display: "flex", flexDirection: "column",
              background: t.canvasBg, borderRadius: 14, border: `1px solid ${t.panelBorder}`,
              overflow: "hidden", position: "relative",
            }}>
              {/* Zoom controls */}
              <div style={{ display: "flex", alignItems: "center", justifyContent: "center", gap: 8, padding: "8px 12px", borderBottom: `1px solid ${t.panelBorder}`, flexShrink: 0 }}>
                <button onClick={() => setZoom(z => Math.max(0.25, Math.round((z - 0.25) * 100) / 100))} style={{ width: 28, height: 28, borderRadius: 6, border: `1px solid ${t.panelBorder}`, background: t.inputBg, color: t.text, cursor: "pointer", fontSize: 16, display: "flex", alignItems: "center", justifyContent: "center" }}>−</button>
                <span style={{ fontSize: 13, color: t.muted, minWidth: 44, textAlign: "center" }}>{Math.round(zoom * 100)}%</span>
                <button onClick={() => setZoom(z => Math.min(3, Math.round((z + 0.25) * 100) / 100))} style={{ width: 28, height: 28, borderRadius: 6, border: `1px solid ${t.panelBorder}`, background: t.inputBg, color: t.text, cursor: "pointer", fontSize: 16, display: "flex", alignItems: "center", justifyContent: "center" }}>+</button>
                <button onClick={() => setZoom(1)} style={{ fontSize: 12, padding: "3px 8px", borderRadius: 6, border: `1px solid ${t.panelBorder}`, background: t.inputBg, color: t.muted, cursor: "pointer" }}>Reset</button>
              </div>
              <div style={{ flex: 1, overflow: "auto", display: "flex", alignItems: "center", justifyContent: "center" }}>
                <div style={{ transform: `scale(${zoom})`, transformOrigin: "center center", transition: "transform 0.15s" }}>
                  <CollagePreview config={config} cards={cards} onCardClick={handleCardClick} onCardDrop={handleCardDrop} onReposition={handleReposition} canvasRef={canvasRef} />
                </div>
              </div>
            </div>
          </div>

          <input type="file" accept="image/*" ref={fileInputRef} style={{ display: "none" }} onChange={handleFile} />
        </div>
      );
    }

    ReactDOM.createRoot(document.getElementById("root")).render(<App />);
  </script>
</body>
</html>
