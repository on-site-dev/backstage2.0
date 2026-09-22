<?php
declare(strict_types=1);

include_once 'config.php';

/* =====================================================================
   usermaint.php
   Maintains ONE row of the `users` table (defined in config.php).

   Modes of operation:
     1) AJAX / JSON API  (used by the edit-form modal in users.php)
          GET  usermaint.php?ajax=1&id=<pk>   -> field metadata + row data
          GET  usermaint.php?ajax=1           -> field metadata + blank row (Add mode)
          POST usermaint.php?ajax=1  (action=save)   -> insert/update, returns JSON
          POST usermaint.php?ajax=1  (action=delete) -> delete row, returns JSON

     2) Stand-alone page (normal browser navigation)
          usermaint.php?id=<pk>  -> full page edit form
          usermaint.php          -> full page "add new user" form
   ===================================================================== */

$isAjax = isset($_GET['ajax'])
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json') && $_SERVER['REQUEST_METHOD'] === 'POST');

function jsonOut(array $payload, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

/* ---------------------------------------------------------------------
   Connect
--------------------------------------------------------------------- */
try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    if ($isAjax) jsonOut(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()], 500);
    $dbError = $e->getMessage();
}

/* ---------------------------------------------------------------------
   Table metadata (columns, primary key, "Y/N" fields)
--------------------------------------------------------------------- */
$columns   = [];
$allowed   = [];
$pkField   = null;
$autoIncPk = false;

$yNoCols = ['active', 'enabled', 'is_admin', 'email_verified'];

if (!isset($dbError)) {
    try {
        $columns = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll();
        foreach ($columns as $col) {
            $allowed[] = $col['Field'];
            if ($col['Key'] === 'PRI' && $pkField === null) {
                $pkField   = $col['Field'];
                $autoIncPk = stripos($col['Extra'], 'auto_increment') !== false;
            }
        }
        if ($pkField === null && !empty($allowed)) {
            $pkField = $allowed[0]; // fallback
        }
    } catch (PDOException $e) {
        $dbError = $e->getMessage();
        if ($isAjax) jsonOut(['success' => false, 'message' => 'Could not read users table: ' . $e->getMessage()], 500);
    }
}

/* ---------------------------------------------------------------------
   Helpers
--------------------------------------------------------------------- */

/** Classify a MySQL column into a simple UI field type. */
function fieldMeta(array $col, array $yNoCols): array {
    $field = $col['Field'];
    $type  = $col['Type'];
    $meta  = [
        'field'    => $field,
        'label'    => ucwords(str_replace('_', ' ', $field)),
        'nullable' => strtoupper($col['Null']) === 'YES',
        'maxlength'=> null,
        'options'  => null,
        'uiType'   => 'text',
    ];

    if (in_array($field, $yNoCols, true)) {
        $meta['uiType'] = 'yn';
    } elseif (preg_match('/^enum\((.*)\)$/i', $type, $m)) {
        $vals = str_getcsv($m[1], ',', "'");
        $meta['uiType'] = 'select';
        $meta['options'] = $vals;
    } elseif (stripos($type, 'tinyint(1)') === 0) {
        $meta['uiType'] = 'checkbox';
    } elseif (stripos($type, 'datetime') === 0 || stripos($type, 'timestamp') === 0) {
        $meta['uiType'] = 'datetime';
    } elseif (stripos($type, 'date') === 0) {
        $meta['uiType'] = 'date';
    } elseif (preg_match('/^(int|bigint|smallint|mediumint|decimal|float|double)/i', $type)) {
        $meta['uiType'] = 'number';
    } elseif (stripos($field, 'password') !== false) {
        $meta['uiType'] = 'password';
    } elseif (preg_match('/^varchar\((\d+)\)/i', $type, $m)) {
        $meta['uiType']    = 'text';
        $meta['maxlength'] = (int) $m[1];
    } elseif (stripos($type, 'text') !== false) {
        $meta['uiType'] = 'textarea';
    }

    return $meta;
}

function buildFieldMetaList(array $columns, array $yNoCols): array {
    $out = [];
    foreach ($columns as $col) {
        $out[] = fieldMeta($col, $yNoCols);
    }
    return $out;
}

