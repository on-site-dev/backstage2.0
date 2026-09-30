<?php

?>
<!-- =====================================================
     TOP BANNER
     Row 1: hamburger · logo · site name ........ login · avatar
     Row 2: search
===================================================== -->
<style>
    /* Banner grows to fit both rows; --banner-height is synced to the real
       height by the script below so page content starts right beneath it. */
    header.top-banner { height: auto !important; }

    header.top-banner .banner-top-row {
        flex: none;
        min-height: 72px;
        padding-top: 10px;
        padding-bottom: 6px;
        justify-content: space-between;
    }

    .banner-search-row {
        display: flex;
        justify-content: center;
        padding: 0 24px 14px;
    }
    .banner-search-row .search-bar {
        width: 100%;
        max-width: 560px;
    }
</style>

<header class="top-banner" role="banner">

    <div class="banner-top-row">
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
            <button class="hamburger" id="navFlyoutToggle" data-nav-flyout-toggle
                    aria-label="Open navigation" aria-controls="navFlyout" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="index.php" class="logo" aria-label="<?= htmlspecialchars($siteName) ?> Home">
                <img src="images/ONSITE-LOGO-New-Web-Small-White-300x139-1.png"
                     alt="<?= htmlspecialchars($siteName) ?> Logo"
                     class="logo-img" />
            </a>
            <span class="logo-site-label"><?= htmlspecialchars($siteName) ?></span>
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
        </div>
    </div>

    <div class="banner-search-row">
        <form class="search-bar" role="search" action="#" method="get">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="search" name="q" placeholder="Search anything…" aria-label="Search" autocomplete="off"/>
        </form>
    </div>

</header>

<script>
// Keep --banner-height equal to the banner's real height so page content
// (which uses it for its top offset) always starts just below the banner.
(function () {
    var header = document.querySelector('header.top-banner');
    if (!header) return;
    function sync() {
        document.documentElement.style.setProperty('--banner-height', header.offsetHeight + 'px');
    }
    sync();
    window.addEventListener('load', sync);
    window.addEventListener('resize', sync);
    if (window.ResizeObserver) new ResizeObserver(sync).observe(header);
})();
</script>

<?php renderNavFlyout($navItems, $siteName); ?>
