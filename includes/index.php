<?php
declare(strict_types=1);

$siteName    = "Backstage 2.0";
$studioName  = "On-Site Studios";
$currentYear = date('Y');
$pageTitle   = "Welcome to " . htmlspecialchars($siteName);

// Nav structure: label, href, optional children array
$navItems = [
    [
        'label'    => 'Home',
        'href'     => '#',
        'children' => [],
    ],
    [
        'label'    => 'Projects',
        'href'     => '#projects',
        'children' => [],
    ],
    [
        'label'    => 'Scheduling',
        'href'     => '#scheduling',
        'children' => [],
    ],
    [
        'label'    => 'Reports',
        'href'     => '#reports',
        'children' => [],
    ],
    [
        'label'    => 'Admin',
        'href'     => '#admin',
        'children' => [],
    ],
    [
        'label'    => 'Ziflow',
        'href'     => '#ziflow',
        'children' => [],
    ],
    [
        'label' => 'Contact',
        'href'  => '#contact',
        'children' => [
            ['label' => 'Support', 'href' => '#support'],
        ],
    ],
    [
        'label' => 'About',
        'href'  => '#about',
        'children' => [
            ['label' => 'Mission',  'href' => '#mission'],
            ['label' => 'Vision',   'href' => '#vision'],
            ['label' => 'Founders', 'href' => '#founders'],
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --purple: #7A55C7;
            --purple-dark: #6644AA;
            --purple-deeper: #5A3D96;
            --text-light: #f0eaff;
            --text-muted: #d8cef5;
            --banner-height: 150px;
            --footer-height: 60px;
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background-color: #fff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
        }

        /* ===== TOP BANNER ===== */
        header.top-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--banner-height);
            background: linear-gradient(135deg, #6644AA 0%, #7A55C7 50%, #9060D4 100%);
            box-shadow: 0 4px 24px rgba(60, 30, 100, 0.35);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        /* Top row */
        .banner-top-row {
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 14px;
            flex: 1;
        }

        /* Nav row */
        .banner-nav-row {
            display: flex;
            align-items: stretch;
            padding: 0 20px;
            height: 44px;
            border-top: 1px solid rgba(255,255,255,0.12);
            background: rgba(0,0,0,0.08);
            overflow: visible;
        }

        /* ===== LOGO ===== */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .logo-img {
            height: 52px;
            width: auto;
            display: block;
            transition: opacity 0.2s, transform 0.2s;
        }

        .logo:hover .logo-img {
            opacity: 0.88;
            transform: scale(1.03);
        }

        .logo-site-label {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 600;
            color: #ffffff;
            letter-spacing: 0.4px;
            white-space: nowrap;
            text-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        /* ===== SEARCH ===== */
        .search-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 50px;
            padding: 0 18px;
            width: 100%;
            max-width: 420px;
            height: 44px;
            transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .search-bar:focus-within {
            background: #ffffff;
            border-color: rgba(255,255,255,0.9);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }

        .search-bar svg {
            width: 17px;
            height: 17px;
            color: #7A55C7;
            flex-shrink: 0;
            margin-right: 10px;
        }

        .search-bar input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #3a2a5a;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 400;
        }

        .search-bar input::placeholder {
            color: #b09ed4;
        }

        /* ===== BANNER RIGHT ===== */
        .banner-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.4);
            border-radius: 50px;
            padding: 0 18px;
            height: 38px;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.3px;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, transform 0.15s;
            white-space: nowrap;
            backdrop-filter: blur(6px);
        }

        .btn-login:hover {
            background: rgba(255,255,255,0.28);
            border-color: rgba(255,255,255,0.65);
            transform: translateY(-1px);
        }

        .btn-login svg {
            width: 15px;
            height: 15px;
        }

        .avatar-wrap {
            flex-shrink: 0;
            cursor: pointer;
            position: relative;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0c8ff, #c09aff);
            border: 2px solid rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 17px;
            color: #5a3fa0;
            font-weight: 700;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }

        .avatar:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 18px rgba(0,0,0,0.3);
        }

        .avatar-status {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 10px;
            height: 10px;
            background: #4ade80;
            border-radius: 50%;
            border: 2px solid var(--purple);
        }

        /* ===== DESKTOP NAV WITH DROPDOWNS ===== */
        nav.banner-nav {
            display: flex;
            align-items: stretch;
            gap: 2px;
            height: 100%;
        }

        /* Each top-level nav item wrapper */
        .nav-item {
            position: relative;
            display: flex;
            align-items: stretch;
        }

        /* Top-level link */
        .nav-item > a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 0 18px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.3px;
            border-radius: 6px 6px 0 0;
            transition: background 0.18s, color 0.18s;
            white-space: nowrap;
        }

        .nav-item > a:hover,
        .nav-item:hover > a {
            background: rgba(255,255,255,0.14);
            color: #fff;
        }

        .nav-item > a.active {
            background: rgba(255,255,255,0.18);
            color: #fff;
            font-weight: 500;
            border-bottom: 2px solid rgba(255,255,255,0.75);
            border-radius: 6px 6px 0 0;
        }

        /* Chevron icon for items with children */
        .nav-item > a .chevron {
            width: 12px;
            height: 12px;
            transition: transform 0.2s;
            opacity: 0.75;
        }

        .nav-item:hover > a .chevron {
            transform: rotate(180deg);
            opacity: 1;
        }

        /* ===== DROPDOWN PANEL ===== */
        .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 160px;
            background: #5a3fa0;
            border-radius: 0 8px 8px 8px;
            box-shadow: 0 8px 28px rgba(40, 15, 80, 0.45);
            overflow: hidden;
            z-index: 2000;
            animation: dropIn 0.16s ease;
        }

        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .nav-item:hover .dropdown {
            display: block;
        }

        .dropdown a {
            display: flex;
            align-items: center;
            padding: 11px 18px;
            color: rgba(255,255,255,0.88);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 400;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }

        .dropdown a:last-child {
            border-bottom: none;
        }

        .dropdown a:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            padding-left: 24px;
        }

        /* ===== HAMBURGER (mobile) ===== */
        .hamburger {
            display: none;
            background: none;
            border: 1.5px solid rgba(255,255,255,0.35);
            border-radius: 8px;
            cursor: pointer;
            padding: 6px 8px;
            color: #fff;
            flex-shrink: 0;
            transition: background 0.2s;
        }

        .hamburger:hover {
            background: rgba(255,255,255,0.15);
        }

        .hamburger svg {
            width: 20px;
            height: 20px;
            display: block;
        }

        /* ===== MOBILE NAV ===== */
        .mobile-nav {
            display: none;
            position: fixed;
            top: var(--banner-height);
            left: 0;
            right: 0;
            background: #5A3D96;
            z-index: 999;
            flex-direction: column;
            border-bottom: 2px solid rgba(255,255,255,0.15);
            box-shadow: 0 8px 20px rgba(60,20,100,0.3);
            max-height: calc(100vh - var(--banner-height));
            overflow-y: auto;
        }

        .mobile-nav.open { display: flex; }

        /* Top-level mobile links */
        .mobile-nav-item > a.mobile-parent {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 28px;
            color: rgba(255,255,255,0.95);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: background 0.15s;
            cursor: pointer;
        }

        .mobile-nav-item > a.mobile-parent:hover {
            background: rgba(255,255,255,0.1);
        }

        .mobile-nav-item > a.mobile-parent .m-chevron {
            width: 14px;
            height: 14px;
            transition: transform 0.2s;
            opacity: 0.7;
        }

        .mobile-nav-item.open > a.mobile-parent .m-chevron {
            transform: rotate(180deg);
        }

        /* Simple mobile link (no children) */
        .mobile-nav-item > a.mobile-simple {
            display: block;
            padding: 15px 28px;
            color: rgba(255,255,255,0.95);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: background 0.15s;
        }

        .mobile-nav-item > a.mobile-simple:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Mobile sub-menu */
        .mobile-submenu {
            display: none;
            flex-direction: column;
            background: rgba(0,0,0,0.12);
        }

        .mobile-nav-item.open .mobile-submenu {
            display: flex;
        }

        .mobile-submenu a {
            display: block;
            padding: 12px 28px 12px 44px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
            font-weight: 300;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            transition: background 0.15s, color 0.15s;
        }

        .mobile-submenu a:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        /* ===== MAIN ===== */
        main {
            flex: 1;
            margin-top: var(--banner-height);
            margin-bottom: var(--footer-height);
            padding: 52px 32px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 28px;
        }

        .hero-card {
            background: #faf8ff;
            border: 1px solid #e6d9ff;
            border-radius: 20px;
            padding: 44px 52px;
            max-width: 580px;
            width: 100%;
            text-align: center;
            box-shadow: 0 6px 28px rgba(147, 112, 219, 0.12);
        }

        .hero-card h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(26px, 5vw, 38px);
            color: var(--purple-deeper);
            margin-bottom: 14px;
            line-height: 1.2;
        }

        .hero-card p {
            font-size: 15px;
            color: #6b6080;
            line-height: 1.75;
            font-weight: 300;
        }

        .decorative-pill {
            display: inline-block;
            background: #f0e8ff;
            border: 1px solid #d9c5ff;
            border-radius: 50px;
            padding: 6px 18px;
            font-size: 11px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--purple);
            margin-bottom: 18px;
            font-weight: 500;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 700px) {
            :root { --banner-height: 80px; }
            .banner-nav-row { display: none; }
            .hamburger { display: flex; }
        }

        @media (max-width: 440px) {
            .btn-login span { display: none; }
            .btn-login { padding: 0 12px; min-width: 38px; justify-content: center; }
            .hero-card { padding: 28px 18px; }
        }

        /* ===== FOOTER ===== */
        footer.bottom-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--footer-height);
            background: linear-gradient(135deg, #6644AA 0%, #7A55C7 100%);
            border-top: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 -4px 20px rgba(60, 20, 100, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        footer.bottom-banner p {
            font-size: 13px;
            color: rgba(255,255,255,0.65);
            letter-spacing: 0.4px;
            text-align: center;
            font-weight: 300;
        }

        footer.bottom-banner p span {
            color: rgba(255,255,255,0.92);
            font-weight: 500;
        }
    </style>
</head>
<body>

    <!-- TOP BANNER -->
    <header class="top-banner" role="banner">

        <!-- Top Row -->
        <div class="banner-top-row">

            <div class="logo-group">
                <a href="#" class="logo" aria-label="<?= htmlspecialchars($siteName) ?> Home">
                    <img src="images/ONSITE-LOGO-New-Web-Small-White-300x139-1.png"
                         alt="<?= htmlspecialchars($siteName) ?> Logo"
                         class="logo-img" />
                </a>
                <span class="logo-site-label"><?= htmlspecialchars($siteName) ?></span>
            </div>

            <div class="search-wrapper">
                <form class="search-bar" role="search" action="#" method="get">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input type="search" name="q" placeholder="Search anything…" aria-label="Search" autocomplete="off"/>
                </form>
            </div>

            <div class="banner-right">
                <a href="login.php" class="btn-login" aria-label="Log in">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h7a2 2 0 012 2v1"/>
                    </svg>
                    <span>Log In</span>
                </a>

                <div class="avatar-wrap" role="button" tabindex="0" aria-label="User profile">
                    <div class="avatar">A</div>
                    <span class="avatar-status" aria-label="Online"></span>
                </div>
            </div>

            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Desktop Nav Row -->
        <div class="banner-nav-row">
            <nav class="banner-nav" aria-label="Main navigation">
                <?php foreach ($navItems as $index => $item): ?>
                    <?php $hasChildren = !empty($item['children']); ?>
                    <div class="nav-item">
                        <a href="<?= htmlspecialchars($item['href']) ?>"
                           <?= $index === 0 ? 'class="active"' : '' ?>
                           <?= $hasChildren ? 'aria-haspopup="true"' : '' ?>>
                            <?= htmlspecialchars($item['label']) ?>
                            <?php if ($hasChildren): ?>
                                <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            <?php endif; ?>
                        </a>
                        <?php if ($hasChildren): ?>
                            <div class="dropdown" role="menu">
                                <?php foreach ($item['children'] as $child): ?>
                                    <a href="<?= htmlspecialchars($child['href']) ?>" role="menuitem">
                                        <?= htmlspecialchars($child['label']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
        </div>

    </header>

    <!-- Mobile Nav -->
    <nav class="mobile-nav" id="mobileNav" aria-label="Mobile navigation">
        <?php foreach ($navItems as $item): ?>
            <?php $hasChildren = !empty($item['children']); ?>
            <div class="mobile-nav-item">
                <?php if ($hasChildren): ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="mobile-parent">
                        <?= htmlspecialchars($item['label']) ?>
                        <svg class="m-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                    <div class="mobile-submenu">
                        <?php foreach ($item['children'] as $child): ?>
                            <a href="<?= htmlspecialchars($child['href']) ?>"><?= htmlspecialchars($child['label']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="mobile-simple">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- MAIN CONTENT -->
    <main>
        <div class="hero-card">
            <div class="decorative-pill">Welcome</div>
            <h1>Hello, <?= htmlspecialchars($siteName) ?></h1>
            <p>Your beautifully crafted interface is ready. Edit this page to add your own content, components, and features.</p>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bottom-banner" role="contentinfo">
        <p>&copy; <?= $currentYear ?> <span><?= htmlspecialchars($studioName) ?></span>. All rights reserved.</p>
    </footer>

    <script>
        // Hamburger toggle
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileNav    = document.getElementById('mobileNav');

        hamburgerBtn?.addEventListener('click', () => {
            const open = mobileNav.classList.toggle('open');
            hamburgerBtn.setAttribute('aria-expanded', String(open));
        });

        // Mobile accordion: toggle submenus
        document.querySelectorAll('.mobile-parent').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const item = link.closest('.mobile-nav-item');
                const isOpen = item.classList.toggle('open');
                // Close siblings
                item.parentElement.querySelectorAll('.mobile-nav-item').forEach(sibling => {
                    if (sibling !== item) sibling.classList.remove('open');
                });
            });
        });

        // Close mobile nav when a leaf link is clicked
        document.querySelectorAll('.mobile-submenu a, .mobile-simple').forEach(a => {
            a.addEventListener('click', () => {
                mobileNav.classList.remove('open');
                hamburgerBtn?.setAttribute('aria-expanded', 'false');
            });
        });
    </script>

</body>
</html>