/** Fetch a single row by primary key. */
function fetchRow(PDO $pdo, string $pkField, string $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `{$pkField}` = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Insert or update a row based on posted data.
 * Returns ['success'=>bool,'message'=>string,'id'=>mixed]
 */
function saveRow(PDO $pdo, array $columns, array $allowed, string $pkField, bool $autoIncPk, array $yNoCols, array $input): array {
    // Build the set of column => value to persist, restricted to real columns.
    $data = [];
    foreach ($columns as $col) {
        $field = $col['Field'];
        if (!array_key_exists($field, $input)) continue;

        $raw = $input[$field];

        // Password fields: blank means "leave unchanged" -> skip entirely.
        if (stripos($field, 'password') !== false) {
            if ($raw === '' || $raw === null) continue;
            $data[$field] = $raw; // NOTE: hash appropriately for your auth scheme before storing in production.
            continue;
        }

        // Y/N fields: normalize, allow explicit NULL sentinel.
        if (in_array($field, $yNoCols, true)) {
            if ($raw === '__NULL__' || $raw === '' || $raw === null) {
                $data[$field] = null;
            } else {
                $data[$field] = ($raw === 'Y' || $raw === '1' || $raw === 'true') ? 'Y' : 'N';
            }
            continue;
        }

        // Checkbox (tinyint(1)) fields.
        if (stripos($col['Type'], 'tinyint(1)') === 0) {
            $data[$field] = (!empty($raw) && $raw !== '0') ? 1 : 0;
            continue;
        }

        // Everything else: empty string -> NULL if column is nullable, else keep as-is.
        if ($raw === '' && strtoupper($col['Null']) === 'YES') {
            $data[$field] = null;
        } else {
            $data[$field] = $raw;
        }
    }

    $pkValue = isset($input[$pkField]) ? trim((string) $input[$pkField]) : '';

    // Auto-increment PK should never be written directly on insert.
    if ($autoIncPk) {
        unset($data[$pkField]);
    }

    try {
        $exists = false;
        if ($pkValue !== '') {
            $exists = fetchRow($pdo, $pkField, $pkValue) !== null;
        }

        if ($exists) {
            // ---- UPDATE ----
            unset($data[$pkField]); // never overwrite the key itself
            if (empty($data)) {
                return ['success' => true, 'message' => 'Nothing to update.', 'id' => $pkValue];
            }
            $setSql = implode(', ', array_map(fn($f) => "`{$f}` = :{$f}", array_keys($data)));
            $stmt   = $pdo->prepare("UPDATE `users` SET {$setSql} WHERE `{$pkField}` = :__pk");
            foreach ($data as $f => $v) {
                $stmt->bindValue(":{$f}", $v);
            }
            $stmt->bindValue(':__pk', $pkValue);
            $stmt->execute();
            return ['success' => true, 'message' => 'User updated successfully.', 'id' => $pkValue];
        }

        // ---- INSERT ----
        if (!$autoIncPk && $pkValue !== '') {
            $data[$pkField] = $pkValue;
        }
        if (empty($data)) {
            return ['success' => false, 'message' => 'No fields to insert.', 'id' => null];
        }

        // Validate required columns before attempting the insert. A NOT NULL
        // column with no default (and not the auto-increment PK) that ended
        // up missing/blank would otherwise fail deep inside the DB driver
        // with no visible feedback to the user - this surfaces it clearly.
        $missing = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $pkField && $autoIncPk) continue;
            $isRequired = strtoupper($col['Null']) === 'NO' && $col['Default'] === null;
            if (!$isRequired) continue;
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            $labels = array_map(fn($f) => ucwords(str_replace('_', ' ', $f)), $missing);
            return ['success' => false, 'message' => 'Please fill in required field(s): ' . implode(', ', $labels) . '.', 'id' => null];
        }

        $cols  = array_keys($data);
        $place = array_map(fn($f) => ":{$f}", $cols);
        $sql   = "INSERT INTO `users` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $place) . ")";
        $stmt  = $pdo->prepare($sql);
        foreach ($data as $f => $v) {
            $stmt->bindValue(":{$f}", $v);
        }
        $stmt->execute();
        $newId = $autoIncPk ? $pdo->lastInsertId() : $pkValue;
        return ['success' => true, 'message' => 'User added successfully.', 'id' => $newId];

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Save failed: ' . $e->getMessage(), 'id' => null];
    }
}

