<?php
$conn = new mysqli('localhost', 'root', '', 'sarkhi sports1');
$self = basename($_SERVER['PHP_SELF']);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM print_history WHERE id = $id");
    $params = array();
    if (!empty($_POST['_view_mode']))  $params['view_mode']  = $_POST['_view_mode'];
    if (!empty($_POST['_filter']))     $params['filter']     = $_POST['_filter'];
    if (!empty($_POST['_from_date']))  $params['from_date']  = $_POST['_from_date'];
    if (!empty($_POST['_to_date']))    $params['to_date']    = $_POST['_to_date'];
    if (!empty($_POST['_month']))      $params['month']      = $_POST['_month'];
    if (!empty($_POST['_year']))       $params['year']       = $_POST['_year'];
    if (!empty($_POST['_search']))     $params['search']     = $_POST['_search'];
    $qs = http_build_query($params);
    header('Location: ' . $self . ($qs ? '?' . $qs : ''));
    exit;
}

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete_ids'])) {
    $ids = $_POST['bulk_delete_ids'];
    $safe_ids = array();
    foreach ($ids as $id) {
        $safe_ids[] = (int)$id;
    }
    if (!empty($safe_ids)) {
        $id_list = implode(',', $safe_ids);
        $conn->query("DELETE FROM print_history WHERE id IN ($id_list)");
    }
    $params = array();
    if (!empty($_POST['_view_mode']))  $params['view_mode']  = $_POST['_view_mode'];
    if (!empty($_POST['_filter']))     $params['filter']     = $_POST['_filter'];
    if (!empty($_POST['_from_date']))  $params['from_date']  = $_POST['_from_date'];
    if (!empty($_POST['_to_date']))    $params['to_date']    = $_POST['_to_date'];
    if (!empty($_POST['_month']))      $params['month']      = $_POST['_month'];
    if (!empty($_POST['_year']))       $params['year']       = $_POST['_year'];
    if (!empty($_POST['_search']))     $params['search']     = $_POST['_search'];
    $qs = http_build_query($params);
    header('Location: ' . $self . ($qs ? '?' . $qs : ''));
    exit;
}

$filter    = isset($_GET['filter'])    ? $_GET['filter']    : 'all';
$search    = isset($_GET['search'])    ? $conn->real_escape_string($_GET['search']) : '';
$view_mode = isset($_GET['view_mode']) ? $_GET['view_mode'] : 'all';

$from_date = isset($_GET['from_date']) && $_GET['from_date'] !== '' ? $_GET['from_date'] : date('Y-m-d');
$to_date   = isset($_GET['to_date'])   && $_GET['to_date']   !== '' ? $_GET['to_date']   : date('Y-m-d');

$sel_month = isset($_GET['month']) && $_GET['month'] !== '' ? (int)$_GET['month'] : (int)date('m');
$sel_year  = isset($_GET['year'])  && $_GET['year']  !== '' ? (int)$_GET['year']  : (int)date('Y');

$where = [];
if ($filter === 'garage')   $where[] = "type = 'garage'";
if ($filter === 'gatepass') $where[] = "type = 'gatepass'";
if ($search !== '')         $where[] = "(party_name LIKE '%$search%' OR no LIKE '%$search%' OR parcel_no LIKE '%$search%' OR garage_name LIKE '%$search%')";

if ($view_mode === 'bydate') {
    $fd = $conn->real_escape_string($from_date);
    $td = $conn->real_escape_string($to_date);
    $where[] = "DATE(created_at) BETWEEN '$fd' AND '$td'";
} elseif ($view_mode === 'bymonth') {
    $where[] = "MONTH(created_at) = $sel_month AND YEAR(created_at) = $sel_year";
}

$sql    = "SELECT * FROM print_history" . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY id DESC";
$result = $conn->query($sql);

$total      = $conn->query("SELECT COUNT(*) FROM print_history")->fetch_row()[0];
$garages    = $conn->query("SELECT COUNT(*) FROM print_history WHERE type='garage'")->fetch_row()[0];
$gatepasses = $conn->query("SELECT COUNT(*) FROM print_history WHERE type='gatepass'")->fetch_row()[0];

