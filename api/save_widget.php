<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

$widgetId = $input['widget_id'] ?? null;
$presetId = $input['preset_id'] ?? null;
$label    = $input['label']     ?? null;
$url      = $input['url']       ?? null;
$x        = $input['x']         ?? null;
$y        = $input['y']         ?? null;
$w        = $input['w']         ?? null;
$h        = $input['h']         ?? null;

if ($widgetId === null || $x === null || $y === null || $w === null || $h === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Fixed user id per current requirement — every widget is attributed to user 3.
const WIDGET_USER_ID = 3;

try {
    $pdo = getWidgetsPDO();
    $sid = session_id();

    // Check-then-update/insert, keyed by (session_id, widget_uid), so a
    // drag/resize on an already-saved widget updates its existing row
    // instead of inserting a duplicate. No unique-key constraint required.
    $checkStmt = $pdo->prepare(
        'SELECT id FROM widgets WHERE session_id = :sid AND widget_uid = :widget_uid LIMIT 1'
    );
    $checkStmt->execute([
        ':sid'        => $sid,
        ':widget_uid' => $widgetId,
    ]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare(
            'UPDATE widgets
                SET preset_id = :preset_id,
                    label     = :label,
                    url       = :url,
                    pos_x     = :pos_x,
                    pos_y     = :pos_y,
                    width     = :width,
                    height    = :height
              WHERE id = :id'
        );
        $stmt->execute([
            ':preset_id' => $presetId,
            ':label'     => $label,
            ':url'       => $url,
            ':pos_x'     => $x,
            ':pos_y'     => $y,
            ':width'     => $w,
            ':height'    => $h,
            ':id'        => $existing['id'],
        ]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO widgets
                (session_id, user_id, widget_uid, preset_id, label, url, pos_x, pos_y, width, height)
             VALUES
                (:sid, :uid, :widget_uid, :preset_id, :label, :url, :pos_x, :pos_y, :width, :height)'
        );
        $stmt->execute([
            ':sid'        => $sid,
            ':uid'        => WIDGET_USER_ID,
            ':widget_uid' => $widgetId,
            ':preset_id'  => $presetId,
            ':label'      => $label,
            ':url'        => $url,
            ':pos_x'      => $x,
            ':pos_y'      => $y,
            ':width'      => $w,
            ':height'     => $h,
        ]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log('Failed to save widget: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
