<?php
if(isset($_SERVER['HTTPS'])){
    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
}
else{
    $protocol = 'http';
}
$baseURL = $protocol . "://" . $_SERVER['HTTP_HOST'] . '/backstagedev/';
error_log('Baseurl=' . $baseURL);

$navItems = [
    ['id' => 'nav-home',       'label' => 'Home',       'href' => $baseURL . 'index.php',    'children' => []],
    ['id' => 'nav-projects',   'label' => 'Projects',   'href' => $baseURL . 'projects.php', 'children' => []],
    ['id' => 'nav-scheduling', 'label' => 'Scheduling', 'href' => $baseURL . 'bs20-calendar/public/index.php',  'children' => []],
    ['id' => 'nav-reports',    'label' => 'Reports',    'href' => '#reports',     'children' => []],
    ['id' => 'nav-admin',      'label' => 'Admin',      'href' => '#admin',       'children' => [
        ['id' => 'nav-admin-sites', 'label' => 'Manage Sites', 'href' => $baseURL . 'sites.php'],
        ['id' => 'nav-admin-users', 'label' => 'Manage Users', 'href' => $baseURL . 'users.php'],
        ['id' => 'nav-admin-email', 'label' => 'Send Emails',  'href' => $baseURL . 'send_email.php'],
        ['id' => 'nav-admin-sms',   'label' => 'Send Texts',   'href' => $baseURL . 'send_sms.php'],
    ]],
    ['id' => 'nav-ziflow',     'label' => 'Ziflow',     'href' => '#ziflow',      'children' => []],
    [
        'id'       => 'nav-contact',
        'label'    => 'Contact',
        'href'     => '#contact',
        'children' => [
            ['id' => 'nav-contact-support', 'label' => 'Support', 'href' => '#support'],
        ],
    ],
    [
        'id'       => 'nav-about',
        'label'    => 'About',
        'href'     => '#about',
        'children' => [
            ['id' => 'nav-about-mission',  'label' => 'Mission', 'href' => '#mission'],
            ['id' => 'nav-about-vision',   'label' => 'Vision',  'href' => '#vision'],
            ['id' => 'nav-about-founders', 'label' => 'Staff',   'href' => '#staff'],
        ],
    ],
];


/* =====================================================
   SIDE FLYOUT NAVIGATION
   Call renderNavFlyout($navItems, $siteName) once per page
   (header.php does this). Any element with
   data-nav-flyout-toggle opens/closes the panel.
===================================================== */

/** True when $href points at the page currently being viewed. */
function navIsCurrent(string $href): bool
{
    if ($href === '' || $href[0] === '#') {
        return false;
    }
    $linkPath    = rtrim((string) parse_url($href, PHP_URL_PATH), '/');
    $currentPath = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    return $linkPath !== '' && $linkPath === $currentPath;
}

function navOnclick(string $id): string
{
    $safeId = htmlspecialchars($id, ENT_QUOTES);
    return "if (window.handleNavClick) handleNavClick(event, '{$safeId}')";
}

function renderNavFlyout(array $navItems, string $siteName = 'Menu'): void
{
    $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
    ?>
<style>
    /* Hamburger is the only nav trigger now, so show it at every width */
    .hamburger { display: flex !important; }
    .banner-nav-row { display: none !important; }

    .nav-flyout-backdrop {
        position: fixed; inset: 0;
        background: rgba(30, 12, 60, 0.45);
        backdrop-filter: blur(2px);
        opacity: 0; visibility: hidden;
        transition: opacity 0.25s, visibility 0.25s;
        z-index: 2999;
    }
    .nav-flyout-backdrop.open { opacity: 1; visibility: visible; }

    .nav-flyout {
        position: fixed; top: 0; left: 0; bottom: 0;
        width: 300px; max-width: 85vw;
        background: linear-gradient(180deg, #6644AA 0%, #5A3D96 100%);
        box-shadow: 6px 0 30px rgba(40, 15, 80, 0.45);
        transform: translateX(-100%);
        transition: transform 0.28s cubic-bezier(.4, 0, .2, 1);
        z-index: 3000;
        display: flex; flex-direction: column;
        font-family: 'DM Sans', sans-serif;
    }
    .nav-flyout.open { transform: translateX(0); }

    .nav-flyout-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 20px 18px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.14);
    }
    .nav-flyout-title {
        font-family: 'Playfair Display', serif;
        font-size: 20px; color: #fff; font-weight: 600;
    }
    .nav-flyout-close {
        background: none; border: 1.5px solid rgba(255,255,255,0.35);
        border-radius: 8px; color: #fff; cursor: pointer;
        width: 34px; height: 34px;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.2s;
    }
    .nav-flyout-close:hover { background: rgba(255,255,255,0.15); }
    .nav-flyout-close svg { width: 18px; height: 18px; }

    .nav-flyout-body { flex: 1; overflow-y: auto; padding: 10px 0 24px; }

    .nav-flyout a,
    .nav-flyout-parent {
        display: flex; align-items: center; justify-content: space-between;
        width: 100%;
        padding: 13px 24px;
        color: rgba(255,255,255,0.9);
        text-decoration: none;
        font: 500 15px 'DM Sans', sans-serif;
        background: none; border: none; border-left: 3px solid transparent;
        text-align: left; cursor: pointer;
        transition: background 0.15s, color 0.15s, border-color 0.15s;
    }
    .nav-flyout a:hover,
    .nav-flyout-parent:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .nav-flyout a.active {
        background: rgba(255,255,255,0.16); color: #fff;
        border-left-color: rgba(255,255,255,0.85);
    }

    .nav-flyout-parent .chevron {
        width: 14px; height: 14px; opacity: 0.75;
        transition: transform 0.2s;
    }
    .nav-flyout-group.open > .nav-flyout-parent .chevron { transform: rotate(180deg); }

    .nav-flyout-sub {
        display: none;
        background: rgba(0,0,0,0.14);
    }
    .nav-flyout-group.open > .nav-flyout-sub { display: block; }
    .nav-flyout-sub a {
        padding: 11px 24px 11px 42px;
        font-size: 14px; font-weight: 400;
        color: rgba(255,255,255,0.8);
    }

    body.nav-flyout-locked { overflow: hidden; }