$months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June',
           '07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarthi Sports Wear — History</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Share+Tech&display=swap" rel="stylesheet">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    background: #0d1b2e;
    font-family: 'Share Tech Mono', monospace;
    min-height: 100vh;
    padding: 30px 12px;
    color: #c0d4e8;
  }

  .container { max-width: 100%; margin: 0 auto; }

  .topbar {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 16px;
    gap: 10px;
  }

  .dt-box {
    background: #1a2a3e; border: 1px solid #2a3f58;
    padding: 5px 14px; font-size: 20px; color: #b0c4d8; letter-spacing: 1px; font-weight: 700;
  }

  .btn-back {
    background: #1a2a3e; border: 1px solid #00cfee;
    color: #00cfee; font-family: 'Share Tech Mono', monospace;
    font-size: 17px; letter-spacing: 2px; font-weight: 700;
    padding: 5px 14px; cursor: pointer; text-decoration: none;
    display: inline-block; text-transform: uppercase;
  }
  .btn-back:hover { background: #00cfee; color: #0d1b2e; }

  /* ── THREE DOTS: now in topbar ── */
 .btn-dots {
    background: transparent; border: 1px solid #2a3f58;
    color: #a0b4c8; font-size: 22px; font-weight: 700;
    padding: 5px 14px; cursor: pointer; letter-spacing: 3px;
    line-height: 1.4; border-radius: 2px;
    transition: color 0.15s, border-color 0.15s;
    margin-left: auto;
    height: 100%;
    display: inline-flex; align-items: center;
  }
  .btn-dots:hover { color: #ff4444; border-color: #ff4444; }
  .btn-dots.active { color: #ff4444; border-color: #ff4444; background: #1a0808; }

  .card {
    background: #152030; border: 1px solid #1e3245;
    border-radius: 2px; overflow: hidden;
  }

  .card-header {
    background: #152030; padding: 12px 20px;
    display: flex; align-items: center; gap: 8px;
    border-bottom: 1px solid #1e3245;
  }

  .card-header .bolt { color: #f97316; font-size: 22px; }

  .card-header h1 {
    font-size: 20px; font-weight: 700;
    color: #00cfee; letter-spacing: 4px; text-transform: uppercase;
  }

  .card-body { padding: 24px; }

  .stats { display: flex; gap: 12px; margin-bottom: 24px; }

  .stat-box {
    flex: 1; background: #0d1b2e; border: 1px solid #1e3245;
    padding: 14px 18px; text-align: center;
  }

  .stat-num { font-size: 38px; color: #00cfee; letter-spacing: 2px; font-weight: 700; }
  .stat-lbl { font-size: 16px; letter-spacing: 3px; color: #a0b4c8; margin-top: 4px; text-transform: uppercase; font-weight: 700; }

  .stat-box.garage   .stat-num { color: #1db954; }
  .stat-box.gatepass .stat-num { color: #1565c0; }

  .view-tabs {
    display: flex; gap: 0; margin-bottom: 18px;
    border-bottom: 2px solid #1e3245;
    align-items: center;
  }

  .view-tab {
    background: transparent; border: none; border-bottom: 2px solid transparent;
    color: #a0b4c8; font-family: 'Share Tech Mono', monospace;
    font-size: 18px; letter-spacing: 2px; text-transform: uppercase; font-weight: 700;
    padding: 9px 20px; cursor: pointer; text-decoration: none;
    margin-bottom: -2px; display: flex; align-items: center; gap: 7px;
    transition: color 0.15s, border-color 0.15s;
  }
  .view-tab:hover { color: #00cfee; }
  .view-tab.active { color: #00cfee; border-bottom: 2px solid #00cfee; background: #0d1b2e; }
  .view-tab .tab-icon { font-size: 17px; }

  .date-bar {
    display: flex; gap: 14px; align-items: flex-end;
    margin-bottom: 20px; flex-wrap: wrap;
    background: #0d1b2e; border: 1px solid #1e3245;
    padding: 14px 18px;
  }

  .date-field { display: flex; flex-direction: column; gap: 5px; }

  .date-field label {
    font-size: 16px; letter-spacing: 2px; color: #a0b4c8; text-transform: uppercase; font-weight: 700;
  }

  .date-field input[type="date"],
  .date-field input[type="number"],
  .date-field select {
    background: #152030; border: 1px solid #2a3f58; color: #c0d4e8;
    font-family: 'Share Tech Mono', monospace; font-size: 18px; font-weight: 700;
    padding: 7px 10px; outline: none; cursor: pointer;
    color-scheme: dark;
  }
  .date-field input[type="date"]:focus,
  .date-field input[type="number"]:focus,
  .date-field select:focus { border-color: #00cfee; }
  .date-field select option { background: #152030; }

  .btn-show {
    background: #1db954; border: none; color: #0d1b2e;
    font-family: 'Share Tech Mono', monospace; font-size: 16px; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: 9px 20px; cursor: pointer; align-self: flex-end;
    transition: background 0.15s;
  }
  .btn-show:hover { background: #17a347; }

  .date-range-label {
    margin-left: auto; align-self: flex-end;
    background: #1a2a3e; border: 1px solid #f97316;
    padding: 6px 14px; font-size: 16px; color: #f97316; letter-spacing: 1px; font-weight: 700;
    white-space: nowrap;
  }

  .toolbar {
    display: flex; gap: 10px; margin-bottom: 20px;
    flex-wrap: wrap; align-items: center;
  }

  .filter-btn {
    background: #0d1b2e; border: 1px solid #1e3245;
    color: #a0b4c8; font-family: 'Share Tech Mono', monospace;
    font-size: 18px; letter-spacing: 2px; font-weight: 700;
    padding: 7px 16px; cursor: pointer; text-transform: uppercase;
    text-decoration: none;
  }
  .filter-btn:hover, .filter-btn.active { border-color: #00cfee; color: #00cfee; }
  .filter-btn.active { background: #0a1a2e; }
  .filter-btn.gc-active  { border-color: #1db954; color: #1db954; background: #0a1e10; }
  .filter-btn.gp-active  { border-color: #1565c0; color: #6ab0ff; background: #0a1428; }

  .search-wrap { flex: 1; min-width: 200px; }
  .search-wrap form { display: flex; gap: 8px; align-items: center; }

  .search-input-wrap { flex: 1; position: relative; display: flex; }

  .search-wrap input[type="text"] {
    flex: 1; background: #0d1b2e; border: 1px solid #1e3245;
    color: #c0d4e8; font-family: 'Share Tech Mono', monospace;
    font-size: 18px; padding: 7px 36px 7px 12px; outline: none; font-weight: 700;
    width: 100%;
  }
  .search-wrap input[type="text"]:focus { border-color: #00cfee; }
  .search-wrap input[type="text"]::placeholder { color: #1e3450; }

  .btn-clear-search {
    position: absolute; right: 8px; top: 50%;
    transform: translateY(-50%);
    background: none; border: none; color: #ff6666;
    font-size: 20px; font-weight: 700; cursor: pointer;
    line-height: 1; padding: 2px 4px;
    display: none;
  }
  .btn-clear-search:hover { color: #ff4444; }
  .btn-clear-search.visible { display: block; }

  .btn-search {
    background: #1a2a3e; border: 1px solid #2a3f58; color: #00cfee;
    font-family: 'Share Tech Mono', monospace; font-size: 17px; font-weight: 700;
    padding: 7px 14px; cursor: pointer; letter-spacing: 1px;
  }
  .btn-search:hover { border-color: #00cfee; }

  /* ── BULK DELETE BAR: styled like reference image ── */
  .bulk-bar {
    display: none;
    background: #0a1520; border: 1px solid #1e3245;
    border-left: 4px solid #ff4444;
    padding: 14px 20px; margin-bottom: 16px;
    align-items: center; gap: 16px; flex-wrap: wrap;
    animation: slideDown 0.2s ease;
  }
  .bulk-bar.show { display: flex; }
  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .bulk-select-all {
    display: flex; align-items: center; gap: 7px;
    font-size: 16px; letter-spacing: 1px; color: #c0d4e8; font-weight: 700; cursor: pointer;
    background: #152030; border: 1px solid #2a3f58;
    padding: 6px 14px; border-radius: 2px;
    transition: border-color 0.15s;
  }
  .bulk-select-all:hover { border-color: #00cfee; color: #00cfee; }

  .bulk-count {
    font-size: 16px; letter-spacing: 1px; color: #a0b4c8; font-weight: 700;
  }
  .bulk-count strong { color: #00cfee; }

  .btn-bulk-del {
    background: #ff4444; border: none; color: #fff;
    font-family: 'Share Tech Mono', monospace;
    font-size: 15px; letter-spacing: 2px; font-weight: 700;
    padding: 8px 18px; cursor: pointer; text-transform: uppercase;
    border-radius: 2px; transition: background 0.15s;
  }
  .btn-bulk-del:hover { background: #cc2222; }
  .btn-bulk-del:disabled { opacity: 0.35; cursor: not-allowed; background: #884444; }

  .btn-cancel-select {
    background: transparent; border: 1px solid #1e3245; color: #a0b4c8;
    font-family: 'Share Tech Mono', monospace;
    font-size: 15px; letter-spacing: 2px; font-weight: 700;
    padding: 7px 14px; cursor: pointer; text-transform: uppercase;
    border-radius: 2px; transition: all 0.15s;
  }
  .btn-cancel-select:hover { border-color: #00cfee; color: #00cfee; }

  /* Checkbox column */
  .col-check { width: 36px; }
  .row-checkbox { width: 16px; height: 16px; cursor: pointer; accent-color: #ff4444; }
  .col-check-th { display: none; }
  body.select-mode .col-check-th { display: table-cell; }
  body.select-mode .col-check-td { display: table-cell; }
  .col-check-td { display: none; }

  .tbl-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; }
  thead tr { border-bottom: 2px solid #1e3245; }

  th {
    font-size: 16px; letter-spacing: 3px; color: #a0b4c8;
    text-transform: uppercase; padding: 10px 14px; text-align: left;
    white-space: nowrap; font-weight: 700;
  }

  tbody tr { border-bottom: 1px solid #1a2a3a; transition: background 0.15s; }
  tbody tr:hover { background: #0d1e30; }
  tbody tr.selected-row { background: #0d1e30 !important; }
  td { padding: 12px 14px; font-size: 18px; letter-spacing: 1px; vertical-align: middle; font-weight: 700; }

  .badge {
    display: inline-block; padding: 3px 10px;
    font-size: 14px; letter-spacing: 2px; text-transform: uppercase; font-weight: 700;
  }
  .badge.garage   { background: rgba(29,185,84,0.15); border: 1px solid #1db954; color: #1db954; }
  .badge.gatepass { background: rgba(21,101,192,0.2); border: 1px solid #6ab0ff; color: #6ab0ff; }

  .td-no   { color: #00cfee; }
  .td-date { color: #e0eaf5; font-size: 17px; }
  .td-dim  { color: #c0d4e8; }

  .btn-del {
    background: transparent; border: 1px solid #3a2020; color: #884444;
    font-family: 'Share Tech Mono', monospace; font-size: 16px; font-weight: 700;
    padding: 4px 10px; cursor: pointer; letter-spacing: 1px;
  }
  .btn-del:hover { border-color: #ff4444; color: #ff6666; background: #1a0a0a; }

  .empty {
    text-align: center; padding: 60px 20px;
    font-size: 17px; letter-spacing: 3px; color: #2a4060; text-transform: uppercase; font-weight: 700;
  }

  .del-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.75); backdrop-filter: blur(4px);
    z-index: 1000; align-items: center; justify-content: center;
  }
  .del-modal-overlay.show { display: flex; }

  .del-modal-box {
    background: #152030; border: 1px solid #ff4444;
    padding: 36px 40px; text-align: center;
    min-width: 320px; max-width: 420px;
    animation: delModalIn 0.2s ease; position: relative;
  }

  @keyframes delModalIn {
    from { transform: scale(0.85); opacity: 0; }
    to   { transform: scale(1);    opacity: 1; }
  }

  .del-modal-icon { font-size: 52px; margin-bottom: 14px; display: block; }

  .del-modal-title {
    font-family: 'Share Tech Mono', monospace;
    font-size: 22px; letter-spacing: 4px; font-weight: 700;
    color: #ff4444; text-transform: uppercase; margin-bottom: 10px;
  }

  .del-modal-msg {
    font-family: 'Share Tech Mono', monospace;
    font-size: 18px; color: #4a6a88; font-weight: 700;
    letter-spacing: 2px; margin-bottom: 28px; line-height: 1.8;
  }

  .del-modal-actions { display: flex; gap: 12px; justify-content: center; }

  .del-btn-confirm {
    background: #ff4444; border: none; color: #fff;
    font-family: 'Share Tech Mono', monospace;
    font-size: 17px; letter-spacing: 3px; font-weight: 700;
    padding: 11px 28px; cursor: pointer; text-transform: uppercase;
    transition: background 0.15s;
  }
  .del-btn-confirm:hover { background: #cc2222; }

  .del-btn-cancel {
    background: #1e3245; border: 1px solid #2a4460; color: #6a8aaa;
    font-family: 'Share Tech Mono', monospace;
    font-size: 17px; letter-spacing: 3px; font-weight: 700;
    padding: 11px 28px; cursor: pointer; text-transform: uppercase;
    transition: all 0.15s;
  }
  .del-btn-cancel:hover { border-color: #00cfee; color: #00cfee; }

  .del-modal-corner {
    position: absolute; width: 10px; height: 10px;
    border-color: #ff4444; border-style: solid;
  }
  .del-modal-corner.tl { top: -1px; left: -1px;  border-width: 2px 0 0 2px; }
  .del-modal-corner.tr { top: -1px; right: -1px; border-width: 2px 2px 0 0; }
  .del-modal-corner.bl { bottom: -1px; left: -1px;  border-width: 0 0 2px 2px; }
  .del-modal-corner.br { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

  .btn-print {
    background: transparent; border: 1px solid #1a3a5a; color: #4a7a9a;
    font-family: 'Share Tech Mono', monospace; font-size: 16px; font-weight: 700;
    padding: 4px 10px; cursor: pointer; letter-spacing: 1px;
  }
  .btn-print:hover { border-color: #00cfee; color: #00cfee; background: #0a1e30; }
</style>
</head>
<body>
<div class="container">

  <div class="topbar">
    <div class="dt-box">📋 HISTORY</div>
    <!-- ⋯ button moved here to top right corner -->
   <button class="btn-dots" id="btnDots" onclick="toggleSelectMode()" title="Select records to delete" style="height:35px; padding: 0 14px; display:inline-flex; align-items:center;">⋯</button>
<a href="garagecopy.php" class="btn-back" style="height:35px; padding: 0 14px; display:inline-flex; align-items:center;">← BACK</a>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="bolt">⚡</span>
      <h1>Sarthi Sports Wear — History</h1>
    </div>
    <div class="card-body">

      <!-- BULK DELETE BAR — now appears right at the top of card-body -->
      <div class="bulk-bar" id="bulkBar">
        <label class="bulk-select-all">
          <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"> &#9635; Select All
        </label>
        <button class="btn-cancel-select" onclick="toggleSelectAllBtn(false)">&#9633; Deselect All</button>
        <span class="bulk-count">Selected: <strong id="bulkCount">0</strong></span>
        <button class="btn-bulk-del" id="btnBulkDel" onclick="confirmBulkDelete()" disabled>&#128465;&#65039; Delete Selected</button>
        <button class="btn-cancel-select" style="margin-left:auto;" onclick="toggleSelectMode()">✕ Cancel</button>
      </div>

      <div class="stats">
        <div class="stat-box">
          <div class="stat-num"><?= $total ?></div>
          <div class="stat-lbl">Total Records</div>
        </div>
        <div class="stat-box garage">
          <div class="stat-num"><?= $garages ?></div>
          <div class="stat-lbl">Garage Copies</div>
        </div>
        <div class="stat-box gatepass">
          <div class="stat-num"><?= $gatepasses ?></div>
          <div class="stat-lbl">Gate Passes</div>
        </div>
      </div>

      <!-- VIEW MODE TABS (⋯ button removed from here) -->
      <div class="view-tabs">
        <a href="<?= $self ?>?filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>&view_mode=all"
           class="view-tab <?= $view_mode === 'all' ? 'active' : '' ?>">
          <span class="tab-icon">☰</span> All
        </a>
        <a href="<?= $self ?>?filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>&view_mode=bydate&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>"
           class="view-tab <?= $view_mode === 'bydate' ? 'active' : '' ?>">
          <span class="tab-icon">📅</span> By Date
        </a>
        <a href="<?= $self ?>?filter=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>&view_mode=bymonth&month=<?= $sel_month ?>&year=<?= $sel_year ?>"
           class="view-tab <?= $view_mode === 'bymonth' ? 'active' : '' ?>">
          <span class="tab-icon">🗓</span> By Month
        </a>
      </div>

      <!-- DATE RANGE BAR (By Date) -->
      <?php if ($view_mode === 'bydate'): ?>
      <form method="GET" action="<?= $self ?>">
        <input type="hidden" name="view_mode" value="bydate">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <div class="date-bar">
          <div class="date-field">
            <label>From Date</label>
            <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>">
          </div>
          <div class="date-field">
            <label>To Date</label>
            <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>">
          </div>
          <button type="submit" class="btn-show">▶ Show</button>
          <div class="date-range-label">
            <?= date('d/m/Y', strtotime($from_date)) ?> → <?= date('d/m/Y', strtotime($to_date)) ?>
          </div>
        </div>
      </form>
      <?php endif; ?>

      <!-- MONTH/YEAR BAR (By Month) -->
      <?php if ($view_mode === 'bymonth'): ?>
      <form method="GET" action="<?= $self ?>">
        <input type="hidden" name="view_mode" value="bymonth">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <div class="date-bar">
          <div class="date-field">
            <label>Month</label>
            <select name="month">
              <?php foreach ($months as $num => $name): ?>
                <option value="<?= (int)$num ?>" <?= $sel_month === (int)$num ? 'selected' : '' ?>><?= $name ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="date-field">
            <label>Year</label>
            <input type="number" name="year" value="<?= $sel_year ?>" min="2000" max="2099" style="width:90px;">
          </div>
          <button type="submit" class="btn-show">▶ Show</button>
          <div class="date-range-label">
            <?= $months[str_pad($sel_month, 2, '0', STR_PAD_LEFT)] ?> <?= $sel_year ?>
          </div>
        </div>
      </form>
      <?php endif; ?>

      <!-- TOOLBAR (type filters + search) -->
      <div class="toolbar">
        <a href="<?= $self ?>?view_mode=<?= $view_mode ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&month=<?= $sel_month ?>&year=<?= $sel_year ?>&search=<?= urlencode($search) ?>"
           class="filter-btn <?= $filter==='all' ? 'active' : '' ?>">All</a>

        <a href="<?= $self ?>?filter=garage&view_mode=<?= $view_mode ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&month=<?= $sel_month ?>&year=<?= $sel_year ?>&search=<?= urlencode($search) ?>"
           class="filter-btn <?= $filter==='garage' ? 'gc-active' : '' ?>">Garage Copy</a>

        <a href="<?= $self ?>?filter=gatepass&view_mode=<?= $view_mode ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&month=<?= $sel_month ?>&year=<?= $sel_year ?>&search=<?= urlencode($search) ?>"
           class="filter-btn <?= $filter==='gatepass' ? 'gp-active' : '' ?>">Gate Pass</a>

        <div class="search-wrap">
          <form method="GET" action="<?= $self ?>" id="searchForm">
            <input type="hidden" name="view_mode"  value="<?= htmlspecialchars($view_mode) ?>">
            <input type="hidden" name="filter"     value="<?= htmlspecialchars($filter) ?>">
            <input type="hidden" name="from_date"  value="<?= htmlspecialchars($from_date) ?>">
            <input type="hidden" name="to_date"    value="<?= htmlspecialchars($to_date) ?>">
            <input type="hidden" name="month"      value="<?= $sel_month ?>">
            <input type="hidden" name="year"       value="<?= $sel_year ?>">
            <div class="search-input-wrap">
              <input type="text" name="search" id="searchInput" placeholder="Search party, no, parcel..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
              <button type="button" class="btn-clear-search <?= $search !== '' ? 'visible' : '' ?>" id="btnClearSearch" onclick="clearSearch()" title="Clear search">✕</button>
            </div>
            <button type="submit" class="btn-search">🔍</button>
          </form>
        </div>
      </div>

      <!-- BULK DELETE FORM (hidden) -->
      <form method="POST" action="<?= $self ?>" id="bulkDeleteForm">
        <input type="hidden" name="_view_mode"  value="<?= htmlspecialchars($view_mode) ?>">
        <input type="hidden" name="_filter"     value="<?= htmlspecialchars($filter) ?>">
        <input type="hidden" name="_from_date"  value="<?= htmlspecialchars($from_date) ?>">
        <input type="hidden" name="_to_date"    value="<?= htmlspecialchars($to_date) ?>">
        <input type="hidden" name="_month"      value="<?= $sel_month ?>">
        <input type="hidden" name="_year"       value="<?= $sel_year ?>">
        <input type="hidden" name="_search"     value="<?= htmlspecialchars($search) ?>">
        <div id="bulkIdsContainer"></div>
      </form>

      <!-- TABLE -->
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr>
              <th class="col-check-th"></th>
              <th>#</th>
              <th>Type</th>
              <th>No.</th>
              <th>Date</th>
              <th>Party Name</th>
              <th>Parcel No.</th>
              <th>Garage Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
              <tr data-id="<?= $row['id'] ?>">
                <td class="col-check-td">
                  <input type="checkbox" class="row-checkbox" value="<?= $row['id'] ?>" onchange="updateBulkCount()">
                </td>
                <td class="td-dim"><?= $i++ ?></td>
                <td><span class="badge <?= $row['type'] ?>"><?= $row['type'] === 'garage' ? 'Garage Copy' : 'Gate Pass' ?></span></td>
                <td class="td-no"><?= htmlspecialchars($row['no']) ?></td>
                <td class="td-date"><?= ($row['date'] && $row['date'] !== '0000-00-00') ? htmlspecialchars($row['date']) : date('d-m-Y', strtotime($row['created_at'])) ?></td>
                <td><?= htmlspecialchars($row['party_name']) ?></td>
                <td class="td-dim"><?= htmlspecialchars($row['parcel_no']) ?></td>
                <td class="td-dim"><?= htmlspecialchars($row['garage_name']) ?></td>
                <td>
                  <div style="display:flex; gap:6px; align-items:center;">
                    <form method="POST" action="<?= $self ?>" data-delete="true">
                      <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                      <input type="hidden" name="_view_mode"  value="<?= htmlspecialchars($view_mode) ?>">
                      <input type="hidden" name="_filter"     value="<?= htmlspecialchars($filter) ?>">
                      <input type="hidden" name="_from_date"  value="<?= htmlspecialchars($from_date) ?>">
                      <input type="hidden" name="_to_date"    value="<?= htmlspecialchars($to_date) ?>">
                      <input type="hidden" name="_month"      value="<?= $sel_month ?>">
                      <input type="hidden" name="_year"       value="<?= $sel_year ?>">
                      <input type="hidden" name="_search"     value="<?= htmlspecialchars($search) ?>">
                      <button type="submit" class="btn-del">✕ DEL</button>
                    </form>
                    <button class="btn-print" onclick="printRow(
                        '<?= addslashes($row['type']) ?>',
                        '<?= addslashes(($row['date'] && $row['date'] !== '0000-00-00') ? $row['date'] : date('d-m-Y', strtotime($row['created_at']))) ?>',
                        '<?= addslashes($row['no']) ?>',
                        '<?= addslashes($row['party_name']) ?>',
                        '<?= addslashes($row['parcel_no']) ?>',
                        '<?= addslashes($row['garage_name']) ?>'
                      )">🖨️</button>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="9" class="empty">— No records found —</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<!-- Delete Confirm Modal -->
<div class="del-modal-overlay" id="delModal">
  <div class="del-modal-box">
    <div class="del-modal-corner tl"></div>
    <div class="del-modal-corner tr"></div>
    <div class="del-modal-corner bl"></div>
    <div class="del-modal-corner br"></div>
    <span class="del-modal-icon">🗑️</span>
    <div class="del-modal-title" id="delModalTitle">Delete Record</div>
    <div class="del-modal-msg" id="delModalMsg">This action cannot be undone.<br>Are you sure you want to delete?</div>
    <div class="del-modal-actions">
      <button class="del-btn-confirm" id="delConfirmBtn">✕ DELETE</button>
      <button class="del-btn-cancel" onclick="closeDelModal()">↺ CANCEL</button>
    </div>
  </div>
</div>

<script>
  // ── Browser back button → home.php ──
  (function() {
    history.replaceState({ page: 'index' }, '', 'index.php');
    history.pushState({ page: 'ghistory' }, '', 'ghistory.php');
    window.addEventListener('popstate', function(e) {
      window.location.href = 'home.php';
    });
  })();

  // ── Single delete ──
  let pendingDeleteForm = null;

  document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.getAttribute('data-delete') === 'true') {
      e.preventDefault();
      pendingDeleteForm = form;
      document.getElementById('delModalTitle').textContent = 'Delete Record';
      document.getElementById('delModalMsg').innerHTML = 'This action cannot be undone.<br>Are you sure you want to delete?';
      document.getElementById('delConfirmBtn').onclick = function() {
        if (pendingDeleteForm) {
          pendingDeleteForm.removeAttribute('data-delete');
          pendingDeleteForm.submit();
        }
      };
      document.getElementById('delModal').classList.add('show');
    }
  });

  function closeDelModal() {
    document.getElementById('delModal').classList.remove('show');
    pendingDeleteForm = null;
  }

  document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('delModal');
    if (!modal.classList.contains('show')) return;
    if (e.key === 'Enter')  { e.preventDefault(); document.getElementById('delConfirmBtn').click(); }
    if (e.key === 'Escape') { closeDelModal(); }
  });

  // ── Select mode (three dots) ──
  var selectModeOn = false;

  function toggleSelectMode() {
    selectModeOn = !selectModeOn;
    document.body.classList.toggle('select-mode', selectModeOn);
    document.getElementById('bulkBar').classList.toggle('show', selectModeOn);
    document.getElementById('btnDots').classList.toggle('active', selectModeOn);

    if (!selectModeOn) {
      document.querySelectorAll('.row-checkbox').forEach(function(cb) { cb.checked = false; });
      document.getElementById('selectAll').checked = false;
      updateBulkCount();
      document.querySelectorAll('tbody tr').forEach(function(tr) { tr.classList.remove('selected-row'); });
    }
  }

  // ── Deselect All button ──
  function toggleSelectAllBtn(val) {
    document.querySelectorAll('.row-checkbox').forEach(function(cb) {
      cb.checked = val;
      var tr = cb.closest('tr');
      if (tr) tr.classList.toggle('selected-row', val);
    });
    document.getElementById('selectAll').checked = val;
    updateBulkCount();
  }

  function toggleSelectAll(master) {
    document.querySelectorAll('.row-checkbox').forEach(function(cb) {
      cb.checked = master.checked;
      var tr = cb.closest('tr');
      if (tr) tr.classList.toggle('selected-row', master.checked);
    });
    updateBulkCount();
  }

  function updateBulkCount() {
    var checked = document.querySelectorAll('.row-checkbox:checked').length;
    document.getElementById('bulkCount').textContent = checked;
    document.getElementById('btnBulkDel').disabled = checked === 0;

    document.querySelectorAll('.row-checkbox').forEach(function(cb) {
      var tr = cb.closest('tr');
      if (tr) tr.classList.toggle('selected-row', cb.checked);
    });

    var total = document.querySelectorAll('.row-checkbox').length;
    document.getElementById('selectAll').checked = total > 0 && checked === total;
  }

  function confirmBulkDelete() {
    var checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length === 0) return;

    document.getElementById('delModalTitle').textContent = 'Delete ' + checked.length + ' Records';
    document.getElementById('delModalMsg').innerHTML = 'This will delete <strong style="color:#ff6666">' + checked.length + '</strong> record(s).<br>This action cannot be undone.';
    document.getElementById('delConfirmBtn').onclick = function() {
      var container = document.getElementById('bulkIdsContainer');
      container.innerHTML = '';
      checked.forEach(function(cb) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'bulk_delete_ids[]';
        inp.value = cb.value;
        container.appendChild(inp);
      });
      document.getElementById('bulkDeleteForm').submit();
    };
    document.getElementById('delModal').classList.add('show');
  }

  // ── Clear search ──
  var searchInput = document.getElementById('searchInput');
  var btnClear    = document.getElementById('btnClearSearch');

  searchInput.addEventListener('input', function() {
    btnClear.classList.toggle('visible', this.value.length > 0);
  });

  function clearSearch() {
    searchInput.value = '';
    btnClear.classList.remove('visible');
    document.getElementById('searchForm').submit();
  }
</script>

<script>
function printRow(type, date, no, party, parcel, garage) {
  const label = type === 'gatepass' ? 'GATE PASS' : 'GARAGE COPY';
  const html = `<!DOCTYPE html><html><head>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Share+Tech&display=swap" rel="stylesheet">
    <style>
      * { margin:0; padding:0; box-sizing:border-box; }
      @page { margin:0; size:A4 portrait; }
      body { font-family: Arial, Helvetica, sans-serif; }
    </style>
  </head><body>
    <div style="font-family:Arial,Helvetica,sans-serif; padding:14mm 18mm; color:#000;">

      <div style="text-align:center; font-size:28px; font-weight:900; letter-spacing:4px; text-transform:uppercase; font-family:Arial,Helvetica,sans-serif; color:#000; padding:8px 0; margin-bottom:14px;">${label}</div>

      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <div>
          <div style="font-family:Arial,Helvetica,sans-serif; font-size:36px; font-weight:900; color:#000;">Sarthi Sports Wear</div>
          <div style="font-family:Arial,Helvetica,sans-serif; font-size:17px; color:#000; margin-top:4px; font-weight:700;">Ph: 7620425141 | 9422107750</div>
        </div>
        <div style="text-align:right;">
          <div style="font-family:Arial,Helvetica,sans-serif; font-size:14px; letter-spacing:2px; font-weight:900; text-transform:uppercase; margin-bottom:4px;">Date</div>
          <div style="font-family:Arial,Helvetica,sans-serif; font-size:26px; font-weight:900; color:#000;">${date}</div>
        </div>
      </div>

      <table style="width:100%; border-collapse:collapse; border:2.5px solid #000;">
        <tr>
          <td colspan="2" style="padding:10px 14px 18px; border-bottom:1.5px solid #000;">
            <div style="font-family:Arial,Helvetica,sans-serif; font-size:18px; letter-spacing:2px; margin-bottom:6px; text-transform:uppercase; font-weight:900;">No.</div>
            <div style="font-family:Arial,Helvetica,sans-serif; font-size:30px; border-bottom:2.5px solid #000; padding-bottom:2px; font-weight:900;">${no}</div>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:10px 14px 18px; border-bottom:1.5px solid #000;">
            <div style="font-family:Arial,Helvetica,sans-serif; font-size:18px; letter-spacing:2px; margin-bottom:6px; text-transform:uppercase; font-weight:900;">Party Name</div>
            <div style="font-family:Arial,Helvetica,sans-serif; font-size:30px; border-bottom:2.5px solid #000; padding-bottom:2px; font-weight:900;">${party}</div>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:10px 14px 18px; border-bottom:1.5px solid #000;">
            <div style="font-family:Arial,Helvetica,sans-serif; font-size:18px; letter-spacing:2px; margin-bottom:6px; text-transform:uppercase; font-weight:900;">Garage Name</div>
            <div style="font-family:Arial,Helvetica,sans-serif; font-size:30px; border-bottom:2.5px solid #000; padding-bottom:2px; font-weight:900;">${garage}</div>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:10px 14px 18px;">
            <div style="font-family:Arial,Helvetica,sans-serif; font-size:18px; letter-spacing:2px; margin-bottom:6px; text-transform:uppercase; font-weight:900;">Parcel No.</div>
            <div style="font-family:Arial,Helvetica,sans-serif; font-size:30px; border-bottom:2.5px solid #000; padding-bottom:2px; font-weight:900;">${parcel}</div>
          </td>
        </tr>
      </table>

      <div style="margin-top:28px; text-align:right; padding-right:20px;">
        <div style="display:inline-block; width:260px; text-align:center;">
          <div style="border-bottom:2px solid #000; height:50px; margin-bottom:6px;"></div>
          <div style="font-family:Arial,Helvetica,sans-serif; font-size:13px; letter-spacing:2px; font-weight:900; text-transform:uppercase;">Authorized Signature</div>
        </div>
      </div>

      <div style="margin-top:40px; border-top:1.5px solid #000; padding-top:8px; display:flex; justify-content:space-between; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#000; letter-spacing:1.5px; text-transform:uppercase; font-weight:700;">
        <div>
          <div>Sarthi Sports Wear</div>
          <div>${no}</div>
        </div>
      </div>
    </div>
  </body></html>`;

  const iframe = document.createElement('iframe');
  iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none;';
  document.body.appendChild(iframe);
  iframe.contentDocument.open();
  iframe.contentDocument.write(html);
  iframe.contentDocument.close();
  iframe.contentWindow.focus();
  setTimeout(() => {
    iframe.contentWindow.print();
    setTimeout(() => document.body.removeChild(iframe), 1000);
  }, 800);
}
</script>
</body>
</html>
<?php $conn->close(); ?>