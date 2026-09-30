<?php
declare(strict_types=1);

session_start();

$siteName    = "Backstage 2.1";
$_SESSION["siteName"] = $siteName;
$studioName  = "On-Site Studios";
$_SESSION["studioName"] = $studioName;
$currentYear = date('Y');
$pageTitle   = "Dashboard — " . htmlspecialchars($siteName);

$loggedInStatusButton = "Log In";
if(isset($_SESSION["loggedInStatus"])) {
    $loggedInStatus    = $_SESSION["loggedInStatus"];
    if ($loggedInStatus == "In") {
        $loggedInStatusButton = "Log Out";
    }
}

include_once 'includes/navitems.php';
include_once 'includes/framepresets.php'; 

require_once 'includes/db.php';

// ── Load previously saved custom frame presets ────────────────
// Appends any custom presets added via "+ Add Custom Frame" and
// persisted to the framepresets table, so they reappear in the
// sidebar (and in the client-side PRESETS list used to rebuild
// saved widgets) on every subsequent page load — not just for the
// one page life in which they were created.
try {
    $pdo  = getWidgetsPDO();
    $stmt = $pdo->prepare(
        'SELECT preset_id, label, icon, url, color, note, default_w, default_h, tab
         FROM framepresets
         WHERE user_id = :uid
         ORDER BY id ASC'
    );
    $stmt->execute([':uid' => 3]);
    $savedPresets = $stmt->fetchAll();

    foreach ($savedPresets as $row) {
        $framePresets[] = [
            'id'       => $row['preset_id'],
            'label'    => $row['label'],
            'icon'     => $row['icon'],
            'url'      => $row['url'],
            'color'    => $row['color'],
            'note'     => $row['note'],
            'defaultW' => (int) $row['default_w'],
            'defaultH' => (int) $row['default_h'],
            'tab'      => $row['tab'],
        ];
    }
} catch (PDOException $e) {
    error_log('Failed to load saved frame presets: ' . $e->getMessage());
}

// ── Palette/frame-widget embed detection ──────────────────────
// When one of this site's own pages is dragged onto the canvas and
// loaded inside a frame-widget's iframe, the iframe src carries this
// flag (see withPalletFlag() in the client script below). In that
// context the page's own header/footer would be redundant — the
// dashboard shell around the canvas already provides them — so they
// are suppressed. A normal, non-embedded page load (navigating here
// directly, or this page being the outer/top-level document) is
// unaffected and still gets the full header/footer.
$isPalletEmbed = isset($_GET['pallet']) && $_GET['pallet'] === '1';