</style>

<div class="nav-flyout-backdrop" id="navFlyoutBackdrop"></div>

<aside class="nav-flyout" id="navFlyout" aria-label="Main navigation" aria-hidden="true">
    <div class="nav-flyout-head">
        <span class="nav-flyout-title"><?= $e($siteName) ?></span>
        <button type="button" class="nav-flyout-close" id="navFlyoutClose" aria-label="Close menu">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="nav-flyout-body">
        <?php foreach ($navItems as $item): ?>
            <?php if (!empty($item['children'])): ?>
                <?php
                    $groupActive = false;
                    foreach ($item['children'] as $c) {
                        if (navIsCurrent($c['href'])) { $groupActive = true; break; }
                    }
                    $subId = $e($item['id']) . '-sub';
                ?>
                <div class="nav-flyout-group<?= $groupActive ? ' open' : '' ?>">
                    <button type="button" class="nav-flyout-parent"
                            id="<?= $e($item['id']) ?>"
                            data-nav-id="<?= $e($item['id']) ?>"
                            aria-expanded="<?= $groupActive ? 'true' : 'false' ?>"
                            aria-controls="<?= $subId ?>">
                        <?= $e($item['label']) ?>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="nav-flyout-sub" id="<?= $subId ?>">
                        <?php foreach ($item['children'] as $child): ?>
                            <a id="<?= $e($child['id']) ?>"
                               href="<?= $e($child['href']) ?>"
                               data-nav-id="<?= $e($child['id']) ?>"
                               onclick="<?= navOnclick($child['id']) ?>"
                               <?= navIsCurrent($child['href']) ? 'class="active" aria-current="page"' : '' ?>>
                                <?= $e($child['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <a id="<?= $e($item['id']) ?>"
                   href="<?= $e($item['href']) ?>"
                   data-nav-id="<?= $e($item['id']) ?>"
                   onclick="<?= navOnclick($item['id']) ?>"
                   <?= navIsCurrent($item['href']) ? 'class="active" aria-current="page"' : '' ?>>
                    <?= $e($item['label']) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</aside>

<script>
(function () {
    const panel    = document.getElementById('navFlyout');
    const backdrop = document.getElementById('navFlyoutBackdrop');
    const closeBtn = document.getElementById('navFlyoutClose');
    const toggles  = document.querySelectorAll('[data-nav-flyout-toggle]');
    let lastFocus  = null;

    function setOpen(open) {
        panel.classList.toggle('open', open);
        backdrop.classList.toggle('open', open);
        panel.setAttribute('aria-hidden', String(!open));
        document.body.classList.toggle('nav-flyout-locked', open);
        toggles.forEach(t => t.setAttribute('aria-expanded', String(open)));

        if (open) {
            lastFocus = document.activeElement;
            closeBtn.focus();
        } else if (lastFocus) {
            lastFocus.focus();
        }
    }

    toggles.forEach(t => t.addEventListener('click', e => {
        e.preventDefault();
        setOpen(!panel.classList.contains('open'));
    }));
    closeBtn.addEventListener('click', () => setOpen(false));
    backdrop.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && panel.classList.contains('open')) setOpen(false);
    });

    // Accordion for items with children (one open at a time)
    panel.querySelectorAll('.nav-flyout-parent').forEach(btn => {
        btn.addEventListener('click', () => {
            const group  = btn.closest('.nav-flyout-group');
            const isOpen = group.classList.toggle('open');
            btn.setAttribute('aria-expanded', String(isOpen));
            panel.querySelectorAll('.nav-flyout-group').forEach(g => {
                if (g !== group) {
                    g.classList.remove('open');
                    g.querySelector('.nav-flyout-parent').setAttribute('aria-expanded', 'false');
                }
            });
        });
    });

    // Close the panel when a link is followed
    panel.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setOpen(false)));
})();
</script>
    <?php
}
