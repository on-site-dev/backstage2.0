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
        ['id' => 'nav-admin-sites', 'label' => 'Manage Sites',        'href' => $baseURL . 'sites.php'],
        ['id' => 'nav-admin-users', 'label' => 'Manage Users', 'href' => $baseURL . 'users.php'],
        ['id' => 'nav-admin-email', 'label' => 'Send Emails', 'href' => $baseURL . 'send_email.php'],
        ['id' => 'nav-admin-sms', 'label' => 'Send Texts', 'href' => $baseURL . 'send_sms.php'],
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
            ['id' => 'nav-about-mission',  'label' => 'Mission',  'href' => '#mission'],
            ['id' => 'nav-about-vision',   'label' => 'Vision',   'href' => '#vision'],
            ['id' => 'nav-about-founders', 'label' => 'Staff', 'href' => '#staff'],
        ],
    ],
];
