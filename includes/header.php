<?php 

?>
<!-- =====================================================
     TOP BANNER (mirrors index.php exactly)
===================================================== -->
<header class="top-banner" role="banner">

    <div class="banner-top-row">
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
            <a href="index.php" class="logo" aria-label="<?= htmlspecialchars($siteName) ?> Home">
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
                <span><?php echo($loggedInStatusButton); ?></span>
            </a>
            <div class="avatar-wrap" role="button" tabindex="0" aria-label="User profile">
                <div class="avatar">A</div>
                <span class="avatar-status" aria-label="Online"></span>
            </div>
            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="banner-nav-row">
        <nav class="banner-nav" aria-label="Main navigation">
            <?php foreach ($navItems as $index => $item): ?>
                <?php $hasChildren = !empty($item['children']); ?>
                <div class="nav-item">
                    <a id="<?= htmlspecialchars($item['id']) ?>"
                       href="<?= htmlspecialchars($item['href']) ?>"
                       data-nav-id="<?= htmlspecialchars($item['id']) ?>"
                       onclick="handleNavClick(event, '<?= htmlspecialchars($item['id']) ?>')"
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
                                <a id="<?= htmlspecialchars($child['id']) ?>"
                                   href="<?= htmlspecialchars($child['href']) ?>"
                                   data-nav-id="<?= htmlspecialchars($child['id']) ?>"
                                   onclick="handleNavClick(event, '<?= htmlspecialchars($child['id']) ?>')"
                                   role="menuitem">
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
