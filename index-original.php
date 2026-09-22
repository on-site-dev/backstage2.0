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
    <link rel="stylesheet" href="css/bs20styles.css">
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