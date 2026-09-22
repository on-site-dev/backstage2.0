<?php
declare(strict_types=1);

$siteName    = "Backstage 2.0";
$studioName  = "On-Site Studios";
$currentYear = date('Y');
$pageTitle   = "Dashboard — " . htmlspecialchars($siteName);

$navItems = [
    ['id' => 'nav-home',       'label' => 'Home',       'href' => 'index.php', 'children' => []],
    ['id' => 'nav-projects',   'label' => 'Projects',   'href' => '#projects',  'children' => []],
    ['id' => 'nav-scheduling', 'label' => 'Scheduling', 'href' => '#scheduling','children' => []],
    ['id' => 'nav-reports',    'label' => 'Reports',    'href' => '#reports',   'children' => []],
    ['id' => 'nav-admin',      'label' => 'Admin',      'href' => '#admin',     'children' => [
        ['id' => 'nav-admin-sites', 'label' => 'Sites', 'href' => 'sites.php'],
    ]],
    ['id' => 'nav-ziflow',     'label' => 'Ziflow',     'href' => '#ziflow',    'children' => []],
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
            ['id' => 'nav-about-mission',  'label' => 'Mission',  'href' => '#mission'],
            ['id' => 'nav-about-vision',   'label' => 'Vision',   'href' => '#vision'],
            ['id' => 'nav-about-founders', 'label' => 'Founders', 'href' => '#founders'],
        ],
    ],
];

/**
 * Available frame presets that users can drag onto the canvas.
 * Google and most large sites block embedding via X-Frame-Options.
 * We include a note in the UI and provide embeddable alternatives too.
 */
