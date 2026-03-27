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
  <title>Image Studio</title>
  <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <script src="https://unpkg.com/jszip@3/dist/jszip.min.js"></script>
</head>
<body>
  <div id="root"></div>
  <script type="text/babel" data-type="module">
    const { useState, useRef, useCallback, useEffect } = React;

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
        toggleTrack: "rgba(255,255,255,0.1)",
        toggleKnob: "#e0dcd4",
        badgeBg: "rgba(201,169,110,0.15)",
        secondaryBtnBg: "rgba(255,255,255,0.06)",
        secondaryBtnBorder: "rgba(201,169,110,0.3)",
        presetBg: "rgba(255,255,255,0.04)",
        presetBorder: "rgba(255,255,255,0.08)",
        presetHoverBg: "rgba(255,255,255,0.08)",
        dropZoneBorder: "rgba(255,255,255,0.15)",
        dropZoneHoverBg: "rgba(255,255,255,0.04)",
        stickyBg: "#0d0d0d",
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
        toggleTrack: "rgba(0,0,0,0.15)",
        toggleKnob: "#8b6914",
        badgeBg: "rgba(139,105,20,0.12)",
        secondaryBtnBg: "rgba(0,0,0,0.04)",
        secondaryBtnBorder: "rgba(139,105,20,0.3)",
        presetBg: "rgba(0,0,0,0.03)",
        presetBorder: "rgba(0,0,0,0.1)",
        presetHoverBg: "rgba(0,0,0,0.06)",
        dropZoneBorder: "rgba(0,0,0,0.2)",
        dropZoneHoverBg: "rgba(0,0,0,0.03)",
        stickyBg: "#f0ece8",
      },
    };

    const CACHE_KEY = "corner-rounder-prefs";

    function loadCache() {
      try {
        const raw = localStorage.getItem(CACHE_KEY);
        return raw ? JSON.parse(raw) : {};
      } catch { return {}; }
    }

    function saveCache(prefs) {
      try { localStorage.setItem(CACHE_KEY, JSON.stringify(prefs)); } catch {}
    }

    function binarySearchQuality(canvas, targetBytes) {
      return new Promise((resolve) => {
        // First try PNG
        canvas.toBlob((pngBlob) => {
          if (pngBlob && pngBlob.size <= targetBytes) {
            resolve({ blob: pngBlob, ext: "png", mime: "image/png" });
            return;
          }
          // Binary search JPEG quality
          let low = 0.01, high = 1.0, bestBlob = null, iterations = 0;
          const search = () => {
            if (iterations >= 12 || high - low < 0.005) {
              if (!bestBlob) {
                canvas.toBlob((fallback) => {
                  resolve({ blob: fallback, ext: "jpg", mime: "image/jpeg", warning: fallback.size > targetBytes });
                }, "image/jpeg", low);
              } else {
                resolve({ blob: bestBlob, ext: "jpg", mime: "image/jpeg" });
              }
              return;
            }
            const mid = (low + high) / 2;
            canvas.toBlob((blob) => {
              iterations++;
              if (blob.size <= targetBytes) {
                bestBlob = blob;
                low = mid;
              } else {
                high = mid;
              }
              search();
            }, "image/jpeg", mid);
          };
          search();
        }, "image/png");
      });
    }

    function CornerRounder() {
      const cached = React.useMemo(() => loadCache(), []);
      const c = (key, fallback) => cached[key] !== undefined ? cached[key] : fallback;

      const [images, setImages] = useState([]);
      const [processed, setProcessed] = useState([]);
      // Applied settings (used for processing)
      const [roundness, setRoundness] = useState(c("roundness", 12));
      const [targetSizeKB, setTargetSizeKB] = useState(c("targetSizeKB", 500));
      const [cornerBgColor, setCornerBgColor] = useState(c("cornerBgColor", "#ffffff"));
      const [theme, setTheme] = useState(c("theme", "dark"));
      const [processing, setProcessing] = useState(false);
      const [wmText, setWmText] = useState(c("wmText", ""));
      const [wmTextPos, setWmTextPos] = useState(c("wmTextPos", "Bottom Right"));
      const [wmTextSize, setWmTextSize] = useState(c("wmTextSize", 16));
      const [wmTextFont, setWmTextFont] = useState(c("wmTextFont", "Arial"));
      const [wmTextColor, setWmTextColor] = useState(c("wmTextColor", "#ffffff"));
      const [wmTextOpacity, setWmTextOpacity] = useState(c("wmTextOpacity", 0.5));
      const [wmImage, setWmImage] = useState(null);
      const [wmImageUrl, setWmImageUrl] = useState(c("wmImageUrl", ""));
      const [wmImagePos, setWmImagePos] = useState(c("wmImagePos", "Bottom Right"));
      const [wmImageOpacity, setWmImageOpacity] = useState(c("wmImageOpacity", 0.3));
      const [wmImageScale, setWmImageScale] = useState(c("wmImageScale", 0.2));
      const [downloadFormat, setDownloadFormat] = useState(c("downloadFormat", "png"));


      const [dragOver, setDragOver] = useState(false);
      const [showClearModal, setShowClearModal] = useState(false);
      const [clearConfirmInput, setClearConfirmInput] = useState("");
      const fileInputRef = useRef(null);
      const processTimerRef = useRef(null);

      const t = THEMES[theme];

      // Save preferences
      useEffect(() => {
        saveCache({ roundness, targetSizeKB, cornerBgColor, theme, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity, wmImageUrl, wmImagePos, wmImageOpacity, wmImageScale, downloadFormat });
      }, [roundness, targetSizeKB, cornerBgColor, theme, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity, wmImageUrl, wmImagePos, wmImageOpacity, wmImageScale, downloadFormat]);

      // Load watermark image from URL on mount
      useEffect(() => {
        if (wmImageUrl && !wmImage) {
          const url = wmImageUrl.trim();
          if (url.startsWith("data:") || url.startsWith("http")) {
            setWmImage({ src: url, name: url.split("/").pop() });
          }
        }
      }, []);

      const handleFiles = useCallback((fileList) => {
        const files = Array.from(fileList).filter(f => f.type.startsWith("image/"));
        if (files.length === 0) return;

        const newImages = [];
        let loaded = 0;
        files.forEach((file, idx) => {
          const reader = new FileReader();
          reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
              newImages.push({
                id: Date.now() + idx,
                file,
                name: file.name.replace(/\.[^.]+$/, ""),
                originalDataUrl: e.target.result,
                width: img.width,
                height: img.height,
              });
              loaded++;
              if (loaded === files.length) {
                setImages(prev => [...prev, ...newImages]);
              }
            };
            img.src = e.target.result;
          };
          reader.readAsDataURL(file);
        });
      }, []);

      // Process images when settings change
      useEffect(() => {
        if (images.length === 0) { setProcessed([]); return; }
        clearTimeout(processTimerRef.current);
        processTimerRef.current = setTimeout(() => {
          processAllImages();
        }, 300);
        return () => clearTimeout(processTimerRef.current);
      }, [images, roundness, targetSizeKB, cornerBgColor, wmText, wmTextPos, wmTextSize, wmTextFont, wmTextColor, wmTextOpacity, wmImage, wmImagePos, wmImageOpacity, wmImageScale, downloadFormat]);

      const processAllImages = async () => {
        setProcessing(true);
        const results = [];

        // Load watermark image element if needed
        let wmImgEl = null;
        if (wmImage) {
          wmImgEl = await new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = wmImage.src;
          });
        }

        for (const imgObj of images) {
          const result = await processImage(imgObj, wmImgEl);
          results.push(result);
        }
        setProcessed(results);
        setProcessing(false);
      };

      const processImage = async (imgObj, wmImgEl) => {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        canvas.width = imgObj.width;
        canvas.height = imgObj.height;
        const w = canvas.width, h = canvas.height;
        const radius = Math.min(w, h) * (roundness / 100);

        // Clip to rounded rect and draw image (corners remain transparent)
        ctx.beginPath();
        ctx.roundRect(0, 0, w, h, radius);
        ctx.clip();

        // Draw the original image
        const srcImg = await new Promise((resolve) => {
          const img = new Image();
          img.onload = () => resolve(img);
          img.src = imgObj.originalDataUrl;
        });
        ctx.drawImage(srcImg, 0, 0, w, h);

        // Image watermark layer
        if (wmImgEl) {
          ctx.save();
          ctx.globalAlpha = wmImageOpacity;
          const ww = w * wmImageScale;
          const wh = (wmImgEl.height / wmImgEl.width) * ww;
          const [ix, iy] = getWmCoords(wmImagePos, w, h, ww, wh);
          ctx.drawImage(wmImgEl, ix, iy, ww, wh);
          ctx.restore();
        }

        // Text watermark layer
        if (wmText.trim()) {
          ctx.save();
          ctx.globalAlpha = wmTextOpacity;
          ctx.font = `${wmTextSize}px ${wmTextFont}`;
          ctx.fillStyle = wmTextColor;
          const lineHeight = wmTextSize * 1.3;
          const wmLines = wmText.split("\n");
          const maxWidth = Math.max(...wmLines.map(l => ctx.measureText(l).width));
          const totalHeight = lineHeight * wmLines.length;
          const [tx, ty] = getWmCoords(wmTextPos, w, h, maxWidth, totalHeight);
          wmLines.forEach((line, i) => {
            const lw = ctx.measureText(line).width;
            const lx = tx + (maxWidth - lw) / 2;
            ctx.fillText(line, lx, ty + wmTextSize + i * lineHeight);
          });
          ctx.restore();
        }

        // Export as PNG or JPG based on selected format
        let blob, ext, warning, previewUrl;
        if (downloadFormat === "jpg") {
          // For JPG, flatten transparent corners with the chosen fill color
          const flatCanvas = document.createElement("canvas");
          flatCanvas.width = w;
          flatCanvas.height = h;
          const flatCtx = flatCanvas.getContext("2d");
          flatCtx.fillStyle = cornerBgColor;
          flatCtx.fillRect(0, 0, w, h);
          flatCtx.drawImage(canvas, 0, 0);
          blob = await new Promise(r => flatCanvas.toBlob(r, "image/jpeg", 0.92));
          ext = "jpg";
          warning = false;
          previewUrl = flatCanvas.toDataURL("image/jpeg", 0.92);
        } else {
          blob = await new Promise(r => canvas.toBlob(r, "image/png"));
          ext = "png";
          warning = false;
          previewUrl = canvas.toDataURL("image/png");
        }

        return {
          id: imgObj.id,
          name: imgObj.name,
          blob,
          ext,
          previewUrl,
          sizeKB: Math.round(blob.size / 1024),
          warning,
        };
      };

      const downloadOne = (item) => {
        const url = URL.createObjectURL(item.blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `${item.name}_rounded.${item.ext}`;
        a.click();
        URL.revokeObjectURL(url);
      };

      const downloadAll = () => {
        processed.forEach((item, i) => {
          setTimeout(() => downloadOne(item), i * 200);
        });
      };

      const downloadZip = async () => {
        if (processed.length === 0) return;
        const zip = new JSZip();
        for (const item of processed) {
          zip.file(`${item.name}_rounded.${item.ext}`, item.blob);
        }
        const blob = await zip.generateAsync({ type: "blob" });
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = "rounded-images.zip";
        a.click();
        URL.revokeObjectURL(a.href);
      };

      const removeImage = (id) => {
        setImages(prev => prev.filter(img => img.id !== id));
        setProcessed(prev => prev.filter(p => p.id !== id));
      };

      const clearAll = () => { setImages([]); setProcessed([]); };

      const clearCache = () => {
        setShowClearModal(true);
        setClearConfirmInput("");
      };

      const confirmClearCache = () => {
        localStorage.removeItem(CACHE_KEY);
        window.location.reload();
      };

      const formatSize = (kb) => kb >= 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${kb} KB`;

      const onDrop = useCallback((e) => {
        e.preventDefault();
        setDragOver(false);
        handleFiles(e.dataTransfer.files);
      }, [handleFiles]);

      return (
        <>
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
            * { box-sizing: border-box; margin: 0; padding: 0; }
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: ${t.scrollThumb}; border-radius: 3px; }
            .panel { background: ${t.panelBg}; border: 1px solid ${t.panelBorder}; border-radius: 14px; padding: 24px; }
            .label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.6px; color: ${t.muted}; margin-bottom: 8px; font-weight: 600; }
            input[type="range"] { -webkit-appearance: none; width: 100%; height: 4px; background: ${t.toggleTrack}; border-radius: 2px; outline: none; }
            input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: ${t.toggleKnob}; cursor: pointer; }
            input[type="color"] { -webkit-appearance: none; border: 2px solid ${t.inputBorder}; border-radius: 8px; width: 44px; height: 44px; cursor: pointer; overflow: hidden; padding: 2px; background: transparent; }
            input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
            input[type="color"]::-webkit-color-swatch { border: none; border-radius: 5px; }
            .gen-btn { width: 100%; padding: 16px; border-radius: 12px; border: none; font-size: 15px; font-weight: 700; cursor: pointer; letter-spacing: 0.5px; transition: all 0.25s; }
            .gen-btn.primary { background: ${t.accentGradient}; color: ${t.accentBtnText}; }
            .gen-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 8px 30px rgba(201,169,110,0.25); }
            .gen-btn.secondary { background: ${t.secondaryBtnBg}; color: ${t.accent}; border: 1px solid ${t.secondaryBtnBorder}; }
            .gen-btn.secondary:hover { background: rgba(201,169,110,0.1); }
            .card { border-radius: 12px; overflow: hidden; border: 1px solid ${t.cardBorder}; background: ${t.cardBg}; transition: all 0.25s; position: relative; }
            .card:hover { border-color: ${t.cardHoverBorder}; transform: translateY(-2px); box-shadow: 0 12px 40px rgba(0,0,0,0.3); }
            .card img { width: 100%; display: block; }
            .card-overlay { position: absolute; inset: 0; background: ${t.overlayBg}; opacity: 0; transition: opacity 0.25s; display: flex; align-items: center; justify-content: center; gap: 8; }
            .card:hover .card-overlay { opacity: 1; }
            .dl-btn { padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: #fff; font-size: 13px; cursor: pointer; backdrop-filter: blur(10px); transition: background 0.2s; }
            .dl-btn:hover { background: rgba(255,255,255,0.2); }
            .rm-btn { padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,100,100,0.3); background: rgba(255,100,100,0.1); color: #ff6b6b; font-size: 13px; cursor: pointer; backdrop-filter: blur(10px); transition: background 0.2s; }
            .rm-btn:hover { background: rgba(255,100,100,0.2); }
            .badge { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; background: ${t.badgeBg}; color: ${t.accent}; }
            .theme-toggle-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid ${t.presetBorder}; background: ${t.presetBg}; color: ${t.muted}; font-size: 16px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; padding: 0; }
            .theme-toggle-btn:hover { background: ${t.presetHoverBg}; color: ${t.text}; }
            select { color-scheme: ${theme === "dark" ? "dark" : "light"}; }
            select option { background: ${theme === "dark" ? "#1a1a2e" : "#ffffff"}; color: ${t.text}; }
            @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
            .fade-in { animation: fadeIn 0.4s ease forwards; }
          `}</style>

          <div style={{ padding: "20px 40px", maxWidth: 1400, margin: "0 auto" }}>
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <div>
                <div style={{ display: "flex", alignItems: "center", gap: 16, marginBottom: 6 }}>
                  <div style={{ fontSize: 32, fontWeight: 700, letterSpacing: "-0.5px" }}>
                    Image <span style={{ color: t.accent }}>Studio</span>
                  </div>
                  <span className="badge">v1.8</span>
                </div>
                <p style={{ color: t.dimmed, fontSize: 14, letterSpacing: "0.3px" }}>
                  Round corners, resize, and watermark your images in bulk
                </p>
              </div>
              <div style={{ display: "flex", gap: 8 }}>
                <button className="theme-toggle-btn" onClick={() => setTheme(theme === "dark" ? "light" : "dark")} title={theme === "dark" ? "Switch to light theme" : "Switch to dark theme"}>
                  {theme === "dark" ? "\u2600\uFE0F" : "\uD83C\uDF19"}
                </button>
                <button className="theme-toggle-btn" onClick={clearCache} title="Clear all saved preferences and reload">
                  {"\uD83D\uDDD1\uFE0F"}
                </button>
              </div>
            </div>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "380px 1fr", gap: 28, padding: "12px 40px 0", maxWidth: 1400, margin: "0 auto", height: "calc(100vh - 110px)" }}>

            <div style={{ overflowY: "auto", paddingRight: 8, display: "flex", flexDirection: "column", gap: 20 }}>

              {/* Drop Zone */}
              <div
                className="panel"
                onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                onDragLeave={() => setDragOver(false)}
                onDrop={onDrop}
                style={{
                  border: `2px dashed ${dragOver ? t.accent : t.dropZoneBorder}`,
                  background: dragOver ? t.dropZoneHoverBg : t.panelBg,
                  textAlign: "center",
                  cursor: "pointer",
                  transition: "all 0.2s",
                }}
                onClick={() => fileInputRef.current?.click()}
              >
                <input
                  ref={fileInputRef}
                  type="file"
                  accept="image/*"
                  multiple
                  style={{ display: "none" }}
                  onChange={(e) => { handleFiles(e.target.files); e.target.value = ""; }}
                />
                <div style={{ fontSize: 36, opacity: 0.3, marginBottom: 8 }}>+</div>
                <div style={{ color: t.muted, fontSize: 14 }}>Drop images here or click to browse</div>
                {images.length > 0 && (
                  <div style={{ marginTop: 8, fontSize: 12, color: t.accent }}>{images.length} image{images.length !== 1 ? "s" : ""} loaded</div>
                )}
              </div>

              {/* Roundness */}
              <div className="panel">
                <div className="label">Corner Roundness — {roundness}%</div>
                <input type="range" min={0} max={50} step={1} value={roundness} onChange={(e) => setRoundness(+e.target.value)} />
              </div>

              {/* File Size */}
              <div className="panel">
                <div className="label">Max File Size — {formatSize(targetSizeKB)}</div>
                <input type="range" min={50} max={10240} step={10} value={targetSizeKB} onChange={(e) => setTargetSizeKB(+e.target.value)} />
                <div style={{ display: "flex", justifyContent: "space-between", fontSize: 10, color: t.dimmed, marginTop: 4 }}>
                  <span>50 KB</span>
                  <span>10 MB</span>
                </div>
              </div>

              {/* Download Format */}
              <div className="panel">
                <div className="label" style={{ marginBottom: 8 }}>Download Format</div>
                <div style={{ display: "flex", gap: 8 }}>
                  {["png", "jpg"].map(fmt => (
                    <button key={fmt} onClick={() => setDownloadFormat(fmt)} style={{
                      flex: 1, padding: "8px 0", borderRadius: 8, border: `1px solid ${downloadFormat === fmt ? t.accent : t.inputBorder}`,
                      background: downloadFormat === fmt ? `rgba(201,169,110,0.15)` : t.inputBg,
                      color: downloadFormat === fmt ? t.accent : t.muted,
                      fontWeight: downloadFormat === fmt ? 600 : 400, fontSize: 13, cursor: "pointer", textTransform: "uppercase", letterSpacing: 1,
                    }}>{fmt}</button>
                  ))}
                </div>
              </div>

              {/* Corner Background Color — only needed for JPG */}
              {downloadFormat === "jpg" && (
              <div className="panel">
                <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                  <div>
                    <div className="label" style={{ marginBottom: 4 }}>Corner Fill Color</div>
                    <div style={{ fontSize: 11, color: t.dimmed }}>Applied to JPG corners</div>
                  </div>
                  <input type="color" value={cornerBgColor} onChange={(e) => setCornerBgColor(e.target.value)} />
                </div>
              </div>
              )}

              {/* Watermark */}
              <div className="panel">
                <div className="label">Watermark</div>
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  {/* Text watermark */}
                  <div>
                    <div style={{ fontSize: 12, color: t.muted, marginBottom: 4 }}>Text</div>
                    <textarea
                      value={wmText}
                      onChange={(e) => setWmText(e.target.value)}
                      placeholder={"e.g. \u00A9 My Name"}
                      rows={2}
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
                        <button onClick={() => { setWmImage(null); setWmImageUrl(""); }} style={{ background: "none", border: `1px solid ${t.inputBorder}`, borderRadius: 6, color: t.muted, cursor: "pointer", fontSize: 11, padding: "4px 8px" }}>{"\u2715"}</button>
                      </div>
                      <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                        <label style={{ padding: "5px 10px", borderRadius: 6, border: `1px solid ${t.inputBorder}`, background: t.inputBg, color: t.muted, fontSize: 11, cursor: "pointer" }}>
                          Choose File
                          <input type="file" accept="image/*" style={{ display: "none" }} onChange={(e) => {
                            const file = e.target.files[0];
                            if (file) {
                              const reader = new FileReader();
                              reader.onload = (ev) => { setWmImage({ src: ev.target.result, name: file.name }); setWmImageUrl(file.name); };
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

              {/* Action Buttons */}
              <div style={{ position: "sticky", bottom: 0, display: "flex", flexDirection: "column", gap: 10, paddingTop: 12, paddingBottom: 16, paddingRight: 8, background: t.stickyBg, zIndex: 1 }}>
                <div style={{ display: "flex", gap: 6 }}>
                  <button className="gen-btn secondary" onClick={downloadAll} disabled={processed.length === 0} style={{ flex: 1, opacity: processed.length === 0 ? 0.4 : 1, cursor: processed.length === 0 ? "default" : "pointer" }}>
                    {"\u2193"} {downloadFormat.toUpperCase()}{processed.length > 0 ? ` (${processed.length})` : ""}
                  </button>
                  <button className="gen-btn secondary" onClick={downloadZip} disabled={processed.length === 0} style={{ flex: 1, opacity: processed.length === 0 ? 0.4 : 1, cursor: processed.length === 0 ? "default" : "pointer" }}>
                    {"\u2193"} ZIP{processed.length > 0 ? ` (${processed.length})` : ""}
                  </button>
                </div>
                <button className="gen-btn secondary" onClick={clearAll} disabled={images.length === 0} style={{ opacity: images.length === 0 ? 0.4 : 1, cursor: images.length === 0 ? "default" : "pointer" }}>
                  Clear All
                </button>
              </div>
            </div>

            {/* Preview Grid */}
            <div style={{ overflowY: "auto", paddingBottom: 40 }}>
              {images.length === 0 ? (
                <div style={{
                  display: "flex", alignItems: "center", justifyContent: "center",
                  minHeight: 500, border: `1px dashed ${t.panelBorder}`, borderRadius: 14,
                  flexDirection: "column", gap: 12,
                }}>
                  <div style={{ fontSize: 48, opacity: 0.15 }}>{"\u25F1"}</div>
                  <div style={{ color: t.dimmed, fontSize: 14 }}>Upload images to get started</div>
                </div>
              ) : (
                <div>
                  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16 }}>
                    <div style={{ fontSize: 13, color: t.muted }}>
                      {processed.length} of {images.length} processed
                      {processing && <span style={{ marginLeft: 8, color: t.accent }}>Processing...</span>}
                    </div>
                  </div>
                  <div style={{
                    display: "grid",
                    gridTemplateColumns: "repeat(auto-fill, minmax(220px, 1fr))",
                    gap: 16,
                  }}>
                    {images.map((imgObj, i) => {
                      const proc = processed.find(p => p.id === imgObj.id);
                      return (
                        <div key={imgObj.id} className="card fade-in" style={{ animationDelay: `${i * 0.05}s`, opacity: 0 }}>
                          <img src={proc ? proc.previewUrl : imgObj.originalDataUrl} alt={imgObj.name} />
                          <div className="card-overlay">
                            {proc && <button className="dl-btn" onClick={() => downloadOne(proc)}>{"\u2193"} Download</button>}
                            <button className="rm-btn" onClick={() => removeImage(imgObj.id)}>{"\u2715"}</button>
                          </div>
                          <div style={{ padding: "10px 14px", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                            <span style={{ fontSize: 13, color: t.muted, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap", maxWidth: "60%" }}>{imgObj.name}</span>
                            <span style={{ fontSize: 11, color: proc?.warning ? "#ff6b6b" : t.dimmed }}>
                              {proc ? formatSize(proc.sizeKB) : "..."}
                            </span>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {showClearModal && (
          <div style={{
            position: "fixed", inset: 0, background: "rgba(0,0,0,0.7)",
            display: "flex", alignItems: "center", justifyContent: "center",
            zIndex: 9999, backdropFilter: "blur(4px)",
          }} onClick={() => setShowClearModal(false)}>
            <div style={{
              background: t.panelBg, border: `1px solid ${t.panelBorder}`,
              borderRadius: 16, padding: 28, maxWidth: 360, width: "90%",
              backdropFilter: "blur(20px)",
            }} onClick={e => e.stopPropagation()}>
              <div style={{ fontSize: 32, textAlign: "center", marginBottom: 12 }}>🗑️</div>
              <div style={{ fontSize: 16, fontWeight: 700, color: t.text, marginBottom: 8, textAlign: "center" }}>Clear Cache</div>
              <div style={{ fontSize: 13, color: t.muted, marginBottom: 20, textAlign: "center", lineHeight: 1.5 }}>
                This will clear all saved preferences and reload the page.<br />
                Type <strong style={{ color: t.accent }}>CLEAR</strong> to confirm.
              </div>
              <input
                type="text"
                value={clearConfirmInput}
                onChange={e => setClearConfirmInput(e.target.value)}
                placeholder="Type CLEAR"
                autoFocus
                onKeyDown={e => { if (e.key === "Enter" && clearConfirmInput === "CLEAR") confirmClearCache(); if (e.key === "Escape") setShowClearModal(false); }}
                style={{
                  width: "100%", padding: "10px 12px", borderRadius: 8,
                  border: `1px solid ${clearConfirmInput === "CLEAR" ? t.accent : t.inputBorder}`,
                  background: t.inputBg, color: t.text, fontSize: 14,
                  outline: "none", marginBottom: 16, textAlign: "center",
                  letterSpacing: 2,
                }}
              />
              <div style={{ display: "flex", gap: 10 }}>
                <button onClick={() => setShowClearModal(false)} style={{
                  flex: 1, padding: "10px", borderRadius: 8,
                  border: `1px solid ${t.panelBorder}`, background: t.secondaryBtnBg,
                  color: t.muted, fontSize: 13, cursor: "pointer",
                }}>Cancel</button>
                <button
                  onClick={confirmClearCache}
                  disabled={clearConfirmInput !== "CLEAR"}
                  style={{
                    flex: 1, padding: "10px", borderRadius: 8,
                    border: "none", background: clearConfirmInput === "CLEAR" ? "rgba(255,80,80,0.8)" : "rgba(255,80,80,0.2)",
                    color: clearConfirmInput === "CLEAR" ? "#fff" : "rgba(255,120,120,0.4)",
                    fontSize: 13, cursor: clearConfirmInput === "CLEAR" ? "pointer" : "default",
                    transition: "all 0.2s",
                  }}>Clear Cache</button>
              </div>
            </div>
          </div>
        )}
        </>
      );
    }

    const root = ReactDOM.createRoot(document.getElementById("root"));
    root.render(<CornerRounder />);
  </script>
</body>
</html>