// ── Load previously saved widgets for this session ────────────
// Skipped for pallet-embedded loads (a frame-widget's own iframe
// pointed at this site) to avoid a saved self-referencing custom
// frame recursively re-spawning the whole canvas inside itself.
$savedWidgets = [];
if (!$isPalletEmbed) {
    try {
        $pdo  = getWidgetsPDO();
        $stmt = $pdo->prepare(
            'SELECT widget_uid, preset_id, label, url, pos_x, pos_y, width, height
             FROM widgets
             WHERE session_id = :sid AND user_id = :uid
             ORDER BY id ASC'
        );
        $stmt->execute([':sid' => session_id(), ':uid' => 3]);
        $savedWidgets = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Failed to load saved widgets: ' . $e->getMessage());
        $savedWidgets = [];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="css/bs20styles.css" rel="stylesheet">
</head>
<body>

<?php if (!$isPalletEmbed): ?>
<?php
include_once 'includes/header.php';
?>
<?php endif; ?>


<!-- Mobile Nav -->
<nav class="mobile-nav" id="mobileNav" aria-label="Mobile navigation">
    <?php foreach ($navItems as $item): ?>
        <?php $hasChildren = !empty($item['children']); ?>
        <div class="mobile-nav-item">
            <?php if ($hasChildren): ?>
                <a id="<?= htmlspecialchars($item['id']) ?>-mobile"
                   href="<?= htmlspecialchars($item['href']) ?>"
                   data-nav-id="<?= htmlspecialchars($item['id']) ?>"
                   onclick="handleNavClick(event, '<?= htmlspecialchars($item['id']) ?>')"
                   class="mobile-parent">
                    <?= htmlspecialchars($item['label']) ?>
                    <svg class="m-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
                <div class="mobile-submenu">
                    <?php foreach ($item['children'] as $child): ?>
                        <a id="<?= htmlspecialchars($child['id']) ?>-mobile"
                           href="<?= htmlspecialchars($child['href']) ?>"
                           data-nav-id="<?= htmlspecialchars($child['id']) ?>"
                           onclick="handleNavClick(event, '<?= htmlspecialchars($child['id']) ?>')">
                            <?= htmlspecialchars($child['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <a id="<?= htmlspecialchars($item['id']) ?>-mobile"
                   href="<?= htmlspecialchars($item['href']) ?>"
                   data-nav-id="<?= htmlspecialchars($item['id']) ?>"
                   onclick="handleNavClick(event, '<?= htmlspecialchars($item['id']) ?>')"
                   class="mobile-simple">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</nav>

<!-- =====================================================
     APP SHELL
===================================================== -->
<div class="app-shell">

    <!-- Flyout toggle -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle frame panel" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
        </svg>
    </button>

    <!-- RIGHT SIDEBAR — flyout frame palette -->
    <aside class="sidebar" id="sidebarPanel">
        <div class="sidebar-header">
            <h2>Frame Panels</h2>
            <p>Drag a frame onto the canvas to embed it</p>
        </div>

        <div class="frame-palette" id="framePalette">
            <?php foreach ($framePresets as $preset): ?>
                <?php if ($preset['id'] === 'custom') continue; ?>
                <div class="palette-item"
                     draggable="true"
                     data-tab="<?= htmlspecialchars($preset['tab'] ?? 'home') ?>"
                     data-preset='<?= htmlspecialchars(json_encode($preset), ENT_QUOTES) ?>'>
                    <div class="palette-icon" style="background:<?= htmlspecialchars($preset['color']) ?>">
                        <?= htmlspecialchars($preset['icon']) ?>
                    </div>
                    <div class="palette-info">
                        <div class="palette-name"><?= htmlspecialchars($preset['label']) ?></div>
                        <div class="palette-url"><?= htmlspecialchars($preset['url'] ?: 'Custom URL') ?></div>
                    </div>
                    <button class="palette-remove" title="Remove from sidebar" aria-label="Remove <?= htmlspecialchars($preset['label']) ?> from sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Custom URL entry -->
        <div class="custom-url-form">
            <input type="url" id="customUrl" placeholder="https://example.com" />
            <button id="addCustomBtn">+ Add Custom Frame</button>
        </div>
    </aside>

    <!-- CANVAS -->
    <div class="canvas-area" id="canvasArea">
        <div id="canvas-surface"></div>

        <!-- Drop hint -->
        <div class="drop-hint" id="dropHint">
            <div class="drop-hint-box">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.3"
                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                <h3>Your Dashboard Canvas</h3>
                <p>Drag frame panels from the panel on the right<br>and drop them anywhere here</p>
            </div>
        </div>

        <!-- Bottom toolbar -->
        <div class="canvas-toolbar">
            <button id="btnZoomOut" title="Zoom Out">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M11 17A6 6 0 1 0 11 5a6 6 0 0 0 0 12zM8 11h6"/>
                </svg>
            </button>
            <span class="zoom-label" id="zoomLabel">100%</span>
            <button id="btnZoomIn" title="Zoom In">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M11 17A6 6 0 1 0 11 5a6 6 0 0 0 0 12zM11 8v6m-3-3h6"/>
                </svg>
            </button>
            <div class="tb-sep"></div>
            <button id="btnReset" title="Reset View">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                Reset
            </button>
            <div class="tb-sep"></div>
            <button id="btnTile" title="Tile all open frames">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
                Tile
            </button>
            <div class="tb-sep"></div>
            <button id="btnClearAll" title="Clear all frames">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Clear All
            </button>
        </div>
    </div>

</div>

<!-- FOOTER -->
<?php if (!$isPalletEmbed): ?>
<?php
include_once 'includes/footer.php';
?>
<?php endif; ?>

<script>
// ============================================================
//  NAV CLICK HANDLER
//  Called by every menu item and sub-item (desktop + mobile).
//  Receives the mouse event and the item's unique string id.
// ============================================================

// Flat lookup: id → { label, href, parent } — built from PHP data
const NAV_MAP = (function() {
    const raw = <?= json_encode(array_values($navItems), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const map = {};
    raw.forEach(item => {
        map[item.id] = { id: item.id, label: item.label, href: item.href, parent: null };
        (item.children || []).forEach(child => {
            map[child.id] = { id: child.id, label: child.label, href: child.href, parent: item.id };
        });
    });
    return map;
})();

/**
 * handleNavClick — fired by onclick on every nav link.
 *
 * @param {MouseEvent} event  - the click event (used to prevent default for hash links)
 * @param {string}     navId  - the unique nav item id, e.g. 'nav-about-founders'
 */
function handleNavClick(event, navId) {
    const item = NAV_MAP[navId];
    if (!item) return;

    // Prevent page jump for hash-only hrefs so the dashboard stays in place.
    // Real hrefs (e.g. index.php, login.php) are allowed to navigate normally.
    if (item.href.startsWith('#')) {
        event.preventDefault();
    }

    // ── Your handler logic goes here ────────────────────────────
    console.log('[Nav Click]', {
        id:     item.id,
        label:  item.label,
        href:   item.href,
        parent: item.parent ?? '(top-level)',
    });
    // ── Example: dispatch a custom DOM event so other modules can listen ──
    document.dispatchEvent(new CustomEvent('nav:click', {
        bubbles: true,
        detail: { id: item.id, label: item.label, href: item.href, parent: item.parent }
    }));
}

// Tab → palette filtering
const TAB_MAP = {
    'nav-home':       'home',
    'nav-scheduling': 'scheduling',
};

function switchPaletteTab(tabKey) {
    document.querySelectorAll('.palette-item[data-tab]').forEach(el => {
        el.style.display = (el.dataset.tab === tabKey) ? '' : 'none';
    });
    const header = document.querySelector('.sidebar-header p');
    if (header) {
        header.textContent = tabKey === 'scheduling'
            ? 'Drag the calendar onto the canvas'
            : 'Drag a frame onto the canvas to embed it';
    }
}

// Initialise to home tab on load
switchPaletteTab('home');

document.addEventListener('nav:click', (e) => {
    const { id, label, parent } = e.detail;
    if (TAB_MAP[id]) switchPaletteTab(TAB_MAP[id]);
    showNavToast(id, label, parent);
});

// ── Lightweight dev toast ────────────────────────────────────
(function buildToast() {
    const toast = document.createElement('div');
    toast.id = 'nav-toast';
    Object.assign(toast.style, {
        position:      'fixed',
        bottom:        '76px',           // sits just above the footer
        left:          '50%',
        transform:     'translateX(-50%) translateY(12px)',
        background:    'rgba(26,16,48,0.92)',
        backdropFilter:'blur(10px)',
        border:        '1px solid rgba(147,112,219,0.45)',
        borderRadius:  '10px',
        padding:       '10px 20px',
        fontFamily:    "'DM Sans', sans-serif",
        fontSize:      '13px',
        color:         '#fff',
        zIndex:        '9999',
        opacity:       '0',
        transition:    'opacity 0.2s ease, transform 0.2s ease',
        pointerEvents: 'none',
        whiteSpace:    'nowrap',
    });
    document.body.appendChild(toast);
    window._navToast = toast;
})();

let _toastTimer = null;
function showNavToast(id, label, parent) {
    const toast = window._navToast;
    const parentStr = parent ? ` › <span style="opacity:.6">${parent}</span>` : '';
    toast.innerHTML = `<span style="opacity:.5;font-size:11px;letter-spacing:.8px;text-transform:uppercase;margin-right:8px;">Nav click</span>`
                    + `<strong>${label}</strong>${parentStr}`
                    + `<span style="opacity:.4;font-size:11px;margin-left:10px;">${id}</span>`;
    toast.style.opacity   = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(-50%) translateY(12px)';
    }, 2800);
}

// ============================================================
//  STATE
// ============================================================
const state = {
    widgets: [],       // { id, preset, x, y, w, h, el }
    selected: null,
    pan:  { x: 0, y: 0 },
    zoom: 1,
    idCounter: 0,
    dragPreset: null,  // preset being dragged from sidebar
};

const canvasArea  = document.getElementById('canvasArea');
const surface     = document.getElementById('canvas-surface');
const dropHint    = document.getElementById('dropHint');
const zoomLabel   = document.getElementById('zoomLabel');

// ============================================================
//  MAXIMISED WIDGET POSITIONING
//  Places the maximised frame's upper-right corner flush with the
//  canvasArea container's own upper-right corner (offset 0,0),
//  computed from the container's live bounding box rather than
//  assumed via banner/footer/sidebar CSS variables.
// ============================================================
function layoutMaximizedWidget(el) {
    const rect  = canvasArea.getBoundingClientRect();
    let right   = window.innerWidth - rect.right;   // 0 when flush with canvasArea's right edge
    let width   = rect.width;
    let top     = 0;

    // The sidebar flyout overlays canvasArea rather than shrinking it,
    // so pull the maximised frame in manually while the sidebar is open.
    if (document.body.classList.contains('sidebar-open')) {
        const sbWidth = parseFloat(
            getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width')
        ) || 0;
        right += sbWidth;
        width -= sbWidth;
    }

    el.style.top    = top + 'px';
    el.style.left   = 'auto';
    el.style.right  = right + 'px';
    el.style.width  = width + 'px';
    el.style.height = rect.height + 'px';
}

function layoutAllMaximizedWidgets() {
    document.querySelectorAll('.frame-widget.maximized').forEach(layoutMaximizedWidget);
}

window.addEventListener('resize', layoutAllMaximizedWidgets);

// ============================================================
//  PALETTE-EMBED URL TAGGING
//  When a frame-widget's iframe points at one of this site's own
//  pages, tag the URL with ?pallet=1 so that page knows to suppress
//  its own header.php/footer.php includes (the dashboard shell
//  already supplies them around the canvas). Pages outside this
//  site (or the palette entirely — i.e. a normal top-level visit)
//  are unaffected and keep their full header/footer.
// ============================================================
// ============================================================
//  EMBEDDED PAGE TOP-SPACE TRIM
//  Our own pages reserve room at the top for the fixed banner
//  (--banner-height, 150px) even when the banner is suppressed in
//  a frame (?pallet=1). Once such a page loads in a frame-widget,
//  inject a small style that collapses that reserved space.
//  Only same-origin pages can be adjusted; external sites
//  (Google, etc.) are left untouched.
// ============================================================
function tightenEmbeddedPage(iframe) {
    let doc;
    try {
        doc = iframe.contentDocument;
    } catch {
        return;                       // cross-origin: not accessible
    }
    if (!doc || !doc.head || doc.getElementById('pallet-embed-trim')) return;

    const style = doc.createElement('style');
    style.id = 'pallet-embed-trim';
    style.textContent = `
        :root {
            --banner-height: 0px !important;
            --footer-height: 0px !important;
        }
        html, body { margin-top: 0 !important; padding-top: 0 !important; }
        body > main {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding-top: 12px !important;
        }
    `;
    doc.head.appendChild(style);
    // Let pages that size themselves from the header/footer re-measure
    try { iframe.contentWindow.dispatchEvent(new Event('resize')); } catch {}
}

function withPalletFlag(url) {
    if (!url) return url;
    try {
        const u = new URL(url, window.location.href);
        if (u.origin === window.location.origin) {
            u.searchParams.set('pallet', '1');
        }
        return u.toString();
    } catch {
        return url;
    }
}

// ============================================================
//  PRESET DATA (from PHP, serialised as JSON)
// ============================================================
const PRESETS = <?= json_encode($framePresets, JSON_UNESCAPED_SLASHES) ?>;

// Widgets previously saved to the DB for this session — restored on load
// (see restoreSavedWidgets() below).
const SAVED_WIDGETS = <?= json_encode($savedWidgets, JSON_UNESCAPED_SLASHES) ?>;

// ============================================================
//  PERSISTENCE — save a widget's iframe attributes to the DB
//  Fires whenever a palette item is dropped onto the canvas.
//  Server keys rows by (session_id, widget_uid), so dropping a
//  new frame inserts a row and this can be re-used later (e.g.
//  after a drag/resize) to update that same row.
// ============================================================
function saveWidgetToServer(widget) {
    fetch('api/save_widget.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            widget_id: widget.id,
            preset_id: widget.preset.id,
            label:     widget.preset.label,
            url:       widget.preset.url,
            x: widget.x,
            y: widget.y,
            w: widget.w,
            h: widget.h,
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) console.error('Widget save failed:', data.error);
    })
    .catch(err => console.error('Widget save request failed:', err));
}

// ============================================================
//  PERSISTENCE — delete a widget's row from the DB
//  Fires whenever a widget is closed (× button or Clear All).
// ============================================================
function deleteWidgetFromServer(widgetId) {
    fetch('api/delete_widget.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ widget_id: widgetId }),
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) console.error('Widget delete failed:', data.error);
    })
    .catch(err => console.error('Widget delete request failed:', err));
}