$framePresets = [
    [
        'id'    => 'google',
        'label' => 'Google',
        'icon'  => 'G',
        'url'   => 'https://www.google.com',
        'color' => '#4285F4',
        'note'  => 'Google blocks embedding (X-Frame-Options). A proxy is required in production.',
        'defaultW' => 800,
        'defaultH' => 540,
    ],
    [
        'id'    => 'wikipedia',
        'label' => 'Wikipedia',
        'icon'  => 'W',
        'url'   => 'https://en.wikipedia.org',
        'color' => '#000000',
        'note'  => '',
        'defaultW' => 800,
        'defaultH' => 540,
    ],
    [
        'id'    => 'openstreetmap',
        'label' => 'OpenStreetMap',
        'icon'  => '⊕',
        'url'   => 'https://www.openstreetmap.org/export/embed.html?bbox=-0.12,51.49,-0.10,51.51&layer=mapnik',
        'color' => '#7EBC6F',
        'note'  => '',
        'defaultW' => 700,
        'defaultH' => 480,
    ],
    [
        'id'    => 'metoffice',
        'label' => 'Weather',
        'icon'  => '☁',
        'url'   => 'https://forecast.weather.gov/MapClick.php?CityName=New+York&state=NY&site=OKX&textField1=40.7128&textField2=-74.0059',
        'color' => '#5B9BD5',
        'note'  => '',
        'defaultW' => 700,
        'defaultH' => 480,
    ],
    [
        'id'    => 'duckduckgo',
        'label' => 'DuckDuckGo',
        'icon'  => '🦆',
        'url'   => 'https://duckduckgo.com',
        'color' => '#DE5833',
        'note'  => 'DuckDuckGo may restrict embedding.',
        'defaultW' => 800,
        'defaultH' => 540,
    ],
    [
        'id'    => 'blank',
        'label' => 'Blank Frame',
        'icon'  => '□',
        'url'   => 'about:blank',
        'color' => '#9370DB',
        'note'  => '',
        'defaultW' => 600,
        'defaultH' => 400,
    ],
    [
        'id'    => 'custom',
        'label' => 'Custom URL',
        'icon'  => '+',
        'url'   => '',
        'color' => '#6B4FA8',
        'note'  => '',
        'defaultW' => 700,
        'defaultH' => 480,
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
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple:        #9370DB;
            --purple-dark:   #7B5EBF;
            --purple-deeper: #6B4FA8;
            --text-light:    #f0eaff;
            --text-muted:    #d8cef5;
            --banner-height: 150px;
            --footer-height: 60px;
            --sidebar-width: 220px;
            --canvas-bg:     #eeeaf6;
        }

        html, body { height: 100%; font-family: 'DM Sans', sans-serif; }

        body {
            background: var(--canvas-bg);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
            overflow: hidden;
        }

        /* =====================================================
           TOP BANNER — exact copy from index.php
        ===================================================== */
        header.top-banner {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--banner-height);
            background: linear-gradient(135deg, #7B5EBF 0%, #9370DB 50%, #A880E8 100%);
            box-shadow: 0 4px 24px rgba(60, 30, 100, 0.35);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        .banner-top-row {
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 14px;
            flex: 1;
        }

        .banner-nav-row {
            display: flex;
            align-items: stretch;
            padding: 0 20px;
            height: 44px;
            border-top: 1px solid rgba(255,255,255,0.12);
            background: rgba(0,0,0,0.08);
            overflow: visible;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .logo-img {
            height: 52px; width: auto; display: block;
            transition: opacity 0.2s, transform 0.2s;
        }
        .logo:hover .logo-img { opacity: 0.88; transform: scale(1.03); }
        .logo-site-label {
            font-family: 'Playfair Display', serif;
            font-size: 24px; font-weight: 600; color: #ffffff;
            letter-spacing: 0.4px; white-space: nowrap;
            text-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        .search-wrapper { flex: 1; display: flex; justify-content: center; }
        .search-bar {
            display: flex; align-items: center;
            background: #ffffff;
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 50px;
            padding: 0 18px; width: 100%; max-width: 420px; height: 44px;
            transition: box-shadow 0.2s;
        }
        .search-bar:focus-within { box-shadow: 0 0 0 3px rgba(255,255,255,0.3); }
        .search-bar svg { width: 17px; height: 17px; color: #9370DB; flex-shrink: 0; margin-right: 10px; }
        .search-bar input {
            flex: 1; background: transparent; border: none; outline: none;
            color: #3a2a5a; font-family: 'DM Sans', sans-serif; font-size: 14px;
        }
        .search-bar input::placeholder { color: #b09ed4; }

        .banner-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .btn-login {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.4);
            border-radius: 50px; padding: 0 18px; height: 38px;
            color: #fff; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500;
            letter-spacing: 0.3px; text-decoration: none; cursor: pointer;
            transition: background 0.2s, transform 0.15s; white-space: nowrap;
            backdrop-filter: blur(6px);
        }
        .btn-login:hover { background: rgba(255,255,255,0.28); transform: translateY(-1px); }
        .btn-login svg { width: 15px; height: 15px; }

        .avatar-wrap { flex-shrink: 0; cursor: pointer; position: relative; }
        .avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg, #e0c8ff, #c09aff);
            border: 2px solid rgba(255,255,255,0.4);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif; font-size: 17px; color: #5a3fa0; font-weight: 700;
            transition: transform 0.2s; box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }
        .avatar:hover { transform: scale(1.08); }
        .avatar-status {
            position: absolute; bottom: 1px; right: 1px;
            width: 10px; height: 10px; background: #4ade80;
            border-radius: 50%; border: 2px solid var(--purple);
        }

        nav.banner-nav { display: flex; align-items: stretch; gap: 2px; height: 100%; }
        .nav-item { position: relative; display: flex; align-items: stretch; }
        .nav-item > a {
            display: inline-flex; align-items: center; gap: 5px; padding: 0 18px;
            color: rgba(255,255,255,0.85); text-decoration: none;
            font-size: 14px; font-weight: 400; letter-spacing: 0.3px;
            border-radius: 6px 6px 0 0;
            transition: background 0.18s, color 0.18s; white-space: nowrap;
        }
        .nav-item > a:hover, .nav-item:hover > a { background: rgba(255,255,255,0.14); color: #fff; }
        .nav-item > a.active {
            background: rgba(255,255,255,0.18); color: #fff; font-weight: 500;
            border-bottom: 2px solid rgba(255,255,255,0.75);
        }
        .chevron { width: 12px; height: 12px; transition: transform 0.2s; opacity: 0.75; }
        .nav-item:hover > a .chevron { transform: rotate(180deg); opacity: 1; }

        .dropdown {
            display: none; position: absolute; top: 100%; left: 0;
            min-width: 160px; background: #5a3fa0;
            border-radius: 0 8px 8px 8px;
            box-shadow: 0 8px 28px rgba(40,15,80,0.45);
            overflow: hidden; z-index: 2000;
            animation: dropIn 0.16s ease;
        }
        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .nav-item:hover .dropdown { display: block; }
        .dropdown a {
            display: flex; align-items: center; padding: 11px 18px;
            color: rgba(255,255,255,0.88); text-decoration: none;
            font-size: 13.5px; font-weight: 400;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            transition: background 0.15s, color 0.15s; white-space: nowrap;
        }
        .dropdown a:last-child { border-bottom: none; }
        .dropdown a:hover { background: rgba(255,255,255,0.15); color: #fff; padding-left: 24px; }

        .hamburger {
            display: none; background: none; border: 1.5px solid rgba(255,255,255,0.35);
            border-radius: 8px; cursor: pointer; padding: 6px 8px; color: #fff;
            flex-shrink: 0; transition: background 0.2s;
        }
        .hamburger:hover { background: rgba(255,255,255,0.15); }
        .hamburger svg { width: 20px; height: 20px; display: block; }

        .mobile-nav {
            display: none; position: fixed; top: var(--banner-height); left: 0; right: 0;
            background: #6B4FA8; z-index: 999; flex-direction: column;
            border-bottom: 2px solid rgba(255,255,255,0.15);
            box-shadow: 0 8px 20px rgba(60,20,100,0.3);
            max-height: calc(100vh - var(--banner-height)); overflow-y: auto;
        }
        .mobile-nav.open { display: flex; }
        .mobile-nav-item > a.mobile-parent {
            display: flex; align-items: center; justify-content: space-between;
            padding: 15px 28px; color: rgba(255,255,255,0.95); text-decoration: none;
            font-size: 15px; font-weight: 500; border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: background 0.15s;
        }
        .mobile-nav-item > a.mobile-parent:hover { background: rgba(255,255,255,0.1); }
        .m-chevron { width: 14px; height: 14px; transition: transform 0.2s; opacity: 0.7; }
        .mobile-nav-item.open > a.mobile-parent .m-chevron { transform: rotate(180deg); }
        .mobile-nav-item > a.mobile-simple {
            display: block; padding: 15px 28px; color: rgba(255,255,255,0.95);
            text-decoration: none; font-size: 15px; font-weight: 500;
            border-bottom: 1px solid rgba(255,255,255,0.1); transition: background 0.15s;
        }
        .mobile-nav-item > a.mobile-simple:hover { background: rgba(255,255,255,0.1); }
        .mobile-submenu { display: none; flex-direction: column; background: rgba(0,0,0,0.12); }
        .mobile-nav-item.open .mobile-submenu { display: flex; }
        .mobile-submenu a {
            display: block; padding: 12px 28px 12px 44px; color: rgba(255,255,255,0.8);
            text-decoration: none; font-size: 14px; font-weight: 300;
            border-bottom: 1px solid rgba(255,255,255,0.06); transition: background 0.15s;
        }
        .mobile-submenu a:hover { background: rgba(255,255,255,0.08); color: #fff; }

        /* =====================================================
           PAGE LAYOUT
        ===================================================== */
        .app-shell {
            position: fixed;
            top: var(--banner-height);
            bottom: var(--footer-height);
            left: 0; right: 0;
            display: flex;
        }

        /* =====================================================
           LEFT SIDEBAR — frame palette
        ===================================================== */
        .sidebar {
            width: var(--sidebar-width);
            background: #1e1538;
            border-right: 1px solid rgba(147,112,219,0.2);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 16px 16px 10px;
            border-bottom: 1px solid rgba(147,112,219,0.15);
        }
        .sidebar-header h2 {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
        }
        .sidebar-header p {
            font-size: 11.5px;
            color: rgba(255,255,255,0.3);
            margin-top: 4px;
            line-height: 1.5;
        }

        .frame-palette {
            flex: 1;
            overflow-y: auto;
            padding: 10px 10px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .frame-palette::-webkit-scrollbar { width: 4px; }
        .frame-palette::-webkit-scrollbar-thumb { background: rgba(147,112,219,0.3); border-radius: 4px; }

        .palette-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1.5px solid rgba(147,112,219,0.18);
            background: rgba(255,255,255,0.04);
            cursor: grab;
            transition: background 0.15s, border-color 0.15s, transform 0.12s, box-shadow 0.15s;
            user-select: none;
        }
        .palette-item:hover {
            background: rgba(147,112,219,0.15);
            border-color: rgba(147,112,219,0.45);
            transform: translateX(3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        .palette-item:active { cursor: grabbing; }

        /* Remove button — shown on hover */
        .palette-remove {
            width: 24px; height: 24px;
            flex-shrink: 0;
            border: none; background: none;
            border-radius: 6px;
            cursor: pointer;
            color: rgba(255,255,255,0.25);
            display: flex; align-items: center; justify-content: center;
            transition: background 0.15s, color 0.15s;
            opacity: 0;
            pointer-events: none;
        }
        .palette-item:hover .palette-remove {
            opacity: 1;
            pointer-events: auto;
        }
        .palette-remove:hover {
            background: rgba(220, 50, 80, 0.3);
            color: #ff8090;
        }
        .palette-remove svg { width: 13px; height: 13px; }

        .palette-icon {
            width: 34px; height: 34px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .palette-info { flex: 1; min-width: 0; }
        .palette-name {
            font-size: 13px;
            font-weight: 500;
            color: rgba(255,255,255,0.85);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .palette-url {
            font-size: 10.5px;
            color: rgba(255,255,255,0.3);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }
        .palette-drag-handle {
            color: rgba(255,255,255,0.2);
            flex-shrink: 0;
        }
        .palette-drag-handle svg { width: 14px; height: 14px; }

        /* Add custom URL */
        .custom-url-form {
            padding: 10px;
            border-top: 1px solid rgba(147,112,219,0.15);
        }
        .custom-url-form input {
            width: 100%;
            padding: 8px 10px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(147,112,219,0.25);
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 6px;
        }
        .custom-url-form input::placeholder { color: rgba(255,255,255,0.25); }
        .custom-url-form input:focus { border-color: var(--purple); }
        .custom-url-form button {
            width: 100%;
            padding: 8px;
            background: rgba(147,112,219,0.25);
            border: 1px solid rgba(147,112,219,0.4);
            border-radius: 7px;
            color: rgba(255,255,255,0.85);
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s;
        }
        .custom-url-form button:hover { background: rgba(147,112,219,0.4); }

        /* =====================================================
           CANVAS
        ===================================================== */
        .canvas-area {
            flex: 1;
            position: relative;
            overflow: hidden;          /* hard clip — widgets cannot bleed outside */
            clip-path: inset(0);       /* belt-and-suspenders: clip even transformed children */
            background: var(--canvas-bg);
            background-image:
                linear-gradient(rgba(147,112,219,0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(147,112,219,0.07) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .canvas-area.drag-over {
            background-color: #e8e0f8;
            outline: 3px dashed rgba(147,112,219,0.5);
            outline-offset: -8px;
        }

        /* Drop hint */
        .drop-hint {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .drop-hint.hidden { opacity: 0; }
        .drop-hint-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            padding: 48px 60px;
            border: 2px dashed rgba(147,112,219,0.25);
            border-radius: 24px;
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(4px);
        }
        .drop-hint-box svg { width: 52px; height: 52px; color: rgba(147,112,219,0.4); }
        .drop-hint-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: var(--purple-deeper);
            opacity: 0.6;
        }
        .drop-hint-box p { font-size: 13px; color: #9380b0; font-weight: 300; text-align: center; line-height: 1.6; }

        /* =====================================================
           CANVAS SURFACE (pan/zoom)
        ===================================================== */
        #canvas-surface {
            position: absolute;
            inset: 0;
            transform-origin: 0 0;
        }

        /* =====================================================
           FRAME WIDGET
        ===================================================== */
        .frame-widget {
            position: absolute;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(40,15,80,0.22), 0 2px 8px rgba(0,0,0,0.12);
            border: 2px solid rgba(147,112,219,0.25);
            background: #fff;
            min-width: 280px;
            min-height: 180px;
            transition: box-shadow 0.15s;
        }
        .frame-widget:hover {
            box-shadow: 0 12px 40px rgba(40,15,80,0.3), 0 4px 12px rgba(0,0,0,0.15);
        }
        .frame-widget.selected {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(147,112,219,0.3), 0 12px 40px rgba(40,15,80,0.3);
        }

        /* Title bar */
        .frame-titlebar {
            height: 38px;
            display: flex;
            align-items: center;
            padding: 0 10px 0 12px;
            gap: 8px;
            cursor: grab;
            flex-shrink: 0;
            user-select: none;
        }
        .frame-titlebar:active { cursor: grabbing; }

        .frame-favicon {
            width: 18px; height: 18px;
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .frame-title {
            flex: 1;
            font-size: 12.5px;
            font-weight: 500;
            color: rgba(255,255,255,0.9);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .frame-url-input {
            flex: 2;
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 5px;
            padding: 3px 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            color: rgba(255,255,255,0.7);
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .frame-url-input:focus {
            border-color: rgba(255,255,255,0.45);
            background: rgba(0,0,0,0.3);
            color: #fff;
        }

        .frame-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .frame-btn {
            width: 26px; height: 26px;
            border: none; background: rgba(255,255,255,0.12);
            border-radius: 6px; cursor: pointer; color: rgba(255,255,255,0.7);
            display: flex; align-items: center; justify-content: center;
            transition: background 0.15s, color 0.15s;
        }
        .frame-btn:hover { background: rgba(255,255,255,0.25); color: #fff; }
        .frame-btn.close:hover { background: rgba(220,50,80,0.6); }
        .frame-btn svg { width: 13px; height: 13px; }

        /* Notice banner */
        .frame-notice {
            background: #fff8e1;
            border-bottom: 1px solid #ffe082;
            padding: 6px 12px;
            font-size: 11px;
            color: #795500;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .frame-notice svg { width: 13px; height: 13px; flex-shrink: 0; }

        /* iframe container */
        .frame-body {
            flex: 1;
            position: relative;
            overflow: hidden;
            background: #f5f5f5;
        }
        .frame-body iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* Resize handles */
        .resize-se {
            position: absolute;
            bottom: 0; right: 0;
            width: 18px; height: 18px;
            cursor: se-resize;
            z-index: 10;
        }
        .resize-se::after {
            content: '';
            position: absolute;
            bottom: 4px; right: 4px;
            width: 8px; height: 8px;
            border-right: 2px solid rgba(147,112,219,0.5);
            border-bottom: 2px solid rgba(147,112,219,0.5);
            border-radius: 1px;
        }
        .frame-widget.selected .resize-se::after {
            border-color: var(--purple);
        }

        /* Overlay to capture mouse during resize/drag over iframe */
        .iframe-overlay {
            position: absolute;
            inset: 0;
            z-index: 5;
            display: none;
        }
        .frame-widget.dragging   .iframe-overlay,
        .frame-widget.resizing   .iframe-overlay { display: block; }

        /* =====================================================
           TOOLBAR STRIP (bottom of canvas)
        ===================================================== */
        .canvas-toolbar {
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(26,16,48,0.82);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(147,112,219,0.3);
            border-radius: 50px;
            padding: 6px 14px;
            z-index: 20;
        }
        .canvas-toolbar button {
            display: flex; align-items: center; gap: 5px;
            padding: 6px 12px;
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,0.6);
            font-family: 'DM Sans', sans-serif; font-size: 12px;
            border-radius: 30px;
            transition: background 0.15s, color 0.15s;
        }
        .canvas-toolbar button:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .canvas-toolbar button svg { width: 15px; height: 15px; }
        .canvas-toolbar .tb-sep {
            width: 1px; height: 18px;
            background: rgba(255,255,255,0.12);
            flex-shrink: 0;
        }
        .zoom-label {
            font-size: 12px;
            color: rgba(255,255,255,0.45);
            padding: 0 4px;
            min-width: 42px;
            text-align: center;
        }

        /* =====================================================
           FOOTER — exact copy from index.php
        ===================================================== */
        footer.bottom-banner {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--footer-height);
            background: linear-gradient(135deg, #7B5EBF 0%, #9370DB 100%);
            border-top: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 -4px 20px rgba(60,20,100,0.3);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000;
        }
        footer.bottom-banner p { font-size: 13px; color: rgba(255,255,255,0.65); font-weight: 300; }
        footer.bottom-banner p span { color: rgba(255,255,255,0.92); font-weight: 500; }

        /* =====================================================
           RESPONSIVE
        ===================================================== */
        @media (max-width: 700px) {
            :root { --banner-height: 80px; --sidebar-width: 0px; }
            .banner-nav-row { display: none; }
            .hamburger { display: flex; }
            .sidebar { display: none; }
        }
        @media (max-width: 440px) {
            .btn-login span { display: none; }
            .btn-login { padding: 0 12px; }
        }
    </style>
</head>
<body>

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
                <span>Log In</span>
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

    <!-- LEFT SIDEBAR — frame palette -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Frame Panels</h2>
            <p>Drag a frame onto the canvas to embed it</p>
        </div>

        <div class="frame-palette" id="framePalette">
            <?php foreach ($framePresets as $preset): ?>
                <?php if ($preset['id'] === 'custom') continue; ?>
                <div class="palette-item"
                     draggable="true"
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
                <p>Drag frame panels from the left sidebar<br>and drop them anywhere here</p>
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
<footer class="bottom-banner" role="contentinfo">
    <p>&copy; <?= $currentYear ?> <span><?= htmlspecialchars($studioName) ?></span>. All rights reserved.</p>
</footer>

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

// Example listener — swap this out for your real routing / panel logic:
document.addEventListener('nav:click', (e) => {
    const { id, label, parent } = e.detail;
    // Show a brief toast so you can see the click firing during development
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
//  PRESET DATA (from PHP, serialised as JSON)
// ============================================================
const PRESETS = <?= json_encode($framePresets, JSON_UNESCAPED_SLASHES) ?>;

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

function buildWidgetEl(widget) {
    const p = widget.preset;

    const el = document.createElement('div');
    el.className    = 'frame-widget';
    el.dataset.id   = widget.id;

    // --- Title bar ---
    const bar = document.createElement('div');
    bar.className   = 'frame-titlebar';
    bar.style.background = p.color || '#9370DB';

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
            iframe.src = urlInput.value;
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

    // Close
    const btnClose = makeFrameBtn(
        `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>`,
        'Close'
    );
    btnClose.classList.add('close');
    btnClose.addEventListener('click', () => removeWidget(widget.id));

    actions.append(btnReload, btnMin, btnClose);
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
    iframe.src             = p.url || 'about:blank';
    iframe.title           = p.label;
    iframe.loading         = 'lazy';
    // Permissive sandbox — allows scripts & same-origin; tighten as needed
    iframe.sandbox         = 'allow-scripts allow-same-origin allow-forms allow-popups allow-presentation';
    iframe.referrerpolicy  = 'no-referrer';

    // Overlay (blocks iframe pointer events during drag/resize)
    const overlay = document.createElement('div');
    overlay.className = 'iframe-overlay';

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

        const onMove = (ev) => {
            widget.x = origX + (ev.clientX - startX) / state.zoom;
            widget.y = origY + (ev.clientY - startY) / state.zoom;
            positionWidget(widget);   // positionWidget calls clampWidget internally
        };
        const onUp = () => {
            el.classList.remove('dragging');
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup',   onUp);
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

        const onMove = (ev) => {
            const b = getCanvasBounds();
            // Compute desired size
            const desiredW = Math.max(280, origW + (ev.clientX - startX) / state.zoom);
            const desiredH = Math.max(180, origH + (ev.clientY - startY) / state.zoom);
            // Cap so right/bottom edge never exceeds canvas bounds
            widget.w = Math.min(desiredW, b.maxX - widget.x);
            widget.h = Math.min(desiredH, b.maxY - widget.y);
            positionWidget(widget);
        };
        const onUp = () => {
            el.classList.remove('resizing');
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup',   onUp);
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
    createWidget(preset, pt.x, pt.y);
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
        color: '#6B4FA8',
        note: '',
        defaultW: 700,
        defaultH: 480,
    };

    // Build palette item with remove button, then bind all events via bindPaletteItem
    const paletteItem = document.createElement('div');
    paletteItem.className      = 'palette-item';
    paletteItem.draggable      = true;
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
    if (e.key === 'Escape') deselectAll();
    if (e.key === '+' || e.key === '=') adjustZoom(1.2);
    if (e.key === '-') adjustZoom(1 / 1.2);
    if (e.key === '0') {
        state.pan  = { x: 0, y: 0 };
        state.zoom = 1;
        applyTransform();
    }
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

    // Drop a Google frame in the centre to demonstrate
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
})();
</script>
</body>
</html>
