<?php
define('STUDIO_AUTH', true);
require_once __DIR__ . '/../studio_src/config.php';
require_once __DIR__ . '/../studio_src/session.php';
studioSessionStart();
if (!isAuthenticated()) {
    header('Location: login.php');
    exit;
}
// Override .htaccess CSP — these tools require CDN scripts (React, Babel, JSZip) and Google Fonts
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self' https://unpkg.com");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>漢字 Studio</title>
  <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <script src="https://unpkg.com/jszip@3/dist/jszip.min.js"></script>
</head>
<body>
  <div id="root"></div>
  <script type="text/babel" data-type="module">
    const { useState, useRef, useCallback, useEffect } = React;

    const DEFAULT_WORDS = `名前\n氏名\n名前\n前\n午前\n国\n国際交流\n外国`;

    const FONTS = [
      { label: "Noto Serif JP", value: "'Noto Serif JP', serif", url: "https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&display=swap" },
      { label: "Noto Sans JP", value: "'Noto Sans JP', sans-serif", url: "https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" },
      { label: "Zen Antique", value: "'Zen Antique', serif", url: "https://fonts.googleapis.com/css2?family=Zen+Antique&display=swap" },
      { label: "Shippori Mincho", value: "'Shippori Mincho', serif", url: "https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@400;700&display=swap" },
      { label: "Yuji Syuku", value: "'Yuji Syuku', serif", url: "https://fonts.googleapis.com/css2?family=Yuji+Syuku&display=swap" },
      { label: "Zen Maru Gothic", value: "'Zen Maru Gothic', sans-serif", url: "https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@400;700&display=swap" },
      { label: "Sawarabi Mincho", value: "'Sawarabi Mincho', serif", url: "https://fonts.googleapis.com/css2?family=Sawarabi+Mincho&display=swap" },
      { label: "Sawarabi Gothic", value: "'Sawarabi Gothic', sans-serif", url: "https://fonts.googleapis.com/css2?family=Sawarabi+Gothic&display=swap" },
      { label: "Kosugi Maru", value: "'Kosugi Maru', sans-serif", url: "https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" },
      { label: "Kosugi", value: "'Kosugi', sans-serif", url: "https://fonts.googleapis.com/css2?family=Kosugi&display=swap" },
      { label: "M PLUS 1p", value: "'M PLUS 1p', sans-serif", url: "https://fonts.googleapis.com/css2?family=M+PLUS+1p:wght@400;700&display=swap" },
      { label: "M PLUS Rounded 1c", value: "'M PLUS Rounded 1c', sans-serif", url: "https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;700&display=swap" },
      { label: "Dela Gothic One", value: "'Dela Gothic One', cursive", url: "https://fonts.googleapis.com/css2?family=Dela+Gothic+One&display=swap" },
      { label: "Hachi Maru Pop", value: "'Hachi Maru Pop', cursive", url: "https://fonts.googleapis.com/css2?family=Hachi+Maru+Pop&display=swap" },
      { label: "DotGothic16", value: "'DotGothic16', sans-serif", url: "https://fonts.googleapis.com/css2?family=DotGothic16&display=swap" },
      { label: "Kiwi Maru", value: "'Kiwi Maru', serif", url: "https://fonts.googleapis.com/css2?family=Kiwi+Maru:wght@400;500&display=swap" },
      { label: "Kaisei Decol", value: "'Kaisei Decol', serif", url: "https://fonts.googleapis.com/css2?family=Kaisei+Decol&display=swap" },
      { label: "Kaisei Tokumin", value: "'Kaisei Tokumin', serif", url: "https://fonts.googleapis.com/css2?family=Kaisei+Tokumin:wght@400;700&display=swap" },
      { label: "Kaisei Opti", value: "'Kaisei Opti', serif", url: "https://fonts.googleapis.com/css2?family=Kaisei+Opti&display=swap" },
      { label: "Kaisei HarunoUmi", value: "'Kaisei HarunoUmi', serif", url: "https://fonts.googleapis.com/css2?family=Kaisei+HarunoUmi&display=swap" },
      { label: "Zen Kaku Gothic New", value: "'Zen Kaku Gothic New', sans-serif", url: "https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;700&display=swap" },
      { label: "Zen Old Mincho", value: "'Zen Old Mincho', serif", url: "https://fonts.googleapis.com/css2?family=Zen+Old+Mincho:wght@400;700&display=swap" },
      { label: "Zen Kurenaido", value: "'Zen Kurenaido', sans-serif", url: "https://fonts.googleapis.com/css2?family=Zen+Kurenaido&display=swap" },
      { label: "Zen Antique Soft", value: "'Zen Antique Soft', serif", url: "https://fonts.googleapis.com/css2?family=Zen+Antique+Soft&display=swap" },
      { label: "Yusei Magic", value: "'Yusei Magic', sans-serif", url: "https://fonts.googleapis.com/css2?family=Yusei+Magic&display=swap" },
      { label: "Yomogi", value: "'Yomogi', cursive", url: "https://fonts.googleapis.com/css2?family=Yomogi&display=swap" },
      { label: "Reggae One", value: "'Reggae One', cursive", url: "https://fonts.googleapis.com/css2?family=Reggae+One&display=swap" },
      { label: "RocknRoll One", value: "'RocknRoll One', sans-serif", url: "https://fonts.googleapis.com/css2?family=RocknRoll+One&display=swap" },
      { label: "Stick", value: "'Stick', sans-serif", url: "https://fonts.googleapis.com/css2?family=Stick&display=swap" },
      { label: "Train One", value: "'Train One', cursive", url: "https://fonts.googleapis.com/css2?family=Train+One&display=swap" },
      { label: "Potta One", value: "'Potta One', cursive", url: "https://fonts.googleapis.com/css2?family=Potta+One&display=swap" },
      { label: "New Tegomin", value: "'New Tegomin', serif", url: "https://fonts.googleapis.com/css2?family=New+Tegomin&display=swap" },
      { label: "Murecho", value: "'Murecho', sans-serif", url: "https://fonts.googleapis.com/css2?family=Murecho:wght@400;700&display=swap" },
      { label: "Klee One", value: "'Klee One', cursive", url: "https://fonts.googleapis.com/css2?family=Klee+One:wght@400;600&display=swap" },
      { label: "Rampart One", value: "'Rampart One', cursive", url: "https://fonts.googleapis.com/css2?family=Rampart+One&display=swap" },
      { label: "Mochiy Pop One", value: "'Mochiy Pop One', sans-serif", url: "https://fonts.googleapis.com/css2?family=Mochiy+Pop+One&display=swap" },
      { label: "Mochiy Pop P One", value: "'Mochiy Pop P One', sans-serif", url: "https://fonts.googleapis.com/css2?family=Mochiy+Pop+P+One&display=swap" },
      { label: "Zen Dots", value: "'Zen Dots', cursive", url: "https://fonts.googleapis.com/css2?family=Zen+Dots&display=swap" },
      { label: "Hina Mincho", value: "'Hina Mincho', serif", url: "https://fonts.googleapis.com/css2?family=Hina+Mincho&display=swap" },
      { label: "IBM Plex Sans JP", value: "'IBM Plex Sans JP', sans-serif", url: "https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+JP:wght@400;700&display=swap" },
      { label: "BIZ UDPGothic", value: "'BIZ UDPGothic', sans-serif", url: "https://fonts.googleapis.com/css2?family=BIZ+UDPGothic:wght@400;700&display=swap" },
      { label: "BIZ UDPMincho", value: "'BIZ UDPMincho', serif", url: "https://fonts.googleapis.com/css2?family=BIZ+UDPMincho&display=swap" },
      { label: "BIZ UDGothic", value: "'BIZ UDGothic', sans-serif", url: "https://fonts.googleapis.com/css2?family=BIZ+UDGothic:wght@400;700&display=swap" },
      { label: "BIZ UDMincho", value: "'BIZ UDMincho', serif", url: "https://fonts.googleapis.com/css2?family=BIZ+UDMincho&display=swap" },
      { label: "Zen Kaku Gothic Antique", value: "'Zen Kaku Gothic Antique', sans-serif", url: "https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+Antique:wght@400;700&display=swap" },
      { label: "Yuji Mai", value: "'Yuji Mai', serif", url: "https://fonts.googleapis.com/css2?family=Yuji+Mai&display=swap" },
      { label: "Yuji Boku", value: "'Yuji Boku', serif", url: "https://fonts.googleapis.com/css2?family=Yuji+Boku&display=swap" },
      { label: "Yuji Hentaigana Akari", value: "'Yuji Hentaigana Akari', cursive", url: "https://fonts.googleapis.com/css2?family=Yuji+Hentaigana+Akari&display=swap" },
      { label: "Shizuru", value: "'Shizuru', cursive", url: "https://fonts.googleapis.com/css2?family=Shizuru&display=swap" },
      { label: "Darumadrop One", value: "'Darumadrop One', cursive", url: "https://fonts.googleapis.com/css2?family=Darumadrop+One&display=swap" },
      { label: "Slackside One", value: "'Slackside One', cursive", url: "https://fonts.googleapis.com/css2?family=Slackside+One&display=swap" },
      { label: "Palette Mosaic", value: "'Palette Mosaic', cursive", url: "https://fonts.googleapis.com/css2?family=Palette+Mosaic&display=swap" },
      { label: "Cherry Bomb One", value: "'Cherry Bomb One', cursive", url: "https://fonts.googleapis.com/css2?family=Cherry+Bomb+One&display=swap" },
      { label: "Monomaniac One", value: "'Monomaniac One', sans-serif", url: "https://fonts.googleapis.com/css2?family=Monomaniac+One&display=swap" },
      { label: "Tsukimi Rounded", value: "'Tsukimi Rounded', sans-serif", url: "https://fonts.googleapis.com/css2?family=Tsukimi+Rounded:wght@400;700&display=swap" },
      { label: "Zen Loop", value: "'Zen Loop', cursive", url: "https://fonts.googleapis.com/css2?family=Zen+Loop&display=swap" },
      { label: "Rock 3D", value: "'Rock 3D', cursive", url: "https://fonts.googleapis.com/css2?family=Rock+3D&display=swap" },
      { label: "Noto Sans Mono", value: "'Noto Sans Mono', monospace", url: "https://fonts.googleapis.com/css2?family=Noto+Sans+Mono:wght@400;700&display=swap" },
      { label: "Hannari", value: "'Hannari', serif", url: "https://fonts.googleapis.com/css2?family=Hannari&display=swap" },
      { label: "Kokoro", value: "'Kokoro', serif", url: "https://fonts.googleapis.com/css2?family=Kokoro&display=swap" },
      { label: "Nikukyu", value: "'Nikukyu', cursive", url: "https://fonts.googleapis.com/css2?family=Nikukyu&display=swap" },
      { label: "Aoboshi One", value: "'Aoboshi One', serif", url: "https://fonts.googleapis.com/css2?family=Aoboshi+One&display=swap" },
      { label: "Chokokutai", value: "'Chokokutai', cursive", url: "https://fonts.googleapis.com/css2?family=Chokokutai&display=swap" },
      { label: "Noto Serif Display", value: "'Noto Serif Display', serif", url: "https://fonts.googleapis.com/css2?family=Noto+Serif+Display:wght@400;700&display=swap" },
      { label: "Press Start 2P", value: "'Press Start 2P', cursive", url: "https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" },
      { label: "Pacifico", value: "'Pacifico', cursive", url: "https://fonts.googleapis.com/css2?family=Pacifico&display=swap" },
      { label: "Abril Fatface", value: "'Abril Fatface', cursive", url: "https://fonts.googleapis.com/css2?family=Abril+Fatface&display=swap" },
      { label: "Bebas Neue", value: "'Bebas Neue', cursive", url: "https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" },
      { label: "Lobster", value: "'Lobster', cursive", url: "https://fonts.googleapis.com/css2?family=Lobster&display=swap" },
      { label: "Orbitron", value: "'Orbitron', sans-serif", url: "https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" },
      { label: "Dancing Script", value: "'Dancing Script', cursive", url: "https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" },
      { label: "Permanent Marker", value: "'Permanent Marker', cursive", url: "https://fonts.googleapis.com/css2?family=Permanent+Marker&display=swap" },
      { label: "Cinzel", value: "'Cinzel', serif", url: "https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&display=swap" },
      { label: "Exo 2", value: "'Exo 2', sans-serif", url: "https://fonts.googleapis.com/css2?family=Exo+2:wght@400;700&display=swap" },
      { label: "Josefin Sans", value: "'Josefin Sans', sans-serif", url: "https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;700&display=swap" },
      { label: "Righteous", value: "'Righteous', cursive", url: "https://fonts.googleapis.com/css2?family=Righteous&display=swap" },
      { label: "Comfortaa", value: "'Comfortaa', cursive", url: "https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&display=swap" },
      { label: "Satisfy", value: "'Satisfy', cursive", url: "https://fonts.googleapis.com/css2?family=Satisfy&display=swap" },
      { label: "Passion One", value: "'Passion One', cursive", url: "https://fonts.googleapis.com/css2?family=Passion+One:wght@400;700&display=swap" },
      { label: "Titillium Web", value: "'Titillium Web', sans-serif", url: "https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;700&display=swap" },
      { label: "Fjalla One", value: "'Fjalla One', sans-serif", url: "https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap" },
      { label: "Boogaloo", value: "'Boogaloo', cursive", url: "https://fonts.googleapis.com/css2?family=Boogaloo&display=swap" },
      { label: "Bangers", value: "'Bangers', cursive", url: "https://fonts.googleapis.com/css2?family=Bangers&display=swap" },
      { label: "Poiret One", value: "'Poiret One', cursive", url: "https://fonts.googleapis.com/css2?family=Poiret+One&display=swap" },
      { label: "Sacramento", value: "'Sacramento', cursive", url: "https://fonts.googleapis.com/css2?family=Sacramento&display=swap" },
      { label: "Special Elite", value: "'Special Elite', cursive", url: "https://fonts.googleapis.com/css2?family=Special+Elite&display=swap" },
      { label: "Alfa Slab One", value: "'Alfa Slab One', cursive", url: "https://fonts.googleapis.com/css2?family=Alfa+Slab+One&display=swap" },
      { label: "Rubik", value: "'Rubik', sans-serif", url: "https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" },
      { label: "Antonio", value: "'Antonio', sans-serif", url: "https://fonts.googleapis.com/css2?family=Antonio:wght@400;700&display=swap" },
      { label: "Cormorant Garamond", value: "'Cormorant Garamond', serif", url: "https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;700&display=swap" },
      { label: "Playfair Display", value: "'Playfair Display', serif", url: "https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" },
      { label: "Zilla Slab", value: "'Zilla Slab', serif", url: "https://fonts.googleapis.com/css2?family=Zilla+Slab:wght@400;700&display=swap" },
      { label: "Cinzel Decorative", value: "'Cinzel Decorative', cursive", url: "https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&display=swap" },
      { label: "Great Vibes", value: "'Great Vibes', cursive", url: "https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" },
      { label: "Ubuntu Mono", value: "'Ubuntu Mono', monospace", url: "https://fonts.googleapis.com/css2?family=Ubuntu+Mono:wght@400;700&display=swap" },
      { label: "JetBrains Mono", value: "'JetBrains Mono', monospace", url: "https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" },
      { label: "Space Grotesk", value: "'Space Grotesk', sans-serif", url: "https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700&display=swap" },
      { label: "Fredericka the Great", value: "'Fredericka the Great', cursive", url: "https://fonts.googleapis.com/css2?family=Fredericka+the+Great&display=swap" },
    ];

    const FONTS_PER_PAGE = 12;
    const FONT_TOTAL_PAGES = Math.ceil(FONTS.length / FONTS_PER_PAGE);

    const PRESETS = [
      { label: "Ink on Paper", bg: "#FAF6F0", fg: "#1a1a1a", font: 0 },
      { label: "Chalk Board", bg: "#2d3a2d", fg: "#e8e4d4", font: 3 },
      { label: "Red Seal", bg: "#f5f0e8", fg: "#b91c1c", font: 4 },
      { label: "Neon Night", bg: "#0f0f1a", fg: "#00ffd5", font: 1 },
      { label: "Minimal", bg: "#ffffff", fg: "#111111", font: 5 },
      { label: "Washi", bg: "#e8ddd0", fg: "#3d2b1f", font: 2 },
      { label: "Bamboo Forest", bg: "#1a2e1a", fg: "#a8d5a2", font: 6 },
      { label: "Cherry Blossom", bg: "#fff0f5", fg: "#c2185b", font: 0 },
      { label: "Ocean Depths", bg: "#0a1628", fg: "#4fc3f7", font: 1 },
      { label: "Golden Temple", bg: "#2a1f0e", fg: "#d4a017", font: 3 },
      { label: "Snow Mountain", bg: "#f0f4f8", fg: "#37474f", font: 5 },
      { label: "Autumn Leaves", bg: "#3e1f0d", fg: "#e65100", font: 2 },
      { label: "Twilight", bg: "#1a1033", fg: "#ce93d8", font: 4 },
      { label: "Matcha", bg: "#2d4a2d", fg: "#c8e6c9", font: 6 },
      { label: "Sunrise", bg: "#fff3e0", fg: "#e64a19", font: 0 },
      { label: "Midnight Blue", bg: "#0d1b2a", fg: "#90caf9", font: 1 },
      { label: "Zen Garden", bg: "#f5f5dc", fg: "#4a4a2a", font: 3 },
      { label: "Volcanic", bg: "#1a0a0a", fg: "#ff5722", font: 2 },
      { label: "Arctic", bg: "#e8f4fd", fg: "#0277bd", font: 5 },
      { label: "Jade", bg: "#0d2818", fg: "#00e676", font: 4 },
      { label: "Rose Gold", bg: "#2a1a1a", fg: "#e8a87c", font: 0 },
      { label: "Lavender", bg: "#f3e5f5", fg: "#6a1b9a", font: 3 },
      { label: "Charcoal", bg: "#1c1c1c", fg: "#9e9e9e", font: 1 },
      { label: "Peach", bg: "#fff8e1", fg: "#bf360c", font: 2 },
      { label: "Deep Forest", bg: "#0a1a0a", fg: "#66bb6a", font: 6 },
      { label: "Royal Purple", bg: "#1a0a2e", fg: "#b388ff", font: 4 },
      { label: "Copper", bg: "#1a120a", fg: "#d4883c", font: 0 },
      { label: "Frost", bg: "#e0f7fa", fg: "#00695c", font: 5 },
      { label: "Ember", bg: "#280a0a", fg: "#ff8a65", font: 2 },
      { label: "Steel", bg: "#263238", fg: "#b0bec5", font: 1 },
      { label: "Sakura Dark", bg: "#1a0a14", fg: "#f48fb1", font: 3 },
      { label: "Moss", bg: "#1b2d1b", fg: "#aed581", font: 6 },
      { label: "Ivory", bg: "#fffff0", fg: "#3e2723", font: 0 },
      { label: "Cobalt", bg: "#0a0a28", fg: "#448aff", font: 1 },
      { label: "Sand", bg: "#f5e6d3", fg: "#5d4037", font: 2 },
      { label: "Plum", bg: "#2a0a2a", fg: "#ea80fc", font: 4 },
      { label: "Teal", bg: "#0a2828", fg: "#26c6da", font: 5 },
      { label: "Cream", bg: "#fefcf0", fg: "#4e342e", font: 3 },
      { label: "Graphite", bg: "#212121", fg: "#757575", font: 1 },
      { label: "Honey", bg: "#2a1f0a", fg: "#ffc107", font: 0 },
      { label: "Wine", bg: "#1a0a0f", fg: "#c62828", font: 2 },
      { label: "Pine", bg: "#0a1a14", fg: "#80cbc4", font: 6 },
      { label: "Sunset", bg: "#1a0f0a", fg: "#ff7043", font: 4 },
      { label: "Moonlight", bg: "#0f0f1e", fg: "#cfd8dc", font: 3 },
      { label: "Coral", bg: "#fff0ee", fg: "#d84315", font: 0 },
      { label: "Slate", bg: "#2d3436", fg: "#dfe6e9", font: 1 },
      { label: "Olive", bg: "#1a1a0a", fg: "#c0ca33", font: 5 },
      { label: "Amethyst", bg: "#1a0a28", fg: "#ab47bc", font: 4 },
      { label: "Parchment", bg: "#f0e6d2", fg: "#2e1a0a", font: 2 },
      { label: "Electric", bg: "#0a0a1e", fg: "#00e5ff", font: 1 },
      { label: "Obsidian", bg: "#0a0a0a", fg: "#e0e0e0", font: 5 },
      { label: "Crimson Lake", bg: "#1a0005", fg: "#ff4d6d", font: 2 },
      { label: "Aurora", bg: "#001a14", fg: "#00ff9d", font: 4 },
      { label: "Dusk", bg: "#1e1428", fg: "#ffd6e0", font: 3 },
      { label: "Glacier", bg: "#e8f0f8", fg: "#1565c0", font: 1 },
      { label: "Ember Glow", bg: "#200a00", fg: "#ff6d00", font: 0 },
      { label: "Seafoam", bg: "#e0f2f1", fg: "#00695c", font: 5 },
      { label: "Midnight Rose", bg: "#0d0010", fg: "#ff80ab", font: 4 },
      { label: "Desert Sand", bg: "#f5e9d0", fg: "#6d3b00", font: 2 },
      { label: "Abyssal", bg: "#00000f", fg: "#5c6bc0", font: 1 },
      { label: "Meadow", bg: "#e8f5e9", fg: "#2e7d32", font: 6 },
      { label: "Solstice", bg: "#fff8e1", fg: "#f57f17", font: 0 },
      { label: "Nebula", bg: "#0d001a", fg: "#ea80fc", font: 4 },
      { label: "Old Paper", bg: "#f0e0c0", fg: "#3e2000", font: 3 },
      { label: "Aqua", bg: "#e0f7fa", fg: "#006064", font: 5 },
      { label: "Inkwell", bg: "#080808", fg: "#c0c0c0", font: 1 },
      { label: "Blossom", bg: "#fce4ec", fg: "#880e4f", font: 0 },
      { label: "Thunder", bg: "#1a1a00", fg: "#ffd600", font: 2 },
      { label: "Cobalt Frost", bg: "#e8eaf6", fg: "#1a237e", font: 5 },
      { label: "Velvet", bg: "#12001e", fg: "#d500f9", font: 4 },
      { label: "Savanna", bg: "#2a1e08", fg: "#e6b830", font: 0 },
      { label: "Mist", bg: "#eceff1", fg: "#455a64", font: 3 },
      { label: "Sulfur", bg: "#1a1800", fg: "#f9a825", font: 1 },
      { label: "Fossil", bg: "#e8e4dc", fg: "#4e342e", font: 2 },
      { label: "Indigo Night", bg: "#090028", fg: "#7c4dff", font: 4 },
      { label: "Polar", bg: "#f0f8ff", fg: "#0d47a1", font: 5 },
      { label: "Lava", bg: "#120000", fg: "#ff1744", font: 2 },
      { label: "Spring", bg: "#f1f8e9", fg: "#558b2f", font: 6 },
      { label: "Twilight Gold", bg: "#0a0800", fg: "#ffd740", font: 3 },
      { label: "Carbon", bg: "#121212", fg: "#a0a0a0", font: 1 },
      { label: "Papaya", bg: "#fff3e0", fg: "#e65100", font: 0 },
      { label: "Gunmetal", bg: "#1c2028", fg: "#90a4ae", font: 5 },
      { label: "Tangerine", bg: "#1a0800", fg: "#ff9100", font: 2 },
      { label: "Silver Screen", bg: "#f5f5f5", fg: "#212121", font: 1 },
      { label: "Deep Sapphire", bg: "#001030", fg: "#82b1ff", font: 4 },
      { label: "Petal", bg: "#fdf0f8", fg: "#ad1457", font: 0 },
      { label: "Rainforest", bg: "#001a0a", fg: "#69f0ae", font: 6 },
      { label: "Ash", bg: "#f0ede8", fg: "#616161", font: 5 },
      { label: "Midnight Teal", bg: "#00141a", fg: "#00bcd4", font: 1 },
      { label: "Bronze", bg: "#1a0e00", fg: "#cd7f32", font: 3 },
      { label: "Lavender Mist", bg: "#f3e8ff", fg: "#7c3aed", font: 4 },
      { label: "Midnight Green", bg: "#001a14", fg: "#40c4aa", font: 6 },
      { label: "Candle", bg: "#fff9f0", fg: "#b05500", font: 0 },
      { label: "Space", bg: "#000814", fg: "#ade8f4", font: 1 },
      { label: "Garnet", bg: "#0e0006", fg: "#e53935", font: 2 },
      { label: "Birch", bg: "#f5f0e8", fg: "#4a3728", font: 3 },
      { label: "Viridian", bg: "#001a12", fg: "#00e5ac", font: 5 },
      { label: "Amber Night", bg: "#100800", fg: "#ffca28", font: 0 },
      { label: "Pearl", bg: "#fafafa", fg: "#37474f", font: 3 },
      { label: "Shadow", bg: "#050505", fg: "#8d8d8d", font: 1 },
    ];

    const GRADIENT_PRESETS = [
      { label: "Sunrise Bloom", bg: "linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%)", fg: "#1a0a0a", font: 0 },
      { label: "Ocean Breeze", bg: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", fg: "#ffffff", font: 1 },
      { label: "Mint Dream", bg: "linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)", fg: "#1a2a1a", font: 5 },
      { label: "Purple Haze", bg: "linear-gradient(135deg, #7b2ff7 0%, #c471f5 100%)", fg: "#ffffff", font: 4 },
      { label: "Golden Hour", bg: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)", fg: "#ffffff", font: 0 },
      { label: "Northern Lights", bg: "linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)", fg: "#0a1a14", font: 6 },
      { label: "Deep Sea", bg: "linear-gradient(135deg, #0c3483 0%, #a2b6df 100%)", fg: "#ffffff", font: 1 },
      { label: "Peach Sunset", bg: "linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)", fg: "#3e1a0a", font: 2 },
      { label: "Cosmic Dust", bg: "linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%)", fg: "#e0e0ff", font: 4 },
      { label: "Cherry Cola", bg: "linear-gradient(135deg, #eb3349 0%, #f45c43 100%)", fg: "#ffffff", font: 0 },
      { label: "Forest Canopy", bg: "linear-gradient(135deg, #134e5e 0%, #71b280 100%)", fg: "#ffffff", font: 6 },
      { label: "Lavender Fields", bg: "linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%)", fg: "#1a0a2e", font: 3 },
      { label: "Ember Fade", bg: "linear-gradient(135deg, #f83600 0%, #f9d423 100%)", fg: "#ffffff", font: 2 },
      { label: "Arctic Aurora", bg: "linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%)", fg: "#ffffff", font: 5 },
      { label: "Rose Quartz", bg: "linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%)", fg: "#2a0a2a", font: 0 },
      { label: "Midnight City", bg: "linear-gradient(135deg, #232526 0%, #414345 100%)", fg: "#e0e0e0", font: 1 },
      { label: "Mango Tango", bg: "linear-gradient(135deg, #ffe259 0%, #ffa751 100%)", fg: "#2a1a00", font: 2 },
      { label: "Sapphire Sky", bg: "linear-gradient(135deg, #0052d4 0%, #4364f7 50%, #6fb1fc 100%)", fg: "#ffffff", font: 1 },
      { label: "Cotton Candy", bg: "linear-gradient(135deg, #f3e7e9 0%, #e3eeff 100%)", fg: "#4a2a4a", font: 3 },
      { label: "Volcanic Ash", bg: "linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)", fg: "#ff8a65", font: 2 },
      { label: "Spring Meadow", bg: "linear-gradient(135deg, #c6ea8d 0%, #fe90af 100%)", fg: "#1a2a0a", font: 6 },
      { label: "Blueberry", bg: "linear-gradient(135deg, #4568dc 0%, #b06ab3 100%)", fg: "#ffffff", font: 4 },
      { label: "Sand Dune", bg: "linear-gradient(135deg, #d4a574 0%, #e8cba8 100%)", fg: "#2a1a0a", font: 0 },
      { label: "Neon Pulse", bg: "linear-gradient(135deg, #00f260 0%, #0575e6 100%)", fg: "#ffffff", font: 1 },
      { label: "Dusty Rose", bg: "linear-gradient(135deg, #d299c2 0%, #fef9d7 100%)", fg: "#3e1a2a", font: 3 },
      { label: "Storm Cloud", bg: "linear-gradient(135deg, #373b44 0%, #4286f4 100%)", fg: "#ffffff", font: 5 },
      { label: "Honey Drip", bg: "linear-gradient(135deg, #f7971e 0%, #ffd200 100%)", fg: "#2a1a00", font: 0 },
      { label: "Twilight Zone", bg: "linear-gradient(135deg, #141e30 0%, #243b55 100%)", fg: "#cfd8dc", font: 1 },
      { label: "Watermelon", bg: "linear-gradient(135deg, #ff6b6b 0%, #556270 100%)", fg: "#ffffff", font: 2 },
      { label: "Sage Mist", bg: "linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%)", fg: "#ffffff", font: 5 },
      { label: "Tangerine Dream", bg: "linear-gradient(135deg, #ff5f6d 0%, #ffc371 100%)", fg: "#ffffff", font: 0 },
      { label: "Galactic", bg: "linear-gradient(135deg, #654ea3 0%, #eaafc8 100%)", fg: "#ffffff", font: 4 },
      { label: "Bamboo Shade", bg: "linear-gradient(135deg, #2d5016 0%, #89a83a 100%)", fg: "#ffffff", font: 6 },
      { label: "Coral Reef", bg: "linear-gradient(135deg, #ff9966 0%, #ff5e62 100%)", fg: "#ffffff", font: 0 },
      { label: "Winter Frost", bg: "linear-gradient(135deg, #e6dada 0%, #274046 100%)", fg: "#ffffff", font: 5 },
      { label: "Plum Wine", bg: "linear-gradient(135deg, #360033 0%, #0b8793 100%)", fg: "#ffffff", font: 4 },
      { label: "Lemon Zest", bg: "linear-gradient(135deg, #f7ff00 0%, #db36a4 100%)", fg: "#1a1a00", font: 2 },
      { label: "Shadow Play", bg: "linear-gradient(135deg, #000000 0%, #434343 100%)", fg: "#e0e0e0", font: 1 },
      { label: "Tropical Punch", bg: "linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%)", fg: "#ffffff", font: 0 },
      { label: "Moonstone", bg: "linear-gradient(135deg, #c9d6ff 0%, #e2e2e2 100%)", fg: "#1a1a3e", font: 3 },
      { label: "Jade Temple", bg: "linear-gradient(135deg, #0f9b0f 0%, #000000 100%)", fg: "#80ff80", font: 6 },
      { label: "Flamingo", bg: "linear-gradient(135deg, #f54ea2 0%, #ff7676 100%)", fg: "#ffffff", font: 0 },
      { label: "Glacier Blue", bg: "linear-gradient(135deg, #74ebd5 0%, #acb6e5 100%)", fg: "#0a1a28", font: 5 },
      { label: "Obsidian Flame", bg: "linear-gradient(135deg, #000000 0%, #e74c3c 100%)", fg: "#ffffff", font: 2 },
      { label: "Pastel Sky", bg: "linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%)", fg: "#1a2a3e", font: 3 },
      { label: "Ruby Glow", bg: "linear-gradient(135deg, #870000 0%, #190a05 100%)", fg: "#ff6b6b", font: 4 },
      { label: "Seafoam Wave", bg: "linear-gradient(135deg, #11998e 0%, #38ef7d 100%)", fg: "#ffffff", font: 6 },
      { label: "Blush", bg: "linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%)", fg: "#3e1a2a", font: 0 },
      { label: "Iron Gray", bg: "linear-gradient(135deg, #485563 0%, #29323c 100%)", fg: "#e0e0e0", font: 1 },
      { label: "Citrus Burst", bg: "linear-gradient(135deg, #f9d423 0%, #ff4e50 100%)", fg: "#1a0a00", font: 2 },
      { label: "Amethyst Cave", bg: "linear-gradient(135deg, #9d50bb 0%, #6e48aa 100%)", fg: "#ffffff", font: 4 },
      { label: "Palm Leaf", bg: "linear-gradient(135deg, #0ba360 0%, #3cba92 100%)", fg: "#ffffff", font: 6 },
      { label: "Copper Rust", bg: "linear-gradient(135deg, #b79891 0%, #94716b 100%)", fg: "#ffffff", font: 0 },
      { label: "Electric Violet", bg: "linear-gradient(135deg, #4776e6 0%, #8e54e9 100%)", fg: "#ffffff", font: 4 },
      { label: "Warm Ember", bg: "linear-gradient(135deg, #c94b4b 0%, #4b134f 100%)", fg: "#ffd6d6", font: 2 },
      { label: "Fern Glow", bg: "linear-gradient(135deg, #56ab2f 0%, #a8e063 100%)", fg: "#0a1a00", font: 6 },
      { label: "Bluebell", bg: "linear-gradient(135deg, #396afc 0%, #2948ff 100%)", fg: "#ffffff", font: 1 },
      { label: "Sandstorm", bg: "linear-gradient(135deg, #c2b280 0%, #d4a76a 100%)", fg: "#2a1a00", font: 3 },
      { label: "Orchid Dream", bg: "linear-gradient(135deg, #da22ff 0%, #9733ee 100%)", fg: "#ffffff", font: 4 },
      { label: "Ash Cloud", bg: "linear-gradient(135deg, #606c88 0%, #3f4c6b 100%)", fg: "#e0e0e0", font: 1 },
      { label: "Lava Flow", bg: "linear-gradient(135deg, #f12711 0%, #f5af19 100%)", fg: "#ffffff", font: 2 },
      { label: "Tidal Wave", bg: "linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)", fg: "#ffffff", font: 5 },
      { label: "Velvet Night", bg: "linear-gradient(135deg, #1f1c2c 0%, #928dab 100%)", fg: "#ffffff", font: 3 },
      { label: "Apricot Glow", bg: "linear-gradient(135deg, #fceabb 0%, #f8b500 100%)", fg: "#2a1a00", font: 0 },
      { label: "Mystic Forest", bg: "linear-gradient(135deg, #0d3b0d 0%, #3e8e41 100%)", fg: "#c8ffc8", font: 6 },
      { label: "Pink Lemonade", bg: "linear-gradient(135deg, #ffc0cb 0%, #fffacd 100%)", fg: "#3e1a2a", font: 0 },
      { label: "Thunder Strike", bg: "linear-gradient(135deg, #1a1a2e 0%, #e94560 100%)", fg: "#ffffff", font: 2 },
      { label: "Silver Lining", bg: "linear-gradient(135deg, #bdc3c7 0%, #ecf0f1 100%)", fg: "#2c3e50", font: 5 },
      { label: "Frozen Berry", bg: "linear-gradient(135deg, #6a3093 0%, #a044ff 100%)", fg: "#ffffff", font: 4 },
      { label: "Burnt Sienna", bg: "linear-gradient(135deg, #7b4397 0%, #dc2430 100%)", fg: "#ffffff", font: 2 },
      { label: "Pacific Rim", bg: "linear-gradient(135deg, #1cb5e0 0%, #000046 100%)", fg: "#ffffff", font: 1 },
      { label: "Champagne", bg: "linear-gradient(135deg, #f7e7ce 0%, #d4a76a 100%)", fg: "#2a1a0a", font: 3 },
      { label: "Dark Matter", bg: "linear-gradient(135deg, #000000 0%, #0f0c29 50%, #302b63 100%)", fg: "#b388ff", font: 4 },
      { label: "Lime Soda", bg: "linear-gradient(135deg, #a8ff78 0%, #78ffd6 100%)", fg: "#0a2a0a", font: 6 },
      { label: "Crimson Tide", bg: "linear-gradient(135deg, #642b73 0%, #c6426e 100%)", fg: "#ffffff", font: 0 },
      { label: "Sky at Dusk", bg: "linear-gradient(135deg, #2980b9 0%, #6dd5fa 50%, #ffffff 100%)", fg: "#0a1a28", font: 5 },
      { label: "Caramel Swirl", bg: "linear-gradient(135deg, #c0392b 0%, #f39c12 100%)", fg: "#ffffff", font: 0 },
      { label: "Misty Mountain", bg: "linear-gradient(135deg, #606c88 0%, #3f4c6b 100%)", fg: "#c0c8d8", font: 3 },
      { label: "Electric Lime", bg: "linear-gradient(135deg, #b4ec51 0%, #429321 100%)", fg: "#ffffff", font: 6 },
      { label: "Starlight", bg: "linear-gradient(135deg, #0f0c29 0%, #24243e 100%)", fg: "#ffd700", font: 3 },
      { label: "Bubblegum", bg: "linear-gradient(135deg, #ff61d2 0%, #fe9090 100%)", fg: "#ffffff", font: 0 },
      { label: "Deep Ocean", bg: "linear-gradient(135deg, #000428 0%, #004e92 100%)", fg: "#ffffff", font: 1 },
      { label: "Autumn Blaze", bg: "linear-gradient(135deg, #d38312 0%, #a83279 100%)", fg: "#ffffff", font: 2 },
      { label: "Zen Stone", bg: "linear-gradient(135deg, #757f9a 0%, #d7dde8 100%)", fg: "#1a1a2e", font: 5 },
      { label: "Solar Flare", bg: "linear-gradient(135deg, #f37335 0%, #fdc830 100%)", fg: "#1a0a00", font: 0 },
      { label: "Wisteria", bg: "linear-gradient(135deg, #c471f5 0%, #fa71cd 100%)", fg: "#ffffff", font: 4 },
      { label: "Frozen Lake", bg: "linear-gradient(135deg, #c2e9fb 0%, #a1c4fd 100%)", fg: "#0a1a3e", font: 5 },
      { label: "Redwood", bg: "linear-gradient(135deg, #3e0000 0%, #7f1d1d 100%)", fg: "#ffb3b3", font: 2 },
      { label: "Pistachio", bg: "linear-gradient(135deg, #93f9b9 0%, #1d976c 100%)", fg: "#0a2a14", font: 6 },
      { label: "Midnight Jazz", bg: "linear-gradient(135deg, #1a002e 0%, #4a148c 100%)", fg: "#ce93d8", font: 4 },
      { label: "Sunrise Peak", bg: "linear-gradient(135deg, #ff512f 0%, #f09819 100%)", fg: "#ffffff", font: 0 },
      { label: "Steel Blue", bg: "linear-gradient(135deg, #2c3e50 0%, #3498db 100%)", fg: "#ffffff", font: 1 },
      { label: "Blossom Rain", bg: "linear-gradient(135deg, #fce4ec 0%, #f8bbd0 50%, #f48fb1 100%)", fg: "#880e4f", font: 0 },
      { label: "Charcoal Gold", bg: "linear-gradient(135deg, #1a1a1a 0%, #3d3d3d 100%)", fg: "#ffd700", font: 3 },
      { label: "Aqua Marine", bg: "linear-gradient(135deg, #1a2980 0%, #26d0ce 100%)", fg: "#ffffff", font: 5 },
      { label: "Sakura Dusk", bg: "linear-gradient(135deg, #2a0a14 0%, #c2185b 100%)", fg: "#ffc0cb", font: 3 },
      { label: "Emerald Isle", bg: "linear-gradient(135deg, #005c1a 0%, #00c853 100%)", fg: "#ffffff", font: 6 },
      { label: "Cosmic Latte", bg: "linear-gradient(135deg, #fff8e7 0%, #d4c5a9 100%)", fg: "#3e2a0a", font: 0 },
    ];

    function loadFont(fontObj) {
      return new Promise((resolve) => {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = fontObj.url;
        link.onload = () => { document.fonts.ready.then(resolve); };
        link.onerror = resolve;
        document.head.appendChild(link);
      });
    }

    function getWmCoords(pos, cw, ch, ew, eh, margin = 10) {
      const m = margin;
      switch (pos) {
        case "Center": return [(cw - ew) / 2, (ch - eh) / 2];
        case "Top Left": return [m, m];
        case "Top Center": return [(cw - ew) / 2, m];
        case "Top Right": return [cw - ew - m, m];
        case "Bottom Left": return [m, ch - eh - m];
        case "Bottom Center": return [(cw - ew) / 2, ch - eh - m];
        case "Bottom Right": return [cw - ew - m, ch - eh - m];
        default: return [cw - ew - m, ch - eh - m];
      }
    }

    function createCanvasGradient(ctx, cssGradient, w, h) {
      const m = cssGradient.match(/linear-gradient\((\d+)deg,\s*(.+)\)/);
      if (!m) return cssGradient;
      const angle = parseFloat(m[1]) * Math.PI / 180;
      const cx = w / 2, cy = h / 2;
      const len = Math.abs(w * Math.sin(angle)) + Math.abs(h * Math.cos(angle));
      const x1 = cx - (len / 2) * Math.sin(angle), y1 = cy - (len / 2) * Math.cos(angle);
      const x2 = cx + (len / 2) * Math.sin(angle), y2 = cy + (len / 2) * Math.cos(angle);
      const grad = ctx.createLinearGradient(x1, y1, x2, y2);
      const stops = m[2].match(/(#[0-9a-fA-F]{3,8}|rgba?\([^)]+\))\s+([\d.]+%)/g);
      if (stops) stops.forEach(s => {
        const parts = s.match(/(#[0-9a-fA-F]{3,8}|rgba?\([^)]+\))\s+([\d.]+%)/);
        if (parts) grad.addColorStop(parseFloat(parts[2]) / 100, parts[1]);
      });
      return grad;
    }

    function renderToCanvas(text, { fontFamily, fontSize, bgColor, fgColor, padding, bold, roundness, scale, watermark, aspectRatio }) {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");
      const weight = bold ? "bold " : "";
      const scaledFontSize = fontSize * scale;
      const scaledPadding = padding * scale;
      const fontStr = `${weight}${scaledFontSize}px ${fontFamily}`;
      ctx.font = fontStr;
      const metrics = ctx.measureText(text);
      const textWidth = metrics.width;
      const textHeight = scaledFontSize * 1.25;
      const naturalW = Math.ceil(textWidth + scaledPadding * 2);
      const naturalH = Math.ceil(textHeight + scaledPadding * 2);
      if (aspectRatio) {
        const targetRatio = aspectRatio.w / aspectRatio.h;
        const naturalRatio = naturalW / naturalH;
        if (targetRatio > naturalRatio) {
          canvas.width = Math.ceil(naturalH * targetRatio);
          canvas.height = naturalH;
        } else {
          canvas.width = naturalW;
          canvas.height = Math.ceil(naturalW / targetRatio);
        }
      } else {
        canvas.width = naturalW;
        canvas.height = naturalH;
      }
      const radius = Math.min(canvas.width, canvas.height) * (roundness / 100);
      ctx.beginPath();
      ctx.roundRect(0, 0, canvas.width, canvas.height, radius);
      ctx.clip();
      ctx.fillStyle = bgColor.includes("gradient") ? createCanvasGradient(ctx, bgColor, canvas.width, canvas.height) : bgColor;
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      // Draw watermark image layer (between background and text)
      if (watermark && watermark.image) {
        ctx.save();
        ctx.globalAlpha = watermark.imageOpacity;
        const ww = canvas.width * watermark.imageScale;
        const wh = (watermark.image.height / watermark.image.width) * ww;
        const [ix, iy] = getWmCoords(watermark.imagePos, canvas.width, canvas.height, ww, wh);
        ctx.drawImage(watermark.image, ix, iy, ww, wh);
        ctx.restore();
      }
      ctx.font = fontStr;
      ctx.fillStyle = fgColor;
      ctx.textBaseline = "middle";
      ctx.textAlign = "center";
      ctx.fillText(text, canvas.width / 2, canvas.height / 2);
      return { canvas, naturalW, naturalH };
    }

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
        accent: "#c9a96e",
        accentGradient: "linear-gradient(135deg, #c9a96e 0%, #a67c52 100%)",
        accentBtnText: "#0d0d0d",
        scrollThumb: "#333",
        cardBg: "rgba(255,255,255,0.02)",
        cardBorder: "rgba(255,255,255,0.06)",
        cardHoverBorder: "rgba(255,255,255,0.15)",
        overlayBg: "rgba(0,0,0,0.6)",
        presetBg: "rgba(255,255,255,0.04)",
        presetBorder: "rgba(255,255,255,0.08)",
        presetHoverBg: "rgba(255,255,255,0.08)",
        presetActiveBg: "rgba(255,255,255,0.12)",
        presetActiveBorder: "rgba(255,255,255,0.2)",
        fontOptionBg: "rgba(255,255,255,0.02)",
        fontOptionBorder: "rgba(255,255,255,0.06)",
        fontOptionHoverBg: "rgba(255,255,255,0.06)",
        fontOptionActiveBg: "rgba(201,169,110,0.12)",
        fontOptionActiveBorder: "rgba(201,169,110,0.4)",
        toggleTrack: "rgba(255,255,255,0.1)",
        toggleKnob: "#e0dcd4",
        badgeBg: "rgba(201,169,110,0.15)",
        secondaryBtnBg: "rgba(255,255,255,0.06)",
        secondaryBtnBorder: "rgba(201,169,110,0.3)",
        swapBtnBorder: "rgba(255,255,255,0.1)",
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
        overlayBg: "rgba(0,0,0,0.4)",
        presetBg: "rgba(0,0,0,0.03)",
        presetBorder: "rgba(0,0,0,0.1)",
        presetHoverBg: "rgba(0,0,0,0.06)",
        presetActiveBg: "rgba(0,0,0,0.1)",
        presetActiveBorder: "rgba(0,0,0,0.25)",
        fontOptionBg: "rgba(0,0,0,0.02)",
        fontOptionBorder: "rgba(0,0,0,0.08)",
        fontOptionHoverBg: "rgba(0,0,0,0.05)",
        fontOptionActiveBg: "rgba(139,105,20,0.12)",
        fontOptionActiveBorder: "rgba(139,105,20,0.4)",
        toggleTrack: "rgba(0,0,0,0.15)",
        toggleKnob: "#8b6914",
        badgeBg: "rgba(139,105,20,0.12)",
        secondaryBtnBg: "rgba(0,0,0,0.04)",
        secondaryBtnBorder: "rgba(139,105,20,0.3)",
        swapBtnBorder: "rgba(0,0,0,0.15)",
      },
    };

    const CACHE_KEY = "kanji-studio-prefs";

    function loadCache() {
      try {
        const raw = localStorage.getItem(CACHE_KEY);
        return raw ? JSON.parse(raw) : {};
      } catch { return {}; }
    }

    function saveCache(prefs) {
      try { localStorage.setItem(CACHE_KEY, JSON.stringify(prefs)); } catch {}
    }

    function KanjiGenerator() {
      const cached = React.useMemo(() => loadCache(), []);
      const c = (key, fallback) => cached[key] !== undefined ? cached[key] : fallback;

      const [text, setText] = useState(c("text", DEFAULT_WORDS));
      const [fontSize, setFontSize] = useState(c("fontSize", 200));
      const [padding, setPadding] = useState(c("padding", 40));
      const [bgColor, setBgColor] = useState(c("bgColor", "#FAF6F0"));
      const [fgColor, setFgColor] = useState(c("fgColor", "#1a1a1a"));
      const [fontIndex, setFontIndex] = useState(c("fontIndex", 0));
      const [bold, setBold] = useState(c("bold", false));
      const [roundness, setRoundness] = useState(c("roundness", 12));
      const [scale, setScale] = useState(c("scale", 5));
      const [fontsLoaded, setFontsLoaded] = useState({});
      const [previews, setPreviews] = useState([]);
      const [generating, setGenerating] = useState(false);
      const [selectedPreset, setSelectedPreset] = useState(c("selectedPreset", 0));
      const [presetType, setPresetType] = useState(c("presetType", "solid"));
      const [theme, setTheme] = useState(c("theme", "dark"));
      const [fontPage, setFontPage] = useState(0);
      const [wmText, setWmText] = useState(c("wmText", "Prime Benchmark Private Limited"));
      const [wmTextPos, setWmTextPos] = useState(c("wmTextPos", "Bottom Center"));
      const [wmTextSize, setWmTextSize] = useState(c("wmTextSize", 14));
      const [wmTextFont, setWmTextFont] = useState(c("wmTextFont", "sans-serif"));
      const [wmTextColor, setWmTextColor] = useState(c("wmTextColor", "#ffffff"));
      const [wmTextOpacity, setWmTextOpacity] = useState(c("wmTextOpacity", 0.5));
      const [wmImage, setWmImage] = useState(c("wmImage", { src: "https://primebenchmark.com.np/logo.png", name: "primebenchmark.com.np/logo.png" }));
      const [wmImageUrl, setWmImageUrl] = useState(c("wmImageUrl", "https://primebenchmark.com.np/logo.png"));
      const [wmImagePos, setWmImagePos] = useState(c("wmImagePos", "Center"));
      const [wmImageOpacity, setWmImageOpacity] = useState(c("wmImageOpacity", 0.25));
      const [wmImageScale, setWmImageScale] = useState(c("wmImageScale", 0.2));
      const [aspectRatioEnabled, setAspectRatioEnabled] = useState(c("aspectRatioEnabled", false));
      const [aspectRatioW, setAspectRatioW] = useState(c("aspectRatioW", 1));
      const [aspectRatioH, setAspectRatioH] = useState(c("aspectRatioH", 1));
      const [serialInFilename, setSerialInFilename] = useState(c("serialInFilename", false));

      // Persist preferences to localStorage whenever they change
      useEffect(() => {
        saveCache({
          text, fontSize, padding, bgColor, fgColor, fontIndex, bold, roundness, scale,
          selectedPreset, presetType, theme, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity,
          wmImage, wmImageUrl, wmImagePos, wmImageOpacity, wmImageScale,
          aspectRatioEnabled, aspectRatioW, aspectRatioH, serialInFilename,
        });
      }, [text, fontSize, padding, bgColor, fgColor, fontIndex, bold, roundness, scale,
          selectedPreset, presetType, theme, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity,
          wmImage, wmImageUrl, wmImagePos, wmImageOpacity, wmImageScale,
          aspectRatioEnabled, aspectRatioW, aspectRatioH, serialInFilename]);

      const [showClearModal, setShowClearModal] = useState(false);
      const [clearConfirmText, setClearConfirmText] = useState("");
      const clearCache = () => {
        try { localStorage.removeItem(CACHE_KEY); } catch {}
        window.location.reload();
      };
      const [displayUrls, setDisplayUrls] = useState({});
      const previewRef = useRef(null);
      const fontLoadPromises = useRef({});
      const t = THEMES[theme];

      useEffect(() => {
        // Load all fonts and store promises by index
        FONTS.forEach((f, i) => {
          fontLoadPromises.current[i] = loadFont(f).then(() => setFontsLoaded((p) => ({ ...p, [i]: true })));
        });
      }, []);

      const lines = text.split("\n").map((l) => l.trim()).filter(Boolean);

      const generatePreviews = useCallback(async () => {
        setGenerating(true);
        const fontFamily = FONTS[fontIndex].value;
        const fontLabel = FONTS[fontIndex].label;
        const weight = bold ? "bold" : "normal";
        // Wait for the font stylesheet to load first, then ensure the font face is loaded
        await (fontLoadPromises.current[fontIndex] || Promise.resolve());
        await document.fonts.load(`${weight} ${fontSize * scale}px '${fontLabel}'`);
        // Pre-load watermark image element if configured
        let wmImageEl = null;
        if (wmImage && wmImage.src) {
          wmImageEl = await new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = wmImage.src;
          });
        }
        const arParam = aspectRatioEnabled ? { w: aspectRatioW, h: aspectRatioH } : null;
        const results = lines.map((line, i) => {
          const { canvas, naturalW, naturalH } = renderToCanvas(line, {
            fontFamily,
            fontSize,
            bgColor,
            fgColor,
            padding,
            bold,
            roundness,
            scale,
            watermark: wmImageEl ? { image: wmImageEl, imageOpacity: wmImageOpacity, imageScale: wmImageScale, imagePos: wmImagePos } : null,
            aspectRatio: arParam,
          });
          return { text: line, dataUrl: canvas.toDataURL("image/png"), index: i + 1, naturalW, naturalH };
        });
        setPreviews(results);
        setDisplayUrls({});
        setGenerating(false);
      }, [lines, fontIndex, fontSize, bgColor, fgColor, padding, bold, roundness, scale, wmImage, wmImageOpacity, wmImageScale, wmImagePos, aspectRatioEnabled, aspectRatioW, aspectRatioH]);

      useEffect(() => {
        if (previews.length === 0) { setDisplayUrls({}); return; }
        if (!hasWatermark) {
          const map = {};
          previews.forEach((p, i) => { map[i] = p.dataUrl; });
          setDisplayUrls(map);
          return;
        }
        // Clear stale URLs immediately so new item.dataUrl is shown while watermark is applied
        setDisplayUrls({});
        previews.forEach((item, i) => {
          applyWatermark(item.dataUrl, item.naturalW, item.naturalH).then(url => {
            setDisplayUrls(prev => ({ ...prev, [i]: url }));
          });
        });
      }, [previews, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity]);

      const WM_POSITIONS = ["Center", "Top Left", "Top Center", "Top Right", "Bottom Left", "Bottom Center", "Bottom Right"];
      const WM_FONTS = [
        "sans-serif", "serif", "monospace", "cursive", "fantasy",
        "Arial", "Arial Black", "Arial Narrow", "Arial Rounded MT Bold",
        "Georgia", "Times New Roman", "Palatino Linotype", "Book Antiqua", "Garamond",
        "Courier New", "Lucida Console", "Lucida Sans Typewriter", "Courier",
        "Verdana", "Tahoma", "Trebuchet MS", "Geneva",
        "Impact", "Haettenschweiler", "Franklin Gothic Medium",
        "Gill Sans", "Gill Sans MT", "Calibri", "Candara", "Constantia", "Corbel",
        "Century Gothic", "Futura", "Optima", "Myriad Pro",
        "Helvetica", "Helvetica Neue",
        "Segoe UI", "Segoe Print", "Segoe Script",
        "Comic Sans MS", "Papyrus", "Brush Script MT",
        "Lucida Handwriting", "Lucida Bright", "Lucida Sans Unicode",
        "Rockwell", "Rockwell Extra Bold", "Rockwell Condensed",
        "Copperplate", "Copperplate Gothic Light", "Copperplate Gothic Bold",
        "Didot", "Bodoni MT", "Baskerville", "Caslon",
        "Perpetua", "Trajan Pro", "Centaur",
        "Cambria", "Cambria Math",
        "Consolas", "Monaco",
        "Menlo", "Andale Mono",
        "Charcoal", "Chicago",
        "Symbol", "Webdings", "Wingdings",
        "MS Sans Serif", "MS Serif",
        "Palatino", "Bookman Old Style",
        "Century", "Century Schoolbook",
        "Goudy Old Style", "Hoefler Text",
        "Minion Pro", "Myriad Web",
        "Frutiger", "Stone Sans", "Stone Serif",
        "Avenir", "Avenir Next",
        "Trade Gothic", "News Gothic MT",
        "Eurostile", "Bank Gothic",
        "Kabel", "Syntax",
        "OCR A Extended", "Letter Gothic",
        "American Typewriter", "ITC Bookman",
        "ITC Avant Garde Gothic", "ITC Lubalin Graph",
        "Zapf Chancery", "Zapf Dingbats",
        "Apple Chancery", "Apple Garamond",
        "Skia", "Charlemagne Std",
      ];


      function applyWatermark(dataUrl, naturalW, naturalH) {
        return new Promise((resolve) => {
          if (!wmText.trim()) { resolve(dataUrl); return; }
          const img = new Image();
          img.onload = () => {
            const c = document.createElement("canvas");
            c.width = img.width;
            c.height = img.height;
            const ctx = c.getContext("2d");
            ctx.drawImage(img, 0, 0);
            ctx.save();
            ctx.globalAlpha = wmTextOpacity;
            // Normalize by natural dimensions so watermark size stays visually consistent
            // regardless of AR expansion. Without AR, naturalW===c.width and naturalH===c.height
            // so the ratio is 1. With AR, the canvas is larger so we scale up to compensate.
            const arScale = naturalW && naturalH
              ? Math.max(c.width / naturalW, c.height / naturalH)
              : 1;
            const scaledSize = wmTextSize * (scale || 1) * arScale;
            ctx.font = `${scaledSize}px ${wmTextFont}`;
            ctx.fillStyle = wmTextColor;
            const lineHeight = scaledSize * 1.3;
            const wmLines = wmText.split("\n");
            const maxWidth = Math.max(...wmLines.map(l => ctx.measureText(l).width));
            const totalHeight = lineHeight * wmLines.length;
            const [tx, ty] = getWmCoords(wmTextPos, c.width, c.height, maxWidth, totalHeight);
            wmLines.forEach((line, i) => {
              const lw = ctx.measureText(line).width;
              const lx = tx + (maxWidth - lw) / 2;
              ctx.fillText(line, lx, ty + scaledSize + i * lineHeight);
            });
            ctx.restore();
            resolve(c.toDataURL("image/png"));
          };
          img.src = dataUrl;
        });
      }

      const hasWatermark = wmText.trim() || wmImage;

      const getFilename = (item, index) => {
        const name = item.text;
        if (serialInFilename) {
          const serial = String(index + 1).padStart(String(previews.length).length, "0");
          return `${serial}_${name}.png`;
        }
        return `${name}.png`;
      };

      const downloadOne = async (item, index) => {
        const url = hasWatermark ? await applyWatermark(item.dataUrl) : item.dataUrl;
        const a = document.createElement("a");
        a.href = url;
        a.download = getFilename(item, index);
        a.click();
      };

      const downloadAll = () => {
        previews.forEach((item, i) => {
          setTimeout(() => downloadOne(item, i), i * 200);
        });
      };

      const downloadZip = async () => {
        const zip = new JSZip();
        for (let idx = 0; idx < previews.length; idx++) {
          const item = previews[idx];
          const url = hasWatermark ? await applyWatermark(item.dataUrl) : item.dataUrl;
          const base64 = url.split(",")[1];
          zip.file(getFilename(item, idx), base64, { base64: true });
        }
        const blob = await zip.generateAsync({ type: "blob" });
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = "kanji-studio.zip";
        a.click();
        URL.revokeObjectURL(a.href);
      };

      const applyPreset = (i, type) => {
        const list = type === "gradient" ? GRADIENT_PRESETS : PRESETS;
        const p = list[i];
        setSelectedPreset(i);
        setPresetType(type || presetType);
        setBgColor(p.bg);
        setFgColor(p.fg);
        setFontIndex(p.font);
      };

      const pagedFonts = FONTS.slice(fontPage * FONTS_PER_PAGE, (fontPage + 1) * FONTS_PER_PAGE);

      return (
        <div style={{
          height: "100vh",
          overflow: "hidden",
          background: t.bg,
          color: t.text,
          fontFamily: "'Segoe UI', system-ui, sans-serif",
          padding: "0",
          transition: "background 0.3s, color 0.3s",
        }}>
          <style>{`
            @import url('https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;700&display=swap');
            * { box-sizing: border-box; margin: 0; padding: 0; }
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: ${t.scrollThumb}; border-radius: 3px; }
            .panel { background: ${t.panelBg}; border: 1px solid ${t.panelBorder}; border-radius: 14px; padding: 24px; }
            .label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.6px; color: ${t.muted}; margin-bottom: 8px; font-weight: 600; }
            textarea { width: 100%; background: ${t.inputBg}; border: 1px solid ${t.inputBorder}; border-radius: 10px; color: ${t.text}; padding: 14px; font-size: 18px; font-family: 'Zen Kaku Gothic New', sans-serif; resize: vertical; min-height: 160px; outline: none; transition: border-color 0.2s; line-height: 1.7; }
            textarea:focus { border-color: ${t.inputFocusBorder}; }
            input[type="range"] { -webkit-appearance: none; width: 100%; height: 4px; background: ${t.toggleTrack}; border-radius: 2px; outline: none; }
            input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: ${t.toggleKnob}; cursor: pointer; }
            input[type="color"] { -webkit-appearance: none; border: 2px solid ${t.inputBorder}; border-radius: 8px; width: 44px; height: 44px; cursor: pointer; overflow: hidden; padding: 2px; background: transparent; }
            input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
            input[type="color"]::-webkit-color-swatch { border: none; border-radius: 5px; }
            .preset-btn { padding: 8px 14px; border-radius: 8px; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.muted}; font-size: 12px; cursor: pointer; transition: all 0.2s; font-weight: 500; }
            .preset-btn:hover { background: ${t.presetHoverBg}; color: ${t.text}; }
            .preset-btn.active { background: ${t.presetActiveBg}; color: ${theme === "dark" ? "#fff" : "#000"}; border-color: ${t.presetActiveBorder}; }
            .gen-btn { width: 100%; padding: 16px; border-radius: 12px; border: none; font-size: 15px; font-weight: 700; cursor: pointer; letter-spacing: 0.5px; transition: all 0.25s; }
            .gen-btn.primary { background: ${t.accentGradient}; color: ${t.accentBtnText}; }
            .gen-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 8px 30px rgba(201,169,110,0.25); }
            .gen-btn.secondary { background: ${t.secondaryBtnBg}; color: ${t.accent}; border: 1px solid ${t.secondaryBtnBorder}; }
            .gen-btn.secondary:hover { background: rgba(201,169,110,0.1); }
            .card { border-radius: 12px; overflow: hidden; border: 1px solid ${t.cardBorder}; background: ${t.cardBg}; transition: all 0.25s; position: relative; }
            .card:hover { border-color: ${t.cardHoverBorder}; transform: translateY(-2px); box-shadow: 0 12px 40px rgba(0,0,0,0.3); }
            .card img { width: 100%; display: block; }
            .card-overlay { position: absolute; inset: 0; background: ${t.overlayBg}; opacity: 0; transition: opacity 0.25s; display: flex; align-items: center; justify-content: center; }
            .card:hover .card-overlay { opacity: 1; }
            .dl-btn { padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: #fff; font-size: 13px; cursor: pointer; backdrop-filter: blur(10px); transition: background 0.2s; }
            .dl-btn:hover { background: rgba(255,255,255,0.2); }
            .font-option { padding: 10px 14px; border-radius: 8px; border: 1px solid ${t.fontOptionBorder}; background: ${t.fontOptionBg}; cursor: pointer; transition: all 0.2s; text-align: center; }
            .font-option:hover { background: ${t.fontOptionHoverBg}; }
            .font-option.active { background: ${t.fontOptionActiveBg}; border-color: ${t.fontOptionActiveBorder}; }
            .toggle { display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; color: ${t.muted}; }
            .toggle-track { width: 36px; height: 20px; border-radius: 10px; background: ${t.toggleTrack}; position: relative; transition: background 0.2s; }
            .toggle-track.on { background: rgba(201,169,110,0.5); }
            .toggle-knob { width: 16px; height: 16px; border-radius: 50%; background: ${t.toggleKnob}; position: absolute; top: 2px; left: 2px; transition: transform 0.2s; }
            .toggle-track.on .toggle-knob { transform: translateX(16px); }
            .badge { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; background: ${t.badgeBg}; color: ${t.accent}; }
            .page-btn { padding: 4px 10px; border-radius: 6px; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.muted}; font-size: 12px; cursor: pointer; transition: all 0.2s; }
            .page-btn:hover:not(:disabled) { background: ${t.presetHoverBg}; color: ${t.text}; }
            .page-btn:disabled { opacity: 0.3; cursor: default; }
            select { color-scheme: ${theme === "dark" ? "dark" : "light"}; }
            select option { background: ${theme === "dark" ? "#1a1a2e" : "#ffffff"}; color: ${t.text}; }
            .theme-toggle-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.muted}; font-size: 16px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; padding: 0; }
            .theme-toggle-btn:hover { background: ${t.presetHoverBg}; color: ${t.text}; }
            .clear-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 9999; }
            .clear-modal { background: ${theme === "dark" ? "#1a1520" : "#f5f3f0"}; border: 1px solid ${t.presetBorder}; border-radius: 12px; padding: 28px 32px; max-width: 380px; width: 90%; }
            .clear-modal h3 { margin: 0 0 10px; color: ${t.text}; font-size: 17px; }
            .clear-modal p { margin: 0 0 18px; color: ${t.muted}; font-size: 14px; }
            .clear-modal input { width: 100%; box-sizing: border-box; padding: 8px 12px; border-radius: 8px; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.text}; font-size: 15px; font-family: monospace; outline: none; }
            .clear-modal input:focus { border-color: ${t.accent}; }
            .clear-modal-btns { display: flex; gap: 10px; margin-top: 18px; justify-content: flex-end; }
            .clear-modal-cancel { padding: 7px 18px; border-radius: 8px; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.muted}; font-size: 13px; cursor: pointer; }
            .clear-modal-confirm { padding: 7px 18px; border-radius: 8px; border: none; background: #c0392b; color: #fff; font-size: 13px; cursor: pointer; opacity: 0.4; transition: opacity 0.2s; }
            .clear-modal-confirm.ready { opacity: 1; }
            @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
            .fade-in { animation: fadeIn 0.4s ease forwards; }
          `}</style>

          <div style={{ padding: "20px 40px", maxWidth: 1400, margin: "0 auto" }}>
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <div>
                <div style={{ display: "flex", alignItems: "center", gap: 16, marginBottom: 6 }}>
                  <div style={{ fontSize: 32, fontFamily: "'Zen Kaku Gothic New', sans-serif", fontWeight: 700, color: t.text, letterSpacing: "-0.5px" }}>
                    漢字 <span style={{ color: t.accent }}>Studio</span>
                  </div>
                  <span className="badge">v2.6</span>
                </div>
                <p style={{ color: t.dimmed, fontSize: 14, letterSpacing: "0.3px" }}>
                  Bulk image generator for Japanese vocabulary
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
                <div className="clear-modal-overlay" onClick={(e) => { if (e.target === e.currentTarget) setShowClearModal(false); }}>
                  <div className="clear-modal">
                    <h3>Clear Cache</h3>
                    <p>This will erase all saved preferences. Type <strong>CLEAR</strong> to confirm.</p>
                    <input
                      autoFocus
                      value={clearConfirmText}
                      onChange={(e) => setClearConfirmText(e.target.value)}
                      placeholder="Type CLEAR"
                      onKeyDown={(e) => { if (e.key === "Enter" && clearConfirmText === "CLEAR") clearCache(); if (e.key === "Escape") setShowClearModal(false); }}
                    />
                    <div className="clear-modal-btns">
                      <button className="clear-modal-cancel" onClick={() => setShowClearModal(false)}>Cancel</button>
                      <button className={"clear-modal-confirm" + (clearConfirmText === "CLEAR" ? " ready" : "")} disabled={clearConfirmText !== "CLEAR"} onClick={clearCache}>Clear</button>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "380px 1fr", gap: 28, padding: "12px 40px 0", maxWidth: 1400, margin: "0 auto", height: "calc(100vh - 110px)" }}>

            <div style={{ overflowY: "auto", paddingRight: 8, display: "flex", flexDirection: "column", gap: 20 }}>

              <div className="panel">
                <div className="label">Vocabulary List</div>
                <textarea
                  value={text}
                  onChange={(e) => setText(e.target.value)}
                  placeholder={"One word per line...\n名前\n漢字\n日本語"}
                />
                <div style={{ marginTop: 8, fontSize: 12, color: t.dimmed }}>
                  {lines.length} word{lines.length !== 1 ? "s" : ""} ready
                </div>
              </div>

              <div className="panel">
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 8 }}>
                  <div className="label" style={{ marginBottom: 0 }}>Style Presets — {presetType === "gradient" ? GRADIENT_PRESETS.length : PRESETS.length}</div>
                  <select
                    value={presetType}
                    onChange={(e) => { setPresetType(e.target.value); setSelectedPreset(0); }}
                    style={{ background: t.presetBg, border: `1px solid ${t.presetBorder}`, borderRadius: 8, color: t.muted, padding: "6px 12px", fontSize: 12, fontWeight: 500, cursor: "pointer", transition: "all 0.2s" }}
                    onMouseEnter={(e) => { e.target.style.background = t.presetHoverBg; e.target.style.color = t.text; }}
                    onMouseLeave={(e) => { e.target.style.background = t.presetBg; e.target.style.color = t.muted; }}
                  >
                    <option value="solid">Solid Color</option>
                    <option value="gradient">Gradient</option>
                  </select>
                </div>
                <div style={{ display: "flex", flexWrap: "wrap", gap: 6, maxHeight: 200, overflowY: "auto", paddingRight: 4 }}>
                  {(presetType === "gradient" ? GRADIENT_PRESETS : PRESETS).map((p, i) => (
                    <button
                      key={i}
                      className={`preset-btn ${selectedPreset === i ? "active" : ""}`}
                      onClick={() => applyPreset(i, presetType)}
                    >
                      <span style={{
                        display: "inline-block", width: 10, height: 10, borderRadius: "50%",
                        background: presetType === "gradient" ? p.bg : p.fg,
                        border: presetType === "gradient" ? "none" : `2px solid ${p.bg}`,
                        marginRight: 6, verticalAlign: "middle",
                        boxShadow: `0 0 0 1px rgba(255,255,255,0.1)`,
                      }} />
                      {p.label}
                    </button>
                  ))}
                </div>
              </div>

              <div className="panel">
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 8 }}>
                  <div className="label" style={{ marginBottom: 0 }}>Typeface — {FONTS.length}</div>
                </div>
                <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 6, maxHeight: 260, overflowY: "auto", paddingRight: 4 }}>
                  {FONTS.map((f, i) => (
                    <div
                      key={i}
                      className={`font-option ${fontIndex === i ? "active" : ""}`}
                      onClick={() => setFontIndex(i)}
                      style={{ fontFamily: fontsLoaded[i] ? f.value : "inherit" }}
                    >
                      <div style={{ fontSize: 22, marginBottom: 2 }}>漢字</div>
                      <div style={{ fontSize: 10, color: t.muted, fontFamily: "system-ui" }}>{f.label}</div>
                    </div>
                  ))}
                </div>
                <div style={{ marginTop: 12 }}>
                  <label className="toggle" onClick={() => setBold(!bold)}>
                    <div className={`toggle-track ${bold ? "on" : ""}`}>
                      <div className="toggle-knob" />
                    </div>
                    Bold weight
                  </label>
                </div>
              </div>

              <div className="panel">
                <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16, rowGap: 14 }}>
                  <div>
                    <div className="label">Font Size — {fontSize}px</div>
                    <input type="range" min={48} max={600} value={fontSize} onChange={(e) => setFontSize(+e.target.value)} />
                  </div>
                  <div>
                    <div className="label">Padding — {padding}px</div>
                    <input type="range" min={0} max={200} value={padding} onChange={(e) => setPadding(+e.target.value)} />
                  </div>
                  <div>
                    <div className="label">Roundness — {roundness}%</div>
                    <input type="range" min={0} max={50} value={roundness} onChange={(e) => setRoundness(+e.target.value)} />
                  </div>
                  <div>
                    <div className="label">Resolution — {scale}x</div>
                    <input type="range" min={0.5} max={10} step={0.5} value={scale} onChange={(e) => setScale(+e.target.value)} />
                  </div>
                </div>
                <div style={{ marginTop: 14 }}>
                  <label style={{ display: "flex", alignItems: "center", gap: 8, cursor: "pointer", userSelect: "none" }}>
                    <input
                      type="checkbox"
                      checked={aspectRatioEnabled}
                      onChange={(e) => setAspectRatioEnabled(e.target.checked)}
                      style={{ accentColor: t.accent, width: 15, height: 15 }}
                    />
                    <span className="label" style={{ margin: 0 }}>Fixed Aspect Ratio</span>
                  </label>
                  {aspectRatioEnabled && (
                    <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 8 }}>
                      <input
                        type="number"
                        min={0.1}
                        step={0.1}
                        value={aspectRatioW}
                        onChange={(e) => setAspectRatioW(Math.max(0.1, +e.target.value))}
                        style={{ width: 60, background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "5px 8px", fontSize: 13, textAlign: "center" }}
                      />
                      <span style={{ color: t.muted, fontSize: 14 }}>:</span>
                      <input
                        type="number"
                        min={0.1}
                        step={0.1}
                        value={aspectRatioH}
                        onChange={(e) => setAspectRatioH(Math.max(0.1, +e.target.value))}
                        style={{ width: 60, background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "5px 8px", fontSize: 13, textAlign: "center" }}
                      />
                      <span style={{ color: t.muted, fontSize: 11 }}>W : H</span>
                      <div style={{ marginLeft: "auto", display: "flex", gap: 4 }}>
                        {[["1:1","1","1"],["4:3","4","3"],["3:4","3","4"],["16:9","16","9"]].map(([label,w,h]) => (
                          <button
                            key={label}
                            onClick={() => { setAspectRatioW(+w); setAspectRatioH(+h); }}
                            style={{ background: (aspectRatioW===+w && aspectRatioH===+h) ? t.accent : "none", border: `1px solid ${t.inputBorder}`, borderRadius: 4, color: (aspectRatioW===+w && aspectRatioH===+h) ? "#000" : t.muted, padding: "3px 6px", fontSize: 10, cursor: "pointer" }}
                          >{label}</button>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </div>

              <div className="panel">
                <div className="label">Colors</div>
                <div style={{ display: "flex", gap: 20, alignItems: "center" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                    {bgColor.includes("gradient") ? (
                      <div style={{ width: 44, height: 44, borderRadius: 8, background: bgColor, border: `2px solid ${t.inputBorder}`, flexShrink: 0 }} />
                    ) : (
                      <input type="color" value={bgColor} onChange={(e) => setBgColor(e.target.value)} />
                    )}
                    <span style={{ fontSize: 12, color: t.muted }}>Background</span>
                  </div>
                  <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                    <input type="color" value={fgColor} onChange={(e) => setFgColor(e.target.value)} />
                    <span style={{ fontSize: 12, color: t.muted }}>Text</span>
                  </div>
                  <button
                    style={{ marginLeft: "auto", background: "none", border: `1px solid ${t.swapBtnBorder}`, borderRadius: 6, color: t.muted, padding: "6px 10px", fontSize: 11, cursor: "pointer" }}
                    onClick={() => { if (!bgColor.includes("gradient")) { const tmp = bgColor; setBgColor(fgColor); setFgColor(tmp); } }}
                    disabled={bgColor.includes("gradient")}
                  >
                    ⇄ Swap
                  </button>
                </div>
              </div>

              <div className="panel">
                <div className="label">Watermark</div>
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  {/* Text watermark */}
                  <div>
                    <div style={{ fontSize: 12, color: t.muted, marginBottom: 4 }}>Text</div>
                    <textarea
                      value={wmText}
                      onChange={(e) => setWmText(e.target.value)}
                      placeholder="e.g. © My Name"
                      rows={3}
                      style={{ width: "100%", background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 8, color: t.text, padding: "8px 10px", fontSize: 13, outline: "none", resize: "vertical", fontFamily: "inherit", boxSizing: "border-box" }}
                    />
                  </div>
                  {wmText.trim() && (
                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10 }}>
                      <div>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Position</div>
                        <select value={wmTextPos} onChange={(e) => setWmTextPos(e.target.value)} style={{ width: "100%", background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "6px", fontSize: 12 }}>
                          {WM_POSITIONS.map(p => <option key={p} value={p}>{p}</option>)}
                        </select>
                      </div>
                      <div>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Color</div>
                        <input type="color" value={wmTextColor} onChange={(e) => setWmTextColor(e.target.value)} style={{ width: 36, height: 36 }} />
                      </div>
                      <div style={{ gridColumn: "1 / -1" }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Font</div>
                        <select value={wmTextFont} onChange={(e) => setWmTextFont(e.target.value)} style={{ width: "100%", background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "6px", fontSize: 12, fontFamily: wmTextFont }}>
                          {WM_FONTS.map(f => <option key={f} value={f} style={{ fontFamily: f }}>{f}</option>)}
                        </select>
                      </div>
                      <div style={{ gridColumn: "1 / -1" }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Size — {wmTextSize}px</div>
                        <input type="range" min={0} max={72} step={0.25} value={wmTextSize} onChange={(e) => setWmTextSize(+e.target.value)} style={{ width: "100%" }} />
                      </div>
                      <div style={{ gridColumn: "1 / -1" }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Opacity — {Math.round(wmTextOpacity * 100)}%</div>
                        <input type="range" min={0} max={100} step={1} value={Math.round(wmTextOpacity * 100)} onChange={(e) => setWmTextOpacity(+e.target.value / 100)} style={{ width: "100%" }} />
                      </div>
                    </div>
                  )}
                  {/* Image watermark */}
                  <div>
                    <div style={{ fontSize: 12, color: t.muted, marginBottom: 4 }}>Image</div>
                    <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                      <div style={{ display: "flex", gap: 6 }}>
                        <input
                          type="text"
                          placeholder="Image URL"
                          value={wmImageUrl}
                          onChange={(e) => setWmImageUrl(e.target.value)}
                          onBlur={(e) => {
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
                          <input type="file" accept="image/*" style={{ display: "none" }} onChange={(e) => {
                            const file = e.target.files[0];
                            if (file) {
                              const reader = new FileReader();
                              reader.onload = (ev) => { setWmImage({ src: ev.target.result, name: file.name }); setWmImageUrl(file.name); }
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
                      <div>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Position</div>
                        <select value={wmImagePos} onChange={(e) => setWmImagePos(e.target.value)} style={{ width: "100%", background: t.inputBg, border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.text, padding: "6px", fontSize: 12 }}>
                          {WM_POSITIONS.map(p => <option key={p} value={p}>{p}</option>)}
                        </select>
                      </div>
                      <div>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Scale — {Math.round(wmImageScale * 100)}%</div>
                        <input type="range" min={0} max={100} step={1} value={Math.round(wmImageScale * 100)} onChange={(e) => setWmImageScale(+e.target.value / 100)} />
                      </div>
                      <div style={{ gridColumn: "span 2" }}>
                        <div style={{ fontSize: 11, color: t.muted, marginBottom: 4 }}>Opacity — {Math.round(wmImageOpacity * 100)}%</div>
                        <input type="range" min={0} max={100} step={1} value={Math.round(wmImageOpacity * 100)} onChange={(e) => setWmImageOpacity(+e.target.value / 100)} />
                      </div>
                    </div>
                  )}
                </div>
              </div>

              <div style={{ position: "sticky", bottom: 0, display: "flex", flexDirection: "column", gap: 10, paddingTop: 12, paddingBottom: 16, paddingRight: 8, background: theme === "dark" ? "#0d0d0d" : "#f0ece8", zIndex: 1 }}>
                <button className="gen-btn primary" onClick={generatePreviews} disabled={generating || lines.length === 0}>
                  {generating ? "Generating..." : `Generate ${lines.length} Image${lines.length !== 1 ? "s" : ""}`}
                </button>
                <div style={{ display: "flex", gap: 6 }}>
                  <button className="gen-btn secondary" onClick={downloadAll} disabled={previews.length === 0} style={{ flex: 1, opacity: previews.length === 0 ? 0.4 : 1, cursor: previews.length === 0 ? "default" : "pointer" }}>
                    ↓ Individual{previews.length > 0 ? ` (${previews.length})` : ""}
                  </button>
                  <button className="gen-btn secondary" onClick={downloadZip} disabled={previews.length === 0} style={{ flex: 1, opacity: previews.length === 0 ? 0.4 : 1, cursor: previews.length === 0 ? "default" : "pointer" }}>
                    ↓ ZIP{previews.length > 0 ? ` (${previews.length})` : ""}
                  </button>
                </div>
                <label style={{ display: "flex", alignItems: "center", gap: 8, fontSize: 13, color: t.muted, cursor: "pointer", userSelect: "none" }}>
                  <input type="checkbox" checked={serialInFilename} onChange={e => setSerialInFilename(e.target.checked)} style={{ accentColor: t.accent, width: 14, height: 14 }} />
                  Prefix filenames with serial number
                </label>
                <div style={{ display: "flex", gap: 6 }}>
                  <button className="gen-btn secondary" onClick={() => setText("")} style={{ flex: 1, color: "#e07070" }}>
                    ✕ Clear List
                  </button>
                  <button className="gen-btn secondary" onClick={() => setPreviews([])} disabled={previews.length === 0} style={{ flex: 1, color: "#e07070", opacity: previews.length === 0 ? 0.4 : 1, cursor: previews.length === 0 ? "default" : "pointer" }}>
                    ✕ Clear Previews
                  </button>
                </div>
              </div>
            </div>

            <div ref={previewRef} style={{ overflowY: "auto", paddingBottom: 40 }}>
              {previews.length === 0 ? (
                <div style={{
                  display: "flex", alignItems: "center", justifyContent: "center",
                  minHeight: 500, border: `1px dashed ${t.panelBorder}`, borderRadius: 14,
                  flexDirection: "column", gap: 12,
                }}>
                  <div style={{ fontSize: 48, opacity: 0.15 }}>筆</div>
                  <div style={{ color: t.dimmed, fontSize: 14 }}>Configure settings and hit Generate</div>
                </div>
              ) : (
                <div>
                  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16 }}>
                    <div style={{ fontSize: 13, color: t.muted }}>{previews.length} images generated</div>
                  </div>
                  <div style={{
                    display: "grid",
                    gridTemplateColumns: "repeat(auto-fill, minmax(220px, 1fr))",
                    gap: 16,
                  }}>
                    {previews.map((item, i) => (
                      <div key={i} className="card fade-in" style={{ animationDelay: `${i * 0.05}s`, opacity: 0 }}>
                        <img src={displayUrls[i] || item.dataUrl} alt={item.text} />
                        <div className="card-overlay" onClick={() => downloadOne(item, i)}>
                          <button className="dl-btn">↓ Download</button>
                        </div>
                        <div style={{ padding: "10px 14px", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                          <span style={{ fontSize: 13, color: t.muted }}>{item.text}</span>
                          <span style={{ fontSize: 11, color: t.dimmed }}>#{item.index}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      );
    }

    const root = ReactDOM.createRoot(document.getElementById("root"));
    root.render(<KanjiGenerator />);
  </script>
</body>
</html>