// ============================================================
//  PERSISTENCE — save a custom frame preset to the DB
//  Fires whenever "+ Add Custom Frame" is used, so the entry
//  reappears in the sidebar (and in the PRESETS list used to
//  restore saved widgets) on future page loads.
// ============================================================
function saveFramePresetToServer(preset) {
    fetch('api/save_framepreset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            preset_id: preset.id,
            label:     preset.label,
            icon:      preset.icon,
            url:       preset.url,
            color:     preset.color,
            note:      preset.note,
            defaultW:  preset.defaultW,
            defaultH:  preset.defaultH,
            tab:       preset.tab || 'home',
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) console.error('Frame preset save failed:', data.error);
    })
    .catch(err => console.error('Frame preset save request failed:', err));
}

// ============================================================
//  UTILITIES
// ============================================================
const uid = () => ++state.idCounter;


function applyTransform() {
    surface.style.transform = `translate(${state.pan.x}px,${state.pan.y}px) scale(${state.zoom})`;
    zoomLabel.textContent   = Math.round(state.zoom * 100) + '%';
    // Re-enforce bounds for all widgets after every pan/zoom change
    state.widgets.forEach(w => positionWidget(w));
}

function canvasPoint(clientX, clientY) {
    const r = canvasArea.getBoundingClientRect();
    return {
        x: (clientX - r.left - state.pan.x) / state.zoom,
        y: (clientY - r.top  - state.pan.y) / state.zoom,
    };
}

