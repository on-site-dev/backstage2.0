<?php
declare(strict_types=1);

session_start();

include_once 'config.php';

$siteName    = $_SESSION['siteName']   ?? "Backstage 2.1";
$studioName  = $_SESSION['studioName'] ?? "On-Site Studios";
$currentYear = date('Y');
$pageTitle   = "Users — " . htmlspecialchars($siteName);

$flashSaved = isset($_GET['saved']) ? (bool) $_GET['saved'] : null;
$flashMsg   = $_GET['msg'] ?? null;


$loggedInStatusButton = "Log In";
if (isset($_SESSION["loggedInStatus"]) && $_SESSION["loggedInStatus"] == "In") {
    $loggedInStatusButton = "Log Out";
}

// Suppress header/footer when loaded inside a dashboard frame-widget (see index.php)
$isPalletEmbed = isset($_GET['pallet']) && $_GET['pallet'] === '1';

include_once 'includes/navitems.php';

$rows = $columns = [];
$dbError = null;
$totalRows = 0;
$colUnique = [];

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $columns     = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll();
    $allowedCols = array_column($columns, 'Field');
    $pkField     = null;
    foreach ($columns as $col) {
        if ($col['Key'] === 'PRI') { $pkField = $col['Field']; break; }
    }
    if ($pkField === null) { $pkField = $allowedCols[0] ?? null; }
    $sortCol     = $_GET['sort'] ?? ($allowedCols[0] ?? '');
    $sortDir     = strtoupper($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
    if (!in_array($sortCol, $allowedCols, true)) $sortCol = $allowedCols[0] ?? '';
    $orderSql    = $sortCol !== '' ? " ORDER BY `{$sortCol}` {$sortDir}" : '';
    $rows        = $pdo->query("SELECT * FROM `users`{$orderSql}")->fetchAll();
    $totalRows   = count($rows);
    foreach ($allowedCols as $field) {
        $vals = [];
        foreach ($rows as $r) {
            $v = $r[$field] ?? null;
            $vals[$v === null ? "\x00NULL\x00" : (string)$v] = $v;
        }
        ksort($vals, SORT_NATURAL | SORT_FLAG_CASE);
        $colUnique[$field] = array_values($vals);
    }
} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

function sortUrl(string $col, string $cc, string $cd): string {
    $q = ['sort' => $col, 'dir' => ($col === $cc && $cd === 'ASC') ? 'DESC' : 'ASC'];
    if (isset($_GET['pallet']) && $_GET['pallet'] === '1') $q['pallet'] = '1';
    return '?' . http_build_query($q);
}
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
</head>
<body>

<?php if (!$isPalletEmbed): ?>
<?php include_once 'includes/header.php'; ?>
<?php endif; ?>

<!-- COLUMN MANAGER DRAWER -->
<div class="col-manager-overlay" id="colManagerOverlay" aria-hidden="true">
  <div class="col-manager-drawer" role="dialog" aria-label="Manage columns" aria-modal="true">
    <div class="cmd-header">
      <span class="cmd-title">Manage Columns</span>
      <button class="cmd-close" id="cmdClose" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="cmd-subhead">Drag to reorder · Toggle to show / hide</div>
    <div class="cmd-actions">
      <button class="cmd-action-btn" id="cmdShowAll">Show all</button>
      <button class="cmd-action-btn" id="cmdHideAll">Hide all</button>
    </div>
    <div class="cmd-list" id="cmdList">
      <?php foreach ($columns as $i => $col): ?>
        <div class="cmd-item" draggable="true" data-col="<?= $i ?>">
          <span class="cmd-drag-handle" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
          </span>
          <span class="cmd-item-label"><?= htmlspecialchars($col['Field']) ?></span>
          <label class="cmd-toggle" aria-label="Show <?= htmlspecialchars($col['Field']) ?>">
            <input type="checkbox" checked data-col-toggle="<?= $i ?>">
            <span class="cmd-toggle-track"></span>
            <span class="cmd-toggle-thumb"></span>
          </label>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="cmd-footer">
      <button class="cmd-reset-btn" id="cmdReset">↺ Reset to default order &amp; visibility</button>
    </div>
  </div>
</div>

