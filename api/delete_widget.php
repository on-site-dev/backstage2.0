<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input) || !isset($input['widget_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing widget_id']);
    exit;
}

$widgetId = $input['widget_id'];

// Fixed user id per current requirement — matches save_widget.php.
const WIDGET_USER_ID = 3;

try {
    $pdo = getWidgetsPDO();

    // Scoped to this session + user so one browser/session can't delete
    // another's rows just by guessing a widget_uid.
    $stmt = $pdo->prepare(
        'DELETE FROM widgets
          WHERE session_id = :sid AND user_id = :uid AND widget_uid = :widget_uid'
    );
    $stmt->execute([
        ':sid'        => session_id(),
        ':uid'        => WIDGET_USER_ID,
        ':widget_uid' => $widgetId,
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log('Failed to delete widget: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