function updateDropHint() {
    dropHint.classList.toggle('hidden', state.widgets.length > 0);
}

// ============================================================
//  BUILD FRAME WIDGET
// ============================================================
function createWidget(preset, cx, cy) {
    const id = uid();
    const b  = getCanvasBounds();

    // Default size — shrink if canvas is smaller
    const w  = Math.min(preset.defaultW || 700, b.visW);
    const h  = Math.min(preset.defaultH || 480, b.visH);

    // Centre on drop point, then clamp so widget is fully within bounds
    const x  = Math.max(b.minX, Math.min(cx - w / 2, b.maxX - w));
    const y  = Math.max(b.minY, Math.min(cy - h / 2, b.maxY - h));

    const widget = { id, preset, x, y, w, h, el: null };
    state.widgets.push(widget);

    const el = buildWidgetEl(widget);
    widget.el = el;
    surface.appendChild(el);
    positionWidget(widget);
    selectWidget(id);
    updateDropHint();
    return widget;
}

// ============================================================
//  RESTORE SAVED WIDGETS (from DB, on page load/refresh)
// ============================================================
function restoreSavedWidgets() {
    if (!SAVED_WIDGETS.length) return false;

    SAVED_WIDGETS.forEach(row => {
        const widgetUid = parseInt(row.widget_uid, 10);

        // Guard against duplicates: skip if a widget with this id is
        // already present on the canvas (e.g. this function somehow
        // runs more than once in the same page life).
        if (state.widgets.some(w => w.id === widgetUid)) return;

        // Recover the full preset (color/icon/defaults) from the known
        // preset list when possible, keeping any URL edits the user made;
        // otherwise fall back to a generic frame built from the saved row.
        let preset = PRESETS.find(p => p.id === row.preset_id);
        preset = preset
            ? { ...preset, url: row.url || preset.url }
            : {
                  id:       row.preset_id || ('custom_' + widgetUid),
                  label:    row.label || 'Frame',
                  icon:     (row.label || '?').charAt(0).toUpperCase(),
                  url:      row.url,
                  color:    '#5A3D96',
                  note:     '',
                  defaultW: 700,
                  defaultH: 480,
              };

        const widget = {
            id: widgetUid,
            preset,
            x: parseFloat(row.pos_x),
            y: parseFloat(row.pos_y),
            w: parseFloat(row.width),
            h: parseFloat(row.height),
            el: null,
        };

        state.widgets.push(widget);
        const el = buildWidgetEl(widget);
        widget.el = el;
        surface.appendChild(el);
        positionWidget(widget);

        // Keep the id counter ahead of every restored id so newly
        // created widgets never collide with a restored one.
        if (widgetUid > state.idCounter) state.idCounter = widgetUid;
    });

    updateDropHint();
    return true;
}