/* ---------------------------------------------------------------------
   Route: handle POST (save / delete)
--------------------------------------------------------------------- */
if (!isset($dbError) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawBody = file_get_contents('php://input');
    $posted  = $_POST;
    if (empty($posted) && $rawBody !== '') {
        $asJson = json_decode($rawBody, true);
        if (is_array($asJson)) $posted = $asJson;
    }

    $action = $posted['action'] ?? 'save';

    if ($action === 'delete') {
        $id = trim((string) ($posted[$pkField] ?? ''));
        if ($id === '') {
            $result = ['success' => false, 'message' => 'Missing id for delete.'];
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM `users` WHERE `{$pkField}` = :id");
                $stmt->execute([':id' => $id]);
                $result = ['success' => true, 'message' => 'User deleted.'];
            } catch (PDOException $e) {
                $result = ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
            }
        }
    } else {
        $result = saveRow($pdo, $columns, $allowed, $pkField, $autoIncPk, $yNoCols, $posted);
    }

    if ($isAjax) jsonOut($result);

    // Non-ajax (plain form post):
    if ($result['success']) {
        // Success -> back to the grid with a confirmation flash.
        $qs = http_build_query(['saved' => 1, 'msg' => $result['message']]);
        header('Location: users.php?' . $qs);
        exit;
    }

    // Failure on save -> stay on THIS page (not the grid) so the error is
    // actually visible, instead of silently vanishing on redirect.
    if ($action !== 'delete') {
        $backParams = ['saved' => 0, 'msg' => $result['message']];
        $backId = isset($posted[$pkField]) ? trim((string) $posted[$pkField]) : '';
        if ($backId !== '') $backParams['id'] = $backId;
        $qs = http_build_query($backParams);
        header('Location: usermaint.php?' . $qs);
        exit;
    }

    // Failure on delete -> back to the grid with an error flash.
    $qs = http_build_query(['saved' => 0, 'msg' => $result['message']]);
    header('Location: users.php?' . $qs);
    exit;
}

/* ---------------------------------------------------------------------
   Route: AJAX GET (field metadata + row data)
--------------------------------------------------------------------- */
if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($dbError)) jsonOut(['success' => false, 'message' => $dbError], 500);

    $id  = $_GET['id'] ?? null;
    $row = null;
    if ($id !== null && $id !== '') {
        $row = fetchRow($pdo, $pkField, (string) $id);
        if ($row === null) {
            jsonOut(['success' => false, 'message' => 'Row not found.']);
        }
    }

    jsonOut([
        'success' => true,
        'pk'      => $pkField,
        'autoInc' => $autoIncPk,
        'fields'  => buildFieldMetaList($columns, $yNoCols),
        'row'     => $row, // null = "add new" mode
    ]);
}

/* =====================================================================
   Stand-alone HTML page (normal navigation, not AJAX)
   ===================================================================== */
$siteName    = "Backstage 2.0";
$studioName  = "On-Site Studios";
$currentYear = date('Y');

$editId   = $_GET['id'] ?? null;
$existing = null;
if (!isset($dbError) && $editId !== null && $editId !== '') {
    $existing = fetchRow($pdo, $pkField, (string) $editId);
}
$isNew     = $existing === null;
$pageTitle = ($isNew ? "Add User — " : "Edit User — ") . htmlspecialchars($siteName);

$flashSaved = isset($_GET['saved']) ? (bool) $_GET['saved'] : null;
$flashMsg   = $_GET['msg'] ?? null;

/* NOTE: This is a deliberately standalone page — no shared site header,
   navigation, or footer are rendered here. The only ways off this page
   are the Cancel link and the Save button below, both of which return
   the user to users.php. */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/bs20sitestyles.css">
