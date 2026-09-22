<?php 
/**
 * Available frame presets that users can drag onto the canvas.
 * Google and most large sites block embedding via X-Frame-Options.
 * We include a note in the UI and provide embeddable alternatives too.
 */

$framePresets = [
    [
        'id'    => 'calendar',
        'label' => 'Calendar',
        'icon'  => 'G',
        'url'   => 'bs20-calendar/public/index.php',
        'color' => '#4285F4',
        'note'  => '',
        'defaultW' => 800,
        'defaultH' => 540,
    ],
    [
        'id'    => 'dashboard',
        'label' => 'Project Dashboard',
        'icon'  => 'G',
        'url'   => 'projects.php',
        'color' => '#4285F4',
        'note'  => '',
        'defaultW' => 800,
        'defaultH' => 540,
    ],
];

?>