function buildWidgetEl(widget) {
    const p = widget.preset;

    const el = document.createElement('div');
    el.className    = 'frame-widget';
    el.dataset.id   = widget.id;

    // --- Title bar ---
    const bar = document.createElement('div');
    bar.className   = 'frame-titlebar';
    bar.style.background = p.color || '#7A55C7';

    const favicon = document.createElement('div');
    favicon.className = 'frame-favicon';
    favicon.style.background = 'rgba(255,255,255,0.2)';
    favicon.textContent = p.icon || '□';

    const title = document.createElement('span');
    title.className = 'frame-title';
    title.textContent = p.label;

    // Editable URL bar
    const urlInput = document.createElement('input');
    urlInput.className   = 'frame-url-input';
    urlInput.type        = 'text';
    urlInput.value       = p.url;
    urlInput.placeholder = 'https://...';
    urlInput.title       = 'Enter URL and press Enter to navigate';
    urlInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const iframe = el.querySelector('iframe');
            iframe.src = withPalletFlag(urlInput.value);
        }
        e.stopPropagation();
    });
    urlInput.addEventListener('pointerdown', (e) => e.stopPropagation());

    // Action buttons
    const actions = document.createElement('div');
    actions.className = 'frame-actions';

    // Reload
    const btnReload = makeFrameBtn(
        `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>`,
        'Reload'
    );
    btnReload.addEventListener('click', () => {
        const iframe = el.querySelector('iframe');
        iframe.src = iframe.src; // eslint-disable-line
    });

    // Minimise / restore
    const btnMin = makeFrameBtn(
        `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
        </svg>`,
        'Minimise'
    );
    let minimised = false;
    btnMin.addEventListener('click', () => {
        minimised = !minimised;
        const body = el.querySelector('.frame-body');
        const notice = el.querySelector('.frame-notice');
        body.style.display   = minimised ? 'none' : '';
        if (notice) notice.style.display = minimised ? 'none' : '';
        el.style.height      = minimised ? 'auto' : widget.h + 'px';
        btnMin.title         = minimised ? 'Restore' : 'Minimise';
    });

    // Maximise / restore
    const btnMax = makeFrameBtn(
        `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
        </svg>`,
        'Maximise'
    );
    let maximized = false;
    // Store the widget's pre-maximise geometry so we can restore it
    let savedGeom = null;

    btnMax.addEventListener('click', () => {
        maximized = !maximized;

        if (maximized) {
            // Save current geometry
            savedGeom = { x: widget.x, y: widget.y, w: widget.w, h: widget.h };

            el.classList.add('maximized');
            btnMax.classList.add('maximized-active');
            btnMax.title = 'Restore';
            btnMax.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 9V4H4v5h5zm6 0h5V4h-5v5zM9 15H4v5h5v-5zm6 0v5h5v-5h-5z"/>
            </svg>`;
            layoutMaximizedWidget(el);

            // Also un-minimise if the widget was minimised
            const body   = el.querySelector('.frame-body');
            const notice = el.querySelector('.frame-notice');
            if (body.style.display === 'none') {
                minimised = false;
                body.style.display   = '';
                if (notice) notice.style.display = '';
                btnMin.title = 'Minimise';
            }
        } else {
            el.classList.remove('maximized');
            btnMax.classList.remove('maximized-active');
            btnMax.title = 'Maximise';
            btnMax.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>`;

            // Clear the inline geometry JS applied while maximised
            el.style.top    = '';
            el.style.left   = '';
            el.style.right  = '';
            el.style.width  = '';
            el.style.height = '';

            // Restore saved geometry
            if (savedGeom) {
                widget.x = savedGeom.x;
                widget.y = savedGeom.y;
                widget.w = savedGeom.w;
                widget.h = savedGeom.h;
                positionWidget(widget);
            }
        }
    });

    // Close
    const btnClose = makeFrameBtn(
        `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>`,
        'Close'
    );
    btnClose.classList.add('close');
    btnClose.addEventListener('click', () => removeWidget(widget.id));

    actions.append(btnReload, btnMin, btnMax, btnClose);
    bar.append(favicon, title, urlInput, actions);

    // --- Notice banner (for sites that block embedding) ---
    let notice = null;
    if (p.note) {
        notice = document.createElement('div');
        notice.className = 'frame-notice';
        notice.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg> ${p.note}`;
    }

    // --- iframe body ---
    const body = document.createElement('div');
    body.className = 'frame-body';

    const iframe = document.createElement('iframe');
    iframe.src             = withPalletFlag(p.url) || 'about:blank';
    iframe.title           = p.label;
    iframe.loading         = 'lazy';
    // Permissive sandbox — allows scripts & same-origin; tighten as needed
    iframe.sandbox         = 'allow-scripts allow-same-origin allow-forms allow-popups allow-presentation';
    iframe.referrerpolicy  = 'no-referrer';

    // Overlay (blocks iframe pointer events during drag/resize)
    const overlay = document.createElement('div');
    overlay.className = 'iframe-overlay';

    // Trim the top gap on our own pages once they load in the frame
    iframe.addEventListener('load', () => tightenEmbeddedPage(iframe));

    body.append(iframe, overlay);

    // --- SE resize handle ---
    const resizeSE = document.createElement('div');
    resizeSE.className = 'resize-se';

    el.append(bar, ...(notice ? [notice] : []), body, resizeSE);

    // Events
    attachWidgetDrag(el, widget, bar);
    attachWidgetResize(el, widget, resizeSE);
    el.addEventListener('pointerdown', (e) => {
        if (!e.target.closest('.frame-btn') && !e.target.closest('input')) {
            selectWidget(widget.id);
        }
    });

    // Double-click title bar to maximise / restore
    bar.addEventListener('dblclick', (e) => {
        if (e.target.closest('.frame-btn') || e.target.closest('input')) return;
        btnMax.click();
    });

    return el;
}

function makeFrameBtn(svgHtml, title) {
    const btn = document.createElement('button');
    btn.className = 'frame-btn';
    btn.title     = title;
    btn.innerHTML = svgHtml;
    return btn;
}

// ============================================================
//  CANVAS BOUNDS — keeps widgets inside the visible canvas area.
//  All coordinates are in canvas-surface space (pre-zoom/pan).
//  The surface origin is at the top-left of canvasArea, so the
//  visible rectangle in surface-space is:
//    x: [-pan.x/zoom  …  (canvasArea.width  - pan.x) / zoom]
//    y: [-pan.y/zoom  …  (canvasArea.height - pan.y) / zoom]
//  We clamp so that the widget's top-left never goes below the
//  top-left corner AND the widget's bottom-right never exceeds
//  the bottom-right corner (widget is also shrunk if it is wider
//  than the visible canvas).
// ============================================================
function getCanvasBounds() {
    const r = canvasArea.getBoundingClientRect();
    // Visible canvas rectangle expressed in surface coordinates
    const minX =  -state.pan.x / state.zoom;
    const minY =  -state.pan.y / state.zoom;
    const maxX = (r.width  - state.pan.x) / state.zoom;
    const maxY = (r.height - state.pan.y) / state.zoom;
    return { minX, minY, maxX, maxY,
             visW: r.width  / state.zoom,
             visH: r.height / state.zoom };
}

function clampWidget(widget) {
    const b = getCanvasBounds();

    // Clamp size so widget is never larger than the canvas
    widget.w = Math.min(widget.w, b.visW);
    widget.h = Math.min(widget.h, b.visH);

    // Enforce minimum sizes
    widget.w = Math.max(widget.w, 280);
    widget.h = Math.max(widget.h, 180);

    // Clamp position so widget stays fully inside
    widget.x = Math.max(b.minX, Math.min(widget.x, b.maxX - widget.w));
    widget.y = Math.max(b.minY, Math.min(widget.y, b.maxY - widget.h));
}

function positionWidget(widget) {
    clampWidget(widget);                // enforce bounds before every render
    const el = widget.el;
    el.style.left   = widget.x + 'px';
    el.style.top    = widget.y + 'px';
    el.style.width  = widget.w + 'px';
    el.style.height = widget.h + 'px';
}

function removeWidget(id) {
    const idx = state.widgets.findIndex(w => w.id === id);
    if (idx === -1) return;
    state.widgets[idx].el.remove();
    state.widgets.splice(idx, 1);
    if (state.selected === id) state.selected = null;
    deleteWidgetFromServer(id);
    updateDropHint();
}

// ============================================================
//  SELECTION
// ============================================================
function selectWidget(id) {
    state.selected = id;
    state.widgets.forEach(w => {
        w.el.classList.toggle('selected', w.id === id);
        // Raise selected to top
        w.el.style.zIndex = w.id === id ? 100 : w.id;
    });
}

function deselectAll() {
    state.selected = null;
    state.widgets.forEach(w => w.el.classList.remove('selected'));
}

canvasArea.addEventListener('pointerdown', (e) => {
    if (e.target === canvasArea || e.target === surface) deselectAll();
});

// ============================================================
//  DRAG-MOVE WIDGET
// ============================================================
function attachWidgetDrag(el, widget, handle) {
    handle.addEventListener('pointerdown', (e) => {
        if (e.target.closest('.frame-btn') || e.target.closest('input')) return;
        e.stopPropagation();
        selectWidget(widget.id);

        el.classList.add('dragging');

        const startX  = e.clientX, startY = e.clientY;
        const origX   = widget.x,   origY  = widget.y;
        let moved     = false;

        const onMove = (ev) => {
            widget.x = origX + (ev.clientX - startX) / state.zoom;
            widget.y = origY + (ev.clientY - startY) / state.zoom;
            positionWidget(widget);   // positionWidget calls clampWidget internally
            moved = true;
        };
        const onUp = () => {
            el.classList.remove('dragging');
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup',   onUp);
            if (moved) saveWidgetToServer(widget);
        };

        window.addEventListener('pointermove', onMove);
        window.addEventListener('pointerup',   onUp);
    });
}

// ============================================================
//  RESIZE WIDGET (SE corner)
// ============================================================
function attachWidgetResize(el, widget, handle) {
    handle.addEventListener('pointerdown', (e) => {
        e.stopPropagation();
        el.classList.add('resizing');

        const startX = e.clientX, startY = e.clientY;
        const origW  = widget.w,   origH  = widget.h;
        let moved    = false;

        const onMove = (ev) => {
            const b = getCanvasBounds();
            // Compute desired size
            const desiredW = Math.max(280, origW + (ev.clientX - startX) / state.zoom);
            const desiredH = Math.max(180, origH + (ev.clientY - startY) / state.zoom);
            // Cap so right/bottom edge never exceeds canvas bounds
            widget.w = Math.min(desiredW, b.maxX - widget.x);
            widget.h = Math.min(desiredH, b.maxY - widget.y);
            positionWidget(widget);
            moved = true;
        };
        const onUp = () => {
            el.classList.remove('resizing');
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup',   onUp);
            if (moved) saveWidgetToServer(widget);
        };

        window.addEventListener('pointermove', onMove);
        window.addEventListener('pointerup',   onUp);
    });
}

// ============================================================
//  PAN & ZOOM
// ============================================================
let isPanning = false, panOrigin = {};

canvasArea.addEventListener('pointerdown', (e) => {
    // Middle-click or space+drag to pan
    if (e.button === 1 || e.button === 2) {
        isPanning  = true;
        panOrigin  = { x: e.clientX - state.pan.x, y: e.clientY - state.pan.y };
        canvasArea.style.cursor = 'grabbing';
        e.preventDefault();
    }
});

window.addEventListener('pointermove', (e) => {
    if (!isPanning) return;
    state.pan.x = e.clientX - panOrigin.x;
    state.pan.y = e.clientY - panOrigin.y;
    applyTransform();
});

window.addEventListener('pointerup', (e) => {
    if (isPanning && (e.button === 1 || e.button === 2)) {
        isPanning = false;
        canvasArea.style.cursor = '';
    }
});

canvasArea.addEventListener('contextmenu', (e) => e.preventDefault());

canvasArea.addEventListener('wheel', (e) => {
    e.preventDefault();
    const rect   = canvasArea.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;
    const factor = e.deltaY < 0 ? 1.1 : 0.9;
    const newZ   = Math.max(0.2, Math.min(3, state.zoom * factor));

    state.pan.x  = mouseX - (mouseX - state.pan.x) * (newZ / state.zoom);
    state.pan.y  = mouseY - (mouseY - state.pan.y) * (newZ / state.zoom);
    state.zoom   = newZ;
    applyTransform();
}, { passive: false });

function adjustZoom(factor) {
    const r  = canvasArea.getBoundingClientRect();
    const cx = r.width / 2, cy = r.height / 2;
    const nz = Math.max(0.2, Math.min(3, state.zoom * factor));
    state.pan.x = cx - (cx - state.pan.x) * (nz / state.zoom);
    state.pan.y = cy - (cy - state.pan.y) * (nz / state.zoom);
    state.zoom  = nz;
    applyTransform();
}

document.getElementById('btnZoomIn').addEventListener('click',  () => adjustZoom(1.2));
document.getElementById('btnZoomOut').addEventListener('click', () => adjustZoom(1 / 1.2));
document.getElementById('btnReset').addEventListener('click', () => {
    state.pan  = { x: 0, y: 0 };
    state.zoom = 1;
    applyTransform();
});
document.getElementById('btnClearAll').addEventListener('click', () => {
    if (!state.widgets.length) return;
    if (!confirm('Remove all frames from the canvas?')) return;
    [...state.widgets].forEach(w => removeWidget(w.id));
});

document.getElementById('btnTile').addEventListener('click', () => {
    // Only tile widgets that are not minimised
    const visible = state.widgets.filter(w => {
        const body = w.el.querySelector('.frame-body');
        return body && body.style.display !== 'none';
    });
    if (!visible.length) return;

    // Restore any maximised widget first
    visible.forEach(w => {
        if (w.el.classList.contains('maximized')) {
            w.el.querySelector('.frame-btn.maximized-active')?.click();
        }
    });

    // Reset pan/zoom so tiles are immediately visible
    state.pan  = { x: 0, y: 0 };
    state.zoom = 1;
    applyTransform();

    const n    = visible.length;
    const r    = canvasArea.getBoundingClientRect();
    const cols = Math.ceil(Math.sqrt(n));
    const rows = Math.ceil(n / cols);
    const gap  = 10;
    const tileW = Math.floor((r.width  - gap * (cols + 1)) / cols);
    const tileH = Math.floor((r.height - gap * (rows + 1)) / rows);

    visible.forEach((w, i) => {
        const col = i % cols;
        const row = Math.floor(i / cols);
        w.x = gap + col * (tileW + gap);
        w.y = gap + row * (tileH + gap);
        w.w = Math.max(280, tileW);
        w.h = Math.max(180, tileH);
        positionWidget(w);
        saveWidgetToServer(w);
    });

    deselectAll();
});

// ============================================================
//  SIDEBAR DRAG → CANVAS DROP
// ============================================================
let dragData = null;

/**
 * Bind all events onto a palette item element:
 *   - dragstart / dragend for canvas drops
 *   - remove button click (× button, visible on hover)
 *
 * Also removes any active canvas widgets that were spawned
 * from this preset when the item is removed from the sidebar.
 */
function bindPaletteItem(el) {
    const preset = JSON.parse(el.dataset.preset);

    // Drag to canvas
    el.addEventListener('dragstart', (e) => {
        // Don't start a drag when clicking the remove button
        if (e.target.closest('.palette-remove')) { e.preventDefault(); return; }
        dragData = preset;
        e.dataTransfer.effectAllowed = 'copy';
        e.dataTransfer.setData('text/plain', preset.id);
    });
    el.addEventListener('dragend', () => { dragData = null; });

    // Remove button — × icon revealed on hover
    const removeBtn = el.querySelector('.palette-remove');
    if (removeBtn) {
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();   // don't trigger drag or any parent handler

            // Remove all canvas widgets spawned from this preset
            const toRemove = state.widgets.filter(w => w.preset.id === preset.id);
            toRemove.forEach(w => removeWidget(w.id));

            // Animate the palette item out, then delete it
            el.style.transition = 'opacity 0.18s ease, transform 0.18s ease, max-height 0.22s ease, margin 0.22s ease, padding 0.22s ease';
            el.style.opacity    = '0';
            el.style.transform  = 'translateX(-12px)';
            el.style.maxHeight  = el.offsetHeight + 'px';   // start from real height
            // Force a reflow so the transition fires
            void el.offsetHeight;
            el.style.maxHeight  = '0';
            el.style.marginTop  = '0';
            el.style.marginBottom = '0';
            el.style.paddingTop   = '0';
            el.style.paddingBottom = '0';
            el.style.overflow   = 'hidden';

            el.addEventListener('transitionend', () => el.remove(), { once: true });
        });
    }
}

// Bind all PHP-rendered palette items on load
document.querySelectorAll('.palette-item').forEach(bindPaletteItem);

canvasArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    canvasArea.classList.add('drag-over');
});
canvasArea.addEventListener('dragleave', (e) => {
    if (!canvasArea.contains(e.relatedTarget)) canvasArea.classList.remove('drag-over');
});
canvasArea.addEventListener('drop', (e) => {
    e.preventDefault();
    canvasArea.classList.remove('drag-over');

    const preset = dragData || PRESETS.find(p => p.id === e.dataTransfer.getData('text/plain'));
    if (!preset) return;

    const pt = canvasPoint(e.clientX, e.clientY);
    const widget = createWidget(preset, pt.x, pt.y);
    saveWidgetToServer(widget);
});

// ============================================================
//  CUSTOM URL
// ============================================================
document.getElementById('addCustomBtn').addEventListener('click', () => {
    const input = document.getElementById('customUrl');
    let url = input.value.trim();
    if (!url) { input.focus(); return; }
    if (!/^https?:\/\//i.test(url)) url = 'https://' + url;

    const label = (() => { try { return new URL(url).hostname; } catch { return 'Custom'; } })();

    const preset = {
        id: 'custom_' + Date.now(),
        label,
        icon: label[0]?.toUpperCase() || '?',
        url,
        color: '#5A3D96',
        note: '',
        defaultW: 700,
        defaultH: 480,
        tab: 'home',
    };

    // Build palette item with remove button, then bind all events via bindPaletteItem
    const paletteItem = document.createElement('div');
    paletteItem.className      = 'palette-item';
    paletteItem.draggable      = true;
    paletteItem.dataset.tab    = preset.tab;
    paletteItem.dataset.preset = JSON.stringify(preset);
    paletteItem.innerHTML = `
        <div class="palette-icon" style="background:${preset.color}">${preset.icon}</div>
        <div class="palette-info">
            <div class="palette-name">${label}</div>
            <div class="palette-url">${url}</div>
        </div>
        <button class="palette-remove" title="Remove from sidebar" aria-label="Remove ${label} from sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;

    bindPaletteItem(paletteItem);
    document.getElementById('framePalette').appendChild(paletteItem);
    saveFramePresetToServer(preset);

    // Also place a widget on the canvas immediately
    const r  = canvasArea.getBoundingClientRect();
    const pt = canvasPoint(r.left + r.width / 2, r.top + r.height / 2);
    createWidget(preset, pt.x, pt.y);

    input.value = '';
});