<!-- MAIN -->
<main>
  <?php if ($flashSaved !== null): ?>
    <div class="flash-banner <?= $flashSaved ? 'flash-ok' : 'flash-err' ?>" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;<?= $flashSaved ? 'background:#e8f8ee;color:#1e7a44;border:1px solid #b9e8c9;' : 'background:#fdeaea;color:#a3282f;border:1px solid #f2c2c2;' ?>">
      <?= htmlspecialchars((string) $flashMsg) ?>
    </div>
  <?php endif; ?>
  <div class="page-header">
    <div class="page-header-left">
      <h1>Users</h1>
      <p><?= htmlspecialchars(DB_NAME) ?> &rsaquo; users</p>
    </div>
    <?php if (!$dbError): ?>
      <span class="row-count-badge">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        <?= number_format($totalRows) ?> row<?= $totalRows !== 1 ? 's' : '' ?>
      </span>
      <a href="usermaint.php" class="toolbar-btn" style="margin-left:auto;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add User
      </a>
    <?php endif; ?>
  </div>

  <?php if ($dbError): ?>
    <div class="table-card"><div class="state-box">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      <h3>Could not connect to the database</h3>
      <p>Check that the server is running and the credentials are correct.</p>
      <code><?= htmlspecialchars($dbError) ?></code>
    </div></div>

  <?php elseif (empty($columns)): ?>
    <div class="table-card"><div class="state-box">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
      <h3>No columns found</h3>
      <p>The <strong>users</strong> table appears to be empty or does not exist.</p>
    </div></div>

  <?php else:
    $allowedCols = array_column($columns, 'Field');
    $sortCol = $_GET['sort'] ?? ($allowedCols[0] ?? '');
    $sortDir = strtoupper($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
    if (!in_array($sortCol, $allowedCols, true)) $sortCol = $allowedCols[0] ?? '';
  ?>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="filter-search">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
        <input type="text" id="tableFilter" placeholder="Filter rows…" autocomplete="off" aria-label="Filter rows"/>
        <button type="button" class="filter-search-clear" id="btnClearText" aria-label="Clear search">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <!-- Columns button -->
      <button class="toolbar-btn" id="btnOpenColManager" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
        Columns
      </button>

      <button class="btn-clear-all" id="btnClearAll" type="button">✕ Clear filters</button>
      <span class="filter-label" id="visibleCount"><?= $totalRows ?> of <?= $totalRows ?> rows</span>
    </div>

    <!-- Active filter chips -->
    <div class="active-filters" id="activeFilters"></div>

    <!-- Table -->
    <div class="table-card">
      <div class="table-scroll">
        <table id="sitesTable" aria-label="Users data">
          <thead>
            <!-- Sort row -->
            <tr class="sort-row" id="sortRow">
              <th class="row-num"><a class="sort-link" style="justify-content:center;cursor:default;pointer-events:none;">#</a></th>
              <th class="actions-th"><a class="sort-link" style="justify-content:center;letter-spacing:.5px;text-transform:uppercase;font-size:12.5px;padding:13px 16px;color:rgba(255,255,255,.85);cursor:default;pointer-events:none;">Actions</a></th>
              <?php foreach ($columns as $col):
                $field = $col['Field'];
                $isActive = ($field === $sortCol);
                $dirClass = $isActive ? 'sort-active dir-'.strtolower($sortDir) : '';
              ?>
                <th class="<?= $dirClass ?>" data-field="<?= htmlspecialchars($field) ?>" data-col-th="<?= array_search($field,$allowedCols) ?>">
                  <a class="sort-link" href="<?= htmlspecialchars(sortUrl($field,$sortCol,$sortDir)) ?>" title="Sort by <?= htmlspecialchars($field) ?>">
                    <?= htmlspecialchars($field) ?>
                    <span class="sort-icon" aria-hidden="true">
                      <svg class="arrow-up" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4l8 8H4z"/></svg>
                      <svg class="arrow-down" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 20l-8-8h16z"/></svg>
                    </span>
                  </a>
                </th>
              <?php endforeach; ?>
            </tr>

            <!-- Filter row -->
            <tr class="filter-row" id="filterRow">
              <th class="row-num"></th>
              <th class="actions-th" style="padding:6px 8px;"></th>
              <?php foreach ($columns as $col):
                $field  = $col['Field'];
                $uniq   = $colUnique[$field] ?? [];
                $colIdx = array_search($field, $allowedCols);
              ?>
                <th data-col-fth="<?= $colIdx ?>">
                  <div class="col-filter-wrap" data-col-index="<?= $colIdx ?>">
                    <button type="button" class="col-filter-btn" data-field="<?= htmlspecialchars($field) ?>" aria-haspopup="listbox" aria-expanded="false">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                      <span class="btn-label">All</span>
                      <span class="filter-count">0</span>
                    </button>
                    <div class="col-filter-panel" role="listbox" aria-multiselectable="true">
                      <div class="cfp-header">
                        <span class="cfp-title"><?= htmlspecialchars($field) ?></span>
                        <div class="cfp-actions">
                          <button type="button" class="cfp-action-btn cfp-select-all">All</button>
                          <button type="button" class="cfp-action-btn cfp-clear">None</button>
                        </div>
                      </div>
                      <div class="cfp-search">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                        <input type="text" class="cfp-search-input" placeholder="Search values…" autocomplete="off"/>
                      </div>
                      <div class="cfp-list">
                        <?php foreach ($uniq as $val):
                          $isNull  = ($val === null);
                          $display = $isNull ? '(NULL)' : htmlspecialchars((string)$val);
                          $rawVal  = $isNull ? '__NULL__' : htmlspecialchars((string)$val);
                          $uid     = 'cf_'.preg_replace('/[^a-z0-9]/i','_',$field).'_'.md5((string)$val);
                        ?>
                          <div class="cfp-item">
                            <input type="checkbox" id="<?= $uid ?>" value="<?= $rawVal ?>" checked>
                            <label for="<?= $uid ?>" <?= $isNull ? 'class="null-label"' : '' ?>><?= $display ?></label>
                          </div>
                        <?php endforeach; ?>
                        <div class="cfp-no-match">No matching values</div>
                      </div>
                      <div class="cfp-footer">
                        <button type="button" class="cfp-cancel">Cancel</button>
                        <button type="button" class="cfp-apply">Apply</button>
                      </div>
                    </div>
                  </div>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>

          <tbody id="sitesBody">
            <?php if (empty($rows)): ?>
              <tr><td colspan="<?= count($columns)+1 ?>" style="text-align:center;padding:40px;color:#9380b0;font-weight:300;">No rows found in the users table.</td></tr>
            <?php else: foreach ($rows as $i => $row): ?>
              <tr data-pk="<?= htmlspecialchars((string)($pkField !== null ? ($row[$pkField] ?? '') : '')) ?>">
                <td class="row-num"><?= $i+1 ?></td>
                <td class="actions-td">
                  <button type="button" class="action-btn action-btn-edit"
                          data-action="edit" data-row="<?= $i ?>"
                          title="Edit this row" aria-label="Edit row <?= $i+1 ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button type="button" class="action-btn action-btn-delete"
                          data-action="delete" data-row="<?= $i ?>"
                          title="Delete this row" aria-label="Delete row <?= $i+1 ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </td>
                <?php
                $yNoCols = ['active','enabled','is_admin','email_verified'];
                foreach ($columns as $col):
                  $field    = $col['Field'];
                  $val      = $row[$field] ?? null;
                  $dataVal  = $val === null ? '__NULL__' : (string)$val;
                  $editable = in_array($field, $yNoCols, true);
                ?>
                  <td class="td-truncate<?= $editable ? ' yn-cell' : '' ?>"
                      title="<?= $editable ? 'Click to change' : htmlspecialchars((string)($val ?? '')) ?>"
                      data-val="<?= htmlspecialchars($dataVal) ?>"
                      data-col-td="<?= array_search($field,$allowedCols) ?>"
                      data-field="<?= htmlspecialchars($field) ?>"
                      <?= $editable ? 'data-yn="true"' : '' ?>>
                    <?php if ($editable):
                      $badge = $val === null ? '<span class="yn-badge yn-null">NULL</span>'
                             : ($val === 'Y' ? '<span class="yn-badge yn-y">Y</span>'
                                            : '<span class="yn-badge yn-n">N</span>');
                      echo $badge;
                    elseif ($val === null): ?><span class="null-pill">NULL</span><?php else: ?><?= htmlspecialchars((string)$val) ?><?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
        <div id="noResults">No rows match the active filters.</div>
      </div>
      <div class="table-footer">
        <span class="sort-info">Sorted by <strong><?= htmlspecialchars($sortCol) ?></strong>&nbsp;<?= $sortDir==='ASC' ? '↑ A → Z' : '↓ Z → A' ?></span>
        <span id="footerCount"><?= $totalRows ?> row<?= $totalRows!==1?'s':'' ?></span>
      </div>
    </div>

  <?php endif; ?>
</main>

<!-- ── ACTION MODAL ── -->
<div class="action-modal-backdrop" id="actionModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="actionModalTitle">
  <div class="action-modal" id="actionModal">
    <div class="action-modal-header">
      <div class="action-modal-icon" id="actionModalIcon"></div>
      <span class="action-modal-title" id="actionModalTitle"></span>
    </div>
    <div class="action-modal-body" id="actionModalBody"></div>
    <div class="action-modal-meta" id="actionModalMeta"></div>
    <div class="action-modal-footer" id="actionModalFooter"></div>
  </div>
</div>

<?php if (!$isPalletEmbed): ?>
<footer class="bottom-banner" role="contentinfo">
  <p>&copy; 2012–<?= $currentYear ?> <span><?= htmlspecialchars($studioName) ?></span>. All rights reserved.</p>
</footer>
<?php endif; ?>

<script>
// ── COLUMN MANAGER ──────────────────────────────────────────
const overlay        = document.getElementById('colManagerOverlay');
const btnOpenColMgr  = document.getElementById('btnOpenColManager');
const cmdClose       = document.getElementById('cmdClose');
const cmdList        = document.getElementById('cmdList');
const cmdShowAll     = document.getElementById('cmdShowAll');
const cmdHideAll     = document.getElementById('cmdHideAll');
const cmdReset       = document.getElementById('cmdReset');
const sitesTable     = document.getElementById('sitesTable');
const sortRow        = document.getElementById('sortRow');
const filterRow      = document.getElementById('filterRow');
const sitesBody      = document.getElementById('sitesBody');

// Column state: array of {colIndex, visible} in display order
const TOTAL_COLS = <?= count($columns) ?>;
let colOrder = Array.from({length: TOTAL_COLS}, (_, i) => ({ idx: i, visible: true }));

function openColManager() {
    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden','false');
    cmdClose.focus();
}
function closeColManager() {
    overlay.classList.remove('open');
    overlay.setAttribute('aria-hidden','true');
    btnOpenColMgr.focus();
}
btnOpenColMgr?.addEventListener('click', openColManager);
cmdClose?.addEventListener('click', closeColManager);
overlay?.addEventListener('click', e => { if (e.target === overlay) closeColManager(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape' && overlay.classList.contains('open')) closeColManager(); });

// Apply column order + visibility to the table
function applyColumnLayout() {
    // ── Sort-header row ──
    const sortThs = Array.from(sortRow.querySelectorAll('th[data-col-th]'));
    colOrder.forEach(({ idx, visible }) => {
        const th = sortThs.find(t => t.dataset.colTh == idx);
        if (!th) return;
        sortRow.appendChild(th);                         // move to end = re-order
        th.classList.toggle('col-hidden', !visible);
    });

    // ── Filter row ──
    const filterThs = Array.from(filterRow.querySelectorAll('th[data-col-fth]'));
    colOrder.forEach(({ idx, visible }) => {
        const th = filterThs.find(t => t.dataset.colFth == idx);
        if (!th) return;
        filterRow.appendChild(th);
        th.classList.toggle('col-hidden', !visible);
    });

    // ── Body rows ──
    Array.from(sitesBody.querySelectorAll('tr')).forEach(tr => {
        const tds = Array.from(tr.querySelectorAll('td[data-col-td]'));
        colOrder.forEach(({ idx, visible }) => {
            const td = tds.find(t => t.dataset.colTd == idx);
            if (!td) return;
            tr.appendChild(td);
            td.classList.toggle('col-hidden', !visible);
        });
    });

    // Update button badge
    const hidden = colOrder.filter(c => !c.visible).length;
    btnOpenColMgr.classList.toggle('active', hidden > 0);
    btnOpenColMgr.querySelector('svg').nextSibling.textContent = hidden > 0
        ? ` Columns (${TOTAL_COLS - hidden}/${TOTAL_COLS})`
        : ' Columns';

    // Re-sync filter engine col indices (visible non-hidden cols)
    syncFilterColIndices();
}

// ── Drag-and-drop within the drawer list ──
let dragSrc = null;
function initDrag() {
    const items = cmdList.querySelectorAll('.cmd-item');
    items.forEach(item => {
        item.addEventListener('dragstart', e => {
            dragSrc = item;
            item.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
            cmdList.querySelectorAll('.cmd-item').forEach(i => i.classList.remove('drag-over'));
            rebuildColOrderFromDOM();
            applyColumnLayout();
        });
        item.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (item !== dragSrc) {
                cmdList.querySelectorAll('.cmd-item').forEach(i => i.classList.remove('drag-over'));
                item.classList.add('drag-over');
            }
        });
        item.addEventListener('drop', e => {
            e.preventDefault();
            if (dragSrc && dragSrc !== item) {
                // Insert dragSrc before item
                cmdList.insertBefore(dragSrc, item);
            }
            cmdList.querySelectorAll('.cmd-item').forEach(i => i.classList.remove('drag-over'));
        });
    });
}
initDrag();

function rebuildColOrderFromDOM() {
    const items = cmdList.querySelectorAll('.cmd-item');
    colOrder = Array.from(items).map(item => ({
        idx:     parseInt(item.dataset.col, 10),
        visible: item.querySelector('input[data-col-toggle]').checked
    }));
}

// ── Toggle switches ──
cmdList.addEventListener('change', e => {
    if (!e.target.matches('input[data-col-toggle]')) return;
    rebuildColOrderFromDOM();
    // Update label style
    const item  = e.target.closest('.cmd-item');
    const label = item.querySelector('.cmd-item-label');
    label.classList.toggle('hidden-col', !e.target.checked);
    applyColumnLayout();
});

// ── Show all / Hide all ──
cmdShowAll?.addEventListener('click', () => {
    cmdList.querySelectorAll('input[data-col-toggle]').forEach(cb => { cb.checked = true; cb.closest('.cmd-item').querySelector('.cmd-item-label').classList.remove('hidden-col'); });
    rebuildColOrderFromDOM();
    applyColumnLayout();
});
cmdHideAll?.addEventListener('click', () => {
    cmdList.querySelectorAll('input[data-col-toggle]').forEach(cb => { cb.checked = false; cb.closest('.cmd-item').querySelector('.cmd-item-label').classList.add('hidden-col'); });
    rebuildColOrderFromDOM();
    applyColumnLayout();
});

// ── Reset ──
cmdReset?.addEventListener('click', () => {
    // Restore original DOM order in drawer
    const items = Array.from(cmdList.querySelectorAll('.cmd-item'));
    items.sort((a, b) => parseInt(a.dataset.col) - parseInt(b.dataset.col));
    items.forEach(item => {
        cmdList.appendChild(item);
        const cb = item.querySelector('input[data-col-toggle]');
        cb.checked = true;
        item.querySelector('.cmd-item-label').classList.remove('hidden-col');
    });
    initDrag();
    rebuildColOrderFromDOM();
    applyColumnLayout();
});

// ── FILTER ENGINE ─────────────────────────────────────────
const totalRows      = <?= $totalRows ?>;
const visibleCount   = document.getElementById('visibleCount');
const footerCount    = document.getElementById('footerCount');
const noResults      = document.getElementById('noResults');
const activeFiltersEl= document.getElementById('activeFilters');
const btnClearAll    = document.getElementById('btnClearAll');
const tableFilter    = document.getElementById('tableFilter');

const colFilters = {};   // colIndex → Set of excluded values
let   textFilter = '';

// Map from colIndex to its current td-order position (for filter matching)
// Since we reorder TDs we track by data-col-td attribute
function getCellByColIdx(tr, colIdx) {
    return tr.querySelector(`td[data-col-td="${colIdx}"]`);
}

function applyFilters() {
    const rows = Array.from(sitesBody.querySelectorAll('tr'));
    let visible = 0;
    rows.forEach(tr => {
        const textMatch = textFilter === '' || tr.textContent.toLowerCase().includes(textFilter);
        let colMatch = true;
        Object.entries(colFilters).forEach(([colIdx, excluded]) => {
            if (excluded.size === 0) return;
            const cell = getCellByColIdx(tr, colIdx);
            if (!cell) return;
            if (excluded.has(cell.dataset.val ?? '')) colMatch = false;
        });
        const show = textMatch && colMatch;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    if (visibleCount) visibleCount.textContent = visible + ' of ' + totalRows + ' rows';
    if (footerCount)  footerCount.textContent  = visible + ' row' + (visible !== 1 ? 's' : '');
    if (noResults)    noResults.style.display  = visible === 0 ? 'block' : 'none';
    renderChips();
}

// After reordering, col filter still works because we look up by data-col-td, not position
function syncFilterColIndices() { /* no-op: lookup is by attribute */ }

// Text search
const btnClearText = document.getElementById('btnClearText');
function clearTextFilter() {
    tableFilter.value = '';
    textFilter = '';
    tableFilter.closest('.filter-search').classList.remove('has-text');
    applyFilters();
    tableFilter.focus();
}
tableFilter?.addEventListener('input', () => {
    textFilter = tableFilter.value.trim().toLowerCase();
    tableFilter.closest('.filter-search').classList.toggle('has-text', tableFilter.value.length > 0);
    applyFilters();
});
tableFilter?.addEventListener('keydown', e => { if (e.key === 'Escape') clearTextFilter(); });
btnClearText?.addEventListener('click', clearTextFilter);

// Active filter chips
function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function renderChips() {
    activeFiltersEl.innerHTML = '';
    let hasAny = false;
    document.querySelectorAll('.col-filter-btn').forEach(btn => {
        const field  = btn.dataset.field;
        const wrap   = btn.closest('.col-filter-wrap');
        const colIdx = wrap.dataset.colIndex;
        const excl   = colFilters[colIdx];
        if (!excl || excl.size === 0) return;
        hasAny = true;
        const all     = wrap.querySelectorAll('.cfp-item input[type="checkbox"]').length;
        const showing = all - excl.size;
        const chip = document.createElement('span');
        chip.className = 'filter-chip';
        chip.innerHTML = `<strong>${escHtml(field)}:</strong>&nbsp;${showing} of ${all} selected<button type="button" title="Clear">✕</button>`;
        chip.querySelector('button').addEventListener('click', () => clearColFilter(colIdx, wrap));
        activeFiltersEl.appendChild(chip);
    });
    btnClearAll.style.display = hasAny ? 'inline-flex' : 'none';
}

function clearColFilter(colIdx, wrap) {
    delete colFilters[colIdx];
    wrap.querySelectorAll('.cfp-item input[type="checkbox"]').forEach(cb => cb.checked = true);
    updateBtnState(wrap.querySelector('.col-filter-btn'), colIdx);
    applyFilters();
}
btnClearAll?.addEventListener('click', () => {
    Object.keys(colFilters).forEach(idx => {
        const wrap = document.querySelector(`.col-filter-wrap[data-col-index="${idx}"]`);
        if (wrap) clearColFilter(idx, wrap);
    });
});

// Per-column filter panels
let openPanel = null;
function closeAllPanels() {
    document.querySelectorAll('.col-filter-panel.open').forEach(p => {
        p.classList.remove('open');
        p.closest('.col-filter-wrap').querySelector('.col-filter-btn').setAttribute('aria-expanded','false');
    });
    openPanel = null;
}
function updateBtnState(btn, colIdx) {
    const excl    = colFilters[colIdx];
    const hasFil  = excl && excl.size > 0;
    btn.classList.toggle('has-filter', hasFil);
    const wrap = btn.closest('.col-filter-wrap');
    const all  = wrap.querySelectorAll('.cfp-item input[type="checkbox"]').length;
    const show = hasFil ? all - excl.size : all;
    btn.querySelector('.btn-label').textContent = hasFil ? `${show} / ${all}` : 'All';
    btn.querySelector('.filter-count').textContent = hasFil ? excl.size : '';
}
document.querySelectorAll('.col-filter-wrap').forEach(wrap => {
    const btn    = wrap.querySelector('.col-filter-btn');
    const panel  = wrap.querySelector('.col-filter-panel');
    const colIdx = wrap.dataset.colIndex;

    btn.addEventListener('click', e => {
        e.stopPropagation();
        const isOpen = panel.classList.contains('open');
        closeAllPanels();
        if (!isOpen) {
            panel.classList.add('open');
            btn.setAttribute('aria-expanded','true');
            openPanel = panel;
            panel._snapshot = Array.from(panel.querySelectorAll('.cfp-item input[type="checkbox"]')).map(cb => cb.checked);
            panel.querySelector('.cfp-search-input')?.focus();
        }
    });
    const si = panel.querySelector('.cfp-search-input');
    const nm = panel.querySelector('.cfp-no-match');
    si?.addEventListener('input', () => {
        const q = si.value.trim().toLowerCase(); let v = 0;
        panel.querySelectorAll('.cfp-item').forEach(item => {
            const show = q === '' || item.querySelector('label').textContent.toLowerCase().includes(q);
            item.classList.toggle('hidden-by-search', !show);
            if (show) v++;
        });
        if (nm) nm.style.display = v === 0 ? 'block' : 'none';
    });
    panel.querySelector('.cfp-select-all')?.addEventListener('click', () => { panel.querySelectorAll('.cfp-item input[type="checkbox"]').forEach(cb => cb.checked = true); });
    panel.querySelector('.cfp-clear')?.addEventListener('click', () => { panel.querySelectorAll('.cfp-item input[type="checkbox"]').forEach(cb => cb.checked = false); });
    panel.querySelector('.cfp-cancel')?.addEventListener('click', () => {
        if (panel._snapshot) panel.querySelectorAll('.cfp-item input[type="checkbox"]').forEach((cb, i) => cb.checked = panel._snapshot[i]);
        closeAllPanels();
    });
    panel.querySelector('.cfp-apply')?.addEventListener('click', () => {
        const excl = new Set();
        panel.querySelectorAll('.cfp-item input[type="checkbox"]').forEach(cb => { if (!cb.checked) excl.add(cb.value); });
        if (excl.size > 0) colFilters[colIdx] = excl; else delete colFilters[colIdx];
        updateBtnState(btn, colIdx);
        closeAllPanels();
        applyFilters();
        if (si) { si.value = ''; si.dispatchEvent(new Event('input')); }
    });
    panel.addEventListener('click', e => e.stopPropagation());
});
document.addEventListener('click', closeAllPanels);
document.addEventListener('keydown', e => { if (e.key === 'Escape' && !overlay.classList.contains('open')) closeAllPanels(); });

// ── ACTION MODAL ─────────────────────────────────────────────
const actionBackdrop  = document.getElementById('actionModalBackdrop');
const actionModalIcon = document.getElementById('actionModalIcon');
const actionModalTitle= document.getElementById('actionModalTitle');
const actionModalBody = document.getElementById('actionModalBody');
const actionModalMeta = document.getElementById('actionModalMeta');
const actionModalFoot = document.getElementById('actionModalFooter');

function openActionModal(type, rowIndex) {
    // Gather first meaningful cell values for context (skip # and Actions cols)
    const tr    = sitesBody.querySelectorAll('tr')[rowIndex];
    const cells = tr ? Array.from(tr.querySelectorAll('td[data-col-td]')) : [];
    // Build a quick id/label snippet from the first two non-null cells
    const snippets = cells.slice(0, 3).map(td => {
        const th = document.querySelector(`th[data-col-th="${td.dataset.colTd}"]`);
        const label = th ? th.querySelector('.sort-link')?.textContent?.trim() : '';
        const val   = td.dataset.val === '__NULL__' ? 'NULL' : (td.dataset.val || '—');
        return label ? `<strong>${label}:</strong> ${val}` : null;
    }).filter(Boolean);

    const isEdit = type === 'edit';

    // Icon
    actionModalIcon.className = 'action-modal-icon ' + (isEdit ? 'icon-edit' : 'icon-delete');
    actionModalIcon.innerHTML = isEdit
        ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`;

    // Title
    actionModalTitle.textContent = isEdit ? 'Edit' : 'Delete';

    // Body
    actionModalBody.textContent = isEdit
        ? 'You are about to edit this record. Make your changes and save when ready.'
        : 'Are you sure you want to delete this record? This action cannot be undone.';

    // Meta row info
    actionModalMeta.innerHTML = `Row ${rowIndex + 1} &nbsp;·&nbsp; ${snippets.join(' &nbsp;·&nbsp; ')}`;

    // Footer buttons
    actionModalFoot.innerHTML = '';
    const cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'modal-btn modal-btn-secondary';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.addEventListener('click', closeActionModal);

    const confirmBtn = document.createElement('button');
    confirmBtn.type = 'button';
    confirmBtn.className = 'modal-btn ' + (isEdit ? 'modal-btn-edit' : 'modal-btn-delete');
    confirmBtn.innerHTML = isEdit
        ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Save Changes`
        : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Delete`;
    confirmBtn.addEventListener('click', () => {
        // TODO: wire up real edit/delete logic here
        closeActionModal();
    });

    actionModalFoot.appendChild(cancelBtn);
    actionModalFoot.appendChild(confirmBtn);

    actionBackdrop.classList.add('open');
    cancelBtn.focus();
}

function closeActionModal() {
    actionBackdrop.classList.remove('open');
}

// Delegate clicks on action buttons
sitesBody.addEventListener('click', e => {
    const btn = e.target.closest('.action-btn');
    if (!btn) return;
    e.stopPropagation();
    closeYnPopup();
    const action = btn.dataset.action;
    const row    = parseInt(btn.dataset.row, 10);

    if (action === 'edit') {
        // Edit navigates to usermaint.php (shares the site header/flyout menu).
        const tr = sitesBody.querySelectorAll('tr')[row];
        const pk = tr ? tr.dataset.pk : '';
        window.location.href = 'usermaint.php?id=' + encodeURIComponent(pk || '');
        return;
    }

    openActionModal(action, row);
});

// Close on backdrop click or Escape
actionBackdrop.addEventListener('click', e => { if (e.target === actionBackdrop) closeActionModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape' && actionBackdrop.classList.contains('open')) closeActionModal(); });
let ynPopup = null;
let ynActiveCell = null;

function closeYnPopup() {
    if (ynPopup) { ynPopup.remove(); ynPopup = null; }
    ynActiveCell = null;
}

function openYnPopup(cell) {
    closeYnPopup();
    closeAllPanels();

    const field   = cell.dataset.field;
    const current = cell.dataset.val;  // 'Y', 'N', or '__NULL__'

    const popup = document.createElement('div');
    popup.className = 'yn-popup';
    popup.innerHTML = `
        <div class="yn-popup-header">${field}</div>
        <div class="yn-option${current==='Y'?' yn-active':''}" data-pick="Y">
            <span class="yn-dot yn-dot-y"></span> Yes (Y)
        </div>
        <div class="yn-option${current==='N'?' yn-active':''}" data-pick="N">
            <span class="yn-dot yn-dot-n"></span> No (N)
        </div>
        <div class="yn-option${current==='__NULL__'?' yn-active':''}" data-pick="__NULL__">
            <span class="yn-dot yn-dot-null"></span> <em style="color:#b09ed4">NULL</em>
        </div>`;

    // Position below the cell
    const rect = cell.getBoundingClientRect();
    popup.style.top  = (rect.bottom + window.scrollY + 4) + 'px';
    popup.style.left = (rect.left  + window.scrollX)      + 'px';
    document.body.appendChild(popup);
    ynPopup     = popup;
    ynActiveCell = cell;

    // Flip up if clipped at bottom
    requestAnimationFrame(() => {
        const pr = popup.getBoundingClientRect();
        if (pr.bottom > window.innerHeight - 8) {
            popup.style.top = (rect.top + window.scrollY - pr.height - 4) + 'px';
        }
    });

    popup.addEventListener('click', e => {
        const opt = e.target.closest('.yn-option');
        if (!opt) return;
        const pick = opt.dataset.pick;
        applyYnValue(cell, pick);
        closeYnPopup();
    });

    popup.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeYnPopup(); cell.focus(); }
    });

    e.stopPropagation?.();
}

function applyYnValue(cell, pick) {
    // Update display
    let badgeHtml;
    if (pick === '__NULL__') {
        badgeHtml = '<span class="yn-badge yn-null">NULL</span>';
    } else if (pick === 'Y') {
        badgeHtml = '<span class="yn-badge yn-y">Y</span>';
    } else {
        badgeHtml = '<span class="yn-badge yn-n">N</span>';
    }
    cell.innerHTML = badgeHtml;
    cell.dataset.val = pick;
    cell.title = 'Click to change';

    // TODO: persist via fetch/AJAX to update the database row
    // Example:
    // const rowId = cell.closest('tr').dataset.rowId;
    // fetch('update_site.php', {
    //   method: 'POST',
    //   headers: {'Content-Type':'application/json'},
    //   body: JSON.stringify({ id: rowId, field: cell.dataset.field, value: pick === '__NULL__' ? null : pick })
    // });
}

// Delegate click on Y/N cells
sitesBody.addEventListener('click', e => {
    const cell = e.target.closest('td[data-yn="true"]');
    if (!cell) { closeYnPopup(); return; }
    if (ynActiveCell === cell) { closeYnPopup(); return; }
    openYnPopup(cell);
    e.stopPropagation();
});

// Close on outside click
document.addEventListener('click', e => {
    if (ynPopup && !ynPopup.contains(e.target)) closeYnPopup();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeYnPopup();
});
</script>
</body>
</html>