<style>
  * { box-sizing: border-box; }
  html, body { height:100%; margin:0; }
  .um-wrap {
    width:100%; height:90vh; margin:0 auto; padding:24px 32px;
    display:flex; flex-direction:column;
  }
  .um-card {
    flex:1; min-height:0; display:flex; flex-direction:column;
    background:#fff; border-radius:14px; box-shadow:0 4px 24px rgba(60,30,100,.12);
    padding:28px 36px 24px;
  }
  .um-card-header { flex:none; }
  .um-title { font-family:'Playfair Display', serif; font-size:26px; color:#4a2e7a; margin-bottom:4px; }
  .um-sub   { color:#8a7aa8; font-size:13px; margin-bottom:22px; }
  .um-flash { padding:10px 14px; border-radius:8px; margin-bottom:18px; font-size:14px; }
  .um-flash.ok  { background:#e8f8ee; color:#1e7a44; border:1px solid #b9e8c9; }
  .um-flash.err { background:#fdeaea; color:#a3282f; border:1px solid #f2c2c2; }

  .um-form { flex:1; min-height:0; display:flex; flex-direction:column; }

  /* Tabs */
  .um-tabs { flex:none; display:flex; gap:4px; border-bottom:2px solid #ece5f7; margin-bottom:20px; }
  .um-tab {
    appearance:none; background:none; border:none; cursor:pointer;
    padding:10px 18px; font-size:14px; font-weight:500; color:#8a7aa8;
    border-bottom:2px solid transparent; margin-bottom:-2px;
    font-family:inherit; transition:color .15s ease, border-color .15s ease;
  }
  .um-tab:hover { color:#5a4a78; }
  .um-tab.active { color:#4a2e7a; border-bottom-color:#7A55C7; }

  /* Scrollable panel area sits between the fixed tab bar and fixed action bar */
  .um-panels { flex:1; min-height:0; overflow-y:auto; padding-right:8px; }
  .um-panel { display:none; }
  .um-panel.active { display:block; }

  .um-grid  { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:18px 24px; }
  .um-field { display:flex; flex-direction:column; gap:5px; }
  .um-field.full { grid-column:1 / -1; }
  .um-field label { font-size:12.5px; font-weight:500; color:#5a4a78; text-transform:uppercase; letter-spacing:.4px; }
  .um-field label .um-req { color:#c0392b; margin-left:2px; }
  .um-field input, .um-field select, .um-field textarea {
    border:1px solid #d9d0ec; border-radius:8px; padding:9px 11px; font-size:14px; font-family:inherit; color:#333;
  }
  .um-field input:focus, .um-field select:focus, .um-field textarea:focus { outline:none; border-color:#7A55C7; box-shadow:0 0 0 3px rgba(122,85,199,.15); }
  .um-field textarea { min-height:80px; resize:vertical; }
  .um-actions { flex:none; margin-top:16px; display:flex; gap:12px; justify-content:flex-end; border-top:1px solid #ece5f7; padding-top:20px; }
  .um-btn { border:none; border-radius:8px; padding:10px 20px; font-size:14px; font-weight:500; cursor:pointer; }
  .um-btn-save   { background:#7A55C7; color:#fff; }
  .um-btn-save:hover { background:#6644AA; }
  .um-btn-cancel { background:#f1eefa; color:#5a4a78; text-decoration:none; display:inline-flex; align-items:center; }
  .um-btn-cancel:hover { background:#e5dff5; }
  .um-hint { font-size:11.5px; color:#a89bc4; margin-top:2px; }

  .um-placeholder {
    display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;
    gap:10px; padding:56px 20px; color:#9380b0; border:1.5px dashed #e2d9f2; border-radius:12px; background:#faf8fd;
  }
  .um-placeholder svg { width:34px; height:34px; color:#b9a8d9; }
  .um-placeholder h3 { font-size:15px; color:#5a4a78; margin:0; font-weight:600; }
  .um-placeholder p { font-size:13px; margin:0; max-width:420px; line-height:1.5; }
</style>
</head>
<body style="overflow:hidden;">

<main class="um-wrap">
  <div class="um-card">
    <?php if (isset($dbError)): ?>
      <div class="um-flash err">Could not connect to the database: <?= htmlspecialchars($dbError) ?></div>
    <?php else: ?>

      <?php if ($flashSaved !== null): ?>
        <div class="um-flash <?= $flashSaved ? 'ok' : 'err' ?>"><?= htmlspecialchars((string) $flashMsg) ?></div>
      <?php endif; ?>

      <div class="um-title"><?= $isNew ? 'Add User' : 'Edit User' ?></div>
      <div class="um-sub"><?= htmlspecialchars(DB_NAME) ?> &rsaquo; users <?= $isNew ? '' : '&rsaquo; #' . htmlspecialchars((string)($existing[$pkField] ?? '')) ?></div>

      <div class="um-tabs" role="tablist">
        <button type="button" class="um-tab active" data-tab="general" role="tab" aria-selected="true">General</button>
        <button type="button" class="um-tab" data-tab="attributes" role="tab" aria-selected="false">Attributes</button>
        <button type="button" class="um-tab" data-tab="roles" role="tab" aria-selected="false">Roles</button>
      </div>

      <form method="post" action="usermaint.php" class="um-form">
        <input type="hidden" name="action" value="save">
        <?php if (!$isNew): ?>
          <input type="hidden" name="<?= htmlspecialchars($pkField) ?>" value="<?= htmlspecialchars((string) $existing[$pkField]) ?>">
        <?php endif; ?>
        <?php
          // created_by / created_date / updated_by / updated_date are hidden from the
          // form entirely. The two date columns are preserved via hidden inputs (stamped
          // to "now" for a brand-new record) so NOT NULL date columns without a DB
          // default don't block inserts; created_by/updated_by are left untouched since
          // this app has no logged-in-user identity to stamp them with yet.
          foreach (['created_date', 'updated_date'] as $dtFieldName):
              $dtCol = null;
              foreach ($columns as $c) { if (strtolower($c['Field']) === $dtFieldName) { $dtCol = $c; break; } }
              if ($dtCol === null) continue;
              $dtVal = $isNew ? date('Y-m-d H:i:s') : (string) ($existing[$dtCol['Field']] ?? '');
        ?>
          <input type="hidden" name="<?= htmlspecialchars($dtCol['Field']) ?>" value="<?= htmlspecialchars($dtVal) ?>">
        <?php endforeach; ?>

        <div class="um-panels">
        <div class="um-panel active" data-panel="general">
        <div class="um-grid">
          <?php
            $hiddenFromUi = ['created_by', 'created_date', 'updated_by', 'updated_date'];
          ?>
          <?php foreach ($columns as $col):
              $field = $col['Field'];
              if ($field === $pkField && $autoIncPk) continue; // auto id not editable
              if (in_array(strtolower($field), $hiddenFromUi, true)) continue; // hidden audit fields
              $meta  = fieldMeta($col, $yNoCols);
              $val   = $existing[$field] ?? '';
              $full  = in_array($meta['uiType'], ['textarea'], true);
              $isRequired = strtoupper($col['Null']) === 'NO' && $col['Default'] === null;
          ?>
            <div class="um-field<?= $full ? ' full' : '' ?>">
              <label for="f_<?= htmlspecialchars($field) ?>"><?= htmlspecialchars($meta['label']) ?><?php if ($isRequired): ?><span class="um-req">*</span><?php endif; ?></label>
              <?php if ($field === $pkField && !$autoIncPk): ?>
                <input type="text" id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>"
                       value="<?= htmlspecialchars((string) $val) ?>" <?= $isNew ? '' : 'readonly' ?>>

              <?php elseif (strtolower($field) === 'status'): ?>
                <select id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>">
                  <option value="Active"  <?= (string) $val === 'Active'  ? 'selected' : '' ?>>Active</option>
                  <option value="Deleted" <?= (string) $val === 'Deleted' ? 'selected' : '' ?>>Deleted</option>
                  <option value="Hidden"  <?= (string) $val === 'Hidden'  ? 'selected' : '' ?>>Hidden</option>
                  <?php if ($val !== '' && !in_array((string) $val, ['Active', 'Deleted', 'Hidden'], true)): ?>
                    <option value="<?= htmlspecialchars((string) $val) ?>" selected>Current: <?= htmlspecialchars((string) $val) ?></option>
                  <?php endif; ?>
                </select>

              <?php elseif ($meta['uiType'] === 'yn'): ?>
                <select id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>">
                  <option value="Y" <?= $val === 'Y' ? 'selected' : '' ?>>Yes (Y)</option>
                  <option value="N" <?= $val === 'N' ? 'selected' : '' ?>>No (N)</option>
                  <?php if ($meta['nullable']): ?>
                    <option value="__NULL__" <?= $val === null || $val === '' ? 'selected' : '' ?>>NULL</option>
                  <?php endif; ?>
                </select>

              <?php elseif ($meta['uiType'] === 'select'): ?>
                <select id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>">
                  <?php foreach ($meta['options'] as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= (string) $val === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                  <?php endforeach; ?>
                </select>

              <?php elseif ($meta['uiType'] === 'checkbox'): ?>
                <select id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>">
                  <option value="1" <?= (string) $val === '1' ? 'selected' : '' ?>>1</option>
                  <option value="0" <?= (string) $val === '0' ? 'selected' : '' ?>>0</option>
                </select>

              <?php elseif ($meta['uiType'] === 'password'): ?>
                <input type="password" id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>" autocomplete="new-password" placeholder="<?= $isNew ? '' : 'Leave blank to keep current password' ?>">
                <?php if (!$isNew): ?><div class="um-hint">Leave blank to keep the current password.</div><?php endif; ?>

              <?php elseif ($meta['uiType'] === 'date'): ?>
                <input type="date" id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>" value="<?= htmlspecialchars(substr((string) $val, 0, 10)) ?>">

              <?php elseif ($meta['uiType'] === 'datetime'): ?>
                <input type="datetime-local" id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>" value="<?= htmlspecialchars(str_replace(' ', 'T', substr((string) $val, 0, 16))) ?>">

              <?php elseif ($meta['uiType'] === 'number'): ?>
                <input type="number" id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>" value="<?= htmlspecialchars((string) $val) ?>">

              <?php elseif ($meta['uiType'] === 'textarea'): ?>
                <textarea id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>"><?= htmlspecialchars((string) $val) ?></textarea>

              <?php else: ?>
                <input type="text" id="f_<?= htmlspecialchars($field) ?>" name="<?= htmlspecialchars($field) ?>"
                       value="<?= htmlspecialchars((string) $val) ?>"
                       <?= $meta['maxlength'] ? 'maxlength="' . (int) $meta['maxlength'] . '"' : '' ?>>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        </div><!-- /panel: general -->

        <div class="um-panel" data-panel="attributes">
          <div class="um-placeholder">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            <h3>No attributes source connected yet</h3>
            <p>This tab is ready for a user-attributes table (e.g. department, location, custom fields). Once one is connected, its fields will appear here alongside General and Roles.</p>
          </div>
        </div>

        <div class="um-panel" data-panel="roles">
          <div class="um-placeholder">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <h3>No roles source connected yet</h3>
            <p>This tab is ready for a roles/permissions table. Once one is connected, role assignments for this user can be managed here.</p>
          </div>
        </div>
        </div><!-- /um-panels -->

        <div class="um-actions">
          <a href="users.php" class="um-btn um-btn-cancel">Cancel</a>
          <button type="submit" class="um-btn um-btn-save">Save</button>
        </div>
      </form>

    <?php endif; ?>
  </div>
</main>

<script>
(function () {
  var tabs   = document.querySelectorAll('.um-tab');
  var panels = document.querySelectorAll('.um-panel');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var target = tab.getAttribute('data-tab');
      tabs.forEach(function (t) {
        t.classList.toggle('active', t === tab);
        t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
      });
      panels.forEach(function (p) {
        p.classList.toggle('active', p.getAttribute('data-panel') === target);
      });
    });
  });
})();
</script>

</body>
</html>