document.getElementById('customUrl').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') document.getElementById('addCustomBtn').click();
});

// ============================================================
//  KEYBOARD SHORTCUTS
// ============================================================
document.addEventListener('keydown', (e) => {
    const tag = document.activeElement.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA') return;

    if (e.key === 'Delete' || e.key === 'Backspace') {
        if (state.selected !== null) removeWidget(state.selected);
    }
    if (e.key === 'Escape') {
        // Restore any maximised widget first; if none, deselect
        const maxWidget = state.widgets.find(w => w.el.classList.contains('maximized'));
        if (maxWidget) {
            maxWidget.el.querySelector('.frame-btn.maximized-active')?.click();
        } else {
            deselectAll();
        }
    }
    if (e.key === 'f' || e.key === 'F') {
        if (state.selected !== null) {
            const w = state.widgets.find(ww => ww.id === state.selected);
            if (w) {
                // Find and click the maximize button
                const btns = [...w.el.querySelectorAll('.frame-btn')];
                const maxBtn = btns.find(b => b.title === 'Maximise' || b.title === 'Restore');
                maxBtn?.click();
            }
        }
    }
    if (e.key === '+' || e.key === '=') adjustZoom(1.2);
    if (e.key === '-') adjustZoom(1 / 1.2);
    if (e.key === '0') {
        state.pan  = { x: 0, y: 0 };
        state.zoom = 1;
        applyTransform();
    }
});

