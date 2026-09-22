<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input) || empty($input['url'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing url']);
    exit;
}

$presetId = $input['preset_id'] ?? ('custom_' . time());
$label    = $input['label']     ?? 'Custom';
$icon     = $input['icon']      ?? '?';
$url      = $input['url'];
$color    = $input['color']     ?? '#5A3D96';
$note     = $input['note']      ?? '';
$defaultW = (int) ($input['defaultW'] ?? 700);
$defaultH = (int) ($input['defaultH'] ?? 480);
$tab      = $input['tab']       ?? 'home';

// Fixed user id per current requirement — matches save_widget.php / delete_widget.php.
const FRAMEPRESET_USER_ID = 3;

try {
    $pdo = getWidgetsPDO();

    $stmt = $pdo->prepare(
        'INSERT INTO framepresets
            (user_id, preset_id, label, icon, url, color, note, default_w, default_h, tab)
         VALUES
            (:uid, :preset_id, :label, :icon, :url, :color, :note, :default_w, :default_h, :tab)'
    );
    $stmt->execute([
        ':uid'       => FRAMEPRESET_USER_ID,
        ':preset_id' => $presetId,
        ':label'     => $label,
        ':icon'      => $icon,
        ':url'       => $url,
        ':color'     => $color,
        ':note'      => $note,
        ':default_w' => $defaultW,
        ':default_h' => $defaultH,
        ':tab'       => $tab,
    ]);

    echo json_encode(['success' => true, 'preset_id' => $presetId]);
} catch (PDOException $e) {
    error_log('Failed to save frame preset: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