// ============================================================
//  SIDEBAR FLYOUT TOGGLE
// ============================================================
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarPanel   = document.getElementById('sidebarPanel');

sidebarToggle?.addEventListener('click', () => {
    const open = sidebarPanel.classList.toggle('open');
    sidebarToggle.classList.toggle('open', open);
    sidebarToggle.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('sidebar-open', open);
    layoutAllMaximizedWidgets();
});

// ============================================================
//  MOBILE NAV — mirrors index.php
// ============================================================
const hamburgerBtn = document.getElementById('hamburgerBtn');
const mobileNav    = document.getElementById('mobileNav');

hamburgerBtn?.addEventListener('click', () => {
    const open = mobileNav.classList.toggle('open');
    hamburgerBtn.setAttribute('aria-expanded', String(open));
});

document.querySelectorAll('.mobile-parent').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const item = link.closest('.mobile-nav-item');
        item.classList.toggle('open');
        item.parentElement.querySelectorAll('.mobile-nav-item').forEach(s => {
            if (s !== item) s.classList.remove('open');
        });
    });
});

document.querySelectorAll('.mobile-submenu a, .mobile-simple').forEach(a => {
    a.addEventListener('click', () => {
        mobileNav.classList.remove('open');
        hamburgerBtn?.setAttribute('aria-expanded', 'false');
    });
});

// ============================================================
//  INIT — pre-place Google frame on load
// ============================================================
(function init() {
    applyTransform();
    updateDropHint();

    const restored = restoreSavedWidgets();

    if (!restored) {
        // Drop a Google frame in the centre to demonstrate (first-time visitors only)
        const googlePreset = PRESETS.find(p => p.id === 'google');
        if (googlePreset) {
            const r  = canvasArea.getBoundingClientRect();
            // Give the layout a tick to settle
            requestAnimationFrame(() => {
                const cx = (r.width  / 2) / state.zoom - state.pan.x / state.zoom;
                const cy = (r.height / 2) / state.zoom - state.pan.y / state.zoom;
                createWidget(googlePreset, cx, cy);
            });
        }
    }
})();
</script>
</body>
</html>