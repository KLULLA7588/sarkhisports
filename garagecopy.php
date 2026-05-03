<?php
// ─── DB CONFIG ────────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');                 // Change if you set a root password
define('DB_NAME', 'sarkhi sports1');   // Kept exactly as your original DB name

// ─── SAVE HANDLER ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');

        $stmt = $conn->prepare(
            "INSERT INTO `print_history` (`type`, `date`, `no`, `party_name`, `parcel_no`, `garage_name`)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $type        = isset($_POST['type'])        ? $_POST['type']        : '';
        $date        = isset($_POST['date'])        ? $_POST['date']        : '';
        $no          = isset($_POST['no'])          ? $_POST['no']          : '';
        $party_name  = isset($_POST['party_name'])  ? $_POST['party_name']  : '';
        $parcel_no   = isset($_POST['parcel_no'])   ? $_POST['parcel_no']   : '';
        $garage_name = isset($_POST['garage_name']) ? $_POST['garage_name'] : '';

        $stmt->bind_param('ssssss', $type, $date, $no, $party_name, $parcel_no, $garage_name);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        echo 'ok';
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);
        echo 'error: ' . $e->getMessage();
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarthi Sports Wear — Garage Copy</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Share+Tech&display=swap" rel="stylesheet">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    background: #0d1b2e;
    font-family: 'Share Tech Mono', monospace;
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 20px;
  }

  .topright {
    width: 100%; max-width: 1040px;
    display: flex; justify-content: space-between;
    align-items: center;
    gap: 8px; margin-bottom: 8px;
    flex-shrink: 0;
  }

  .dt-boxes { display: flex; gap: 8px; }

  .dt-box {
    background: #1a2a3e; border: 1px solid #2a3f58;
    padding: 5px 16px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 20px; color: #ffffff; letter-spacing: 1px; font-weight: 700;
  }

  .btn-history {
    background: #1a2a3e; border: 1px solid #00cfee;
    color: #00cfee; font-family: 'Share Tech Mono', monospace;
    font-size: 19px; letter-spacing: 2px; font-weight: 700;
    padding: 5px 16px; cursor: pointer; text-transform: uppercase;
    text-decoration: none; display: inline-block;
  }
  .btn-history:hover { background: #00cfee; color: #0d1b2e; }

  .card {
    width: 100%; max-width: 1040px;
    background: #152030; border: 1px solid #1e3245;
    border-radius: 2px; overflow: hidden;
    flex-shrink: 0;
  }

  .card-header {
    background: #152030; padding: 10px 28px;
    display: flex; align-items: center; gap: 10px;
    border-bottom: 1px solid #1e3245;
  }

  .card-header .bolt { color: #f97316; font-size: 23px; }

  .card-header h1 {
    font-family: 'Share Tech Mono', monospace;
    font-size: 23px; font-weight: 700;
    color: #00cfee; letter-spacing: 4px; text-transform: uppercase;
  }

  .card-body { padding: 16px 32px 18px; }

  .company-row {
    display: flex; justify-content: space-between;
    align-items: flex-start; margin-bottom: 14px;
  }

  .company-name {
    font-family: 'Share Tech', sans-serif;
    font-size: 42px; color: #ffffff; letter-spacing: 1px; font-weight: 700;
  }

  .company-meta {
    font-family: 'Share Tech Mono', monospace;
    font-size: 19px; color: #e8f4ff; margin-top: 4px; letter-spacing: 1px; font-weight: 700;
  }

  .gc-no-box {
    background: #1565c0; border: 1px solid #00cfee;
    padding: 10px 22px; text-align: center; min-width: 200px;
  }

  .gc-no-label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 17px; letter-spacing: 3px; color: #ffffff; text-transform: uppercase; font-weight: 700;
  }

  .gc-no-val {
    font-family: 'Share Tech Mono', monospace;
    font-size: 32px; color: #fff; margin-top: 5px;
    border: none; background: transparent;
    width: 100%; text-align: center; outline: none; letter-spacing: 2px;
    cursor: default; pointer-events: none; user-select: none; font-weight: 700;
  }

  .gc-no-val::placeholder { color: rgba(255,255,255,0.35); }

  .section-label {
    display: flex; align-items: center; gap: 6px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 18px; letter-spacing: 3px; color: #00cfee;
    margin-bottom: 10px; text-transform: uppercase; font-weight: 700;
  }

  .section-label::before { content: '▶'; font-size: 11px; }

  .field-row { display: flex; gap: 16px; margin-bottom: 10px; }
  .field { display: flex; flex-direction: column; flex: 1; }

  .field label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 18px; letter-spacing: 2px; color: #ddeeff;
    margin-bottom: 5px; text-transform: uppercase; font-weight: 700;
  }

  .field input {
    background: #0d1b2e; border: 1px solid #1e3245;
    color: #ffffff; font-family: 'Share Tech Mono', monospace;
    font-size: 22px; padding: 9px 13px; outline: none;
    width: 100%; letter-spacing: 1px; font-weight: 700;
  }

  .field input:focus { border-color: #00cfee; background: #0a1624; }
  .field input::placeholder { color: #8aaac8; font-size: 19px; }
  .field input[type="date"] { color-scheme: dark; cursor: pointer; }

  .no-input-wrap {
    display: flex; align-items: center;
    background: #0d1b2e; border: 1px solid #1e3245;
    padding: 9px 13px;
  }
  .no-input-wrap:focus-within { border-color: #00cfee; background: #0a1624; }

  .no-prefix {
    color: #00cfee;
    font-family: 'Share Tech Mono', monospace;
    font-size: 22px; letter-spacing: 1px;
    user-select: none; flex-shrink: 0; font-weight: 700;
  }

  .no-input-wrap input {
    background: transparent; border: none;
    color: #ffffff; font-family: 'Share Tech Mono', monospace;
    font-size: 22px; outline: none;
    width: 100%; letter-spacing: 1px; padding: 0; font-weight: 700;
  }
  .no-input-wrap input:focus { border: none; background: transparent; }
  .no-input-wrap input::placeholder { color: #8aaac8; }

  .actions { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }

  .btn-gen {
    background: #1db954; border: none; color: #fff;
    font-family: 'Share Tech Mono', monospace;
    font-size: 19px; letter-spacing: 2px; font-weight: 700;
    padding: 11px 28px; cursor: pointer; text-transform: uppercase;
  }
  .btn-gen:hover { background: #17a348; }

  .btn-gp {
    background: #1565c0; border: none; color: #fff;
    font-family: 'Share Tech Mono', monospace;
    font-size: 19px; letter-spacing: 2px; font-weight: 700;
    padding: 11px 28px; cursor: pointer; text-transform: uppercase;
  }
  .btn-gp:hover { background: #1251a3; }

  .btn-clr {
    background: #1e3245; border: 1px solid #2a4460; color: #ddeeff;
    font-family: 'Share Tech Mono', monospace;
    font-size: 19px; letter-spacing: 2px; font-weight: 700;
    padding: 11px 22px; cursor: pointer; text-transform: uppercase;
  }
  .btn-clr:hover { border-color: #00cfee; color: #00cfee; }

  #printArea { display: none; }

  /* ═══════════════════════════════════════════════
     PRINT STYLES — larger fonts, Arial heading, signature below table
  ═══════════════════════════════════════════════ */
  @media print {
    @page { margin: 0; size: A4 portrait; }
    body > * { display: none !important; }

    #printArea {
      display: block !important;
      position: fixed;
      top: 0; left: 0;
      width: 210mm;
      min-height: 297mm;
      background: #fff;
      padding: 14mm 18mm;
      font-family: Arial, Helvetica, sans-serif;
      color: #000;
    }

    .a4-title {
      text-align: center;
      font-size: 28px;
      font-weight: 900;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: #000;
      padding: 8px 0;
      margin-bottom: 14px;
      font-family: Arial, Helvetica, sans-serif;
    }

    .a4-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
    }

    .a4-co {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 36px;
      color: #000;
      font-weight: 900;
      letter-spacing: 0.5px;
    }

    .a4-meta {
      font-size: 17px;
      color: #000;
      margin-top: 4px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .a4-tbl {
      width: 100%;
      border-collapse: collapse;
      border: 2.5px solid #000;
    }

    .a4-tbl td {
      padding: 10px 14px 18px;
      border-bottom: 1.5px solid #000;
      vertical-align: top;
    }

    .a4-tbl tr:last-child td { border-bottom: none; }

    .a4-lbl {
      font-size: 18px;
      letter-spacing: 2px;
      color: #000;
      margin-bottom: 6px;
      text-transform: uppercase;
      font-weight: 900;
    }

    .a4-val {
      font-size: 30px;
      color: #000;
      font-weight: 900;
      border-bottom: 2.5px solid #000;
      padding-bottom: 2px;
      min-height: 36px;
      letter-spacing: 1px;
      font-family: Arial, Helvetica, sans-serif;
    }

    .a4-sig {
      margin-top: 28px;
      display: flex;
      justify-content: flex-end;
      padding-right: 20px;
    }

    .a4-sig-box { width: 260px; text-align: center; }

    .a4-sig-line {
      border-bottom: 2px solid #000;
      height: 50px;
      margin-bottom: 6px;
    }

    .a4-sig-lbl {
      font-size: 13px;
      letter-spacing: 2px;
      color: #000;
      text-transform: uppercase;
      font-weight: 900;
    }

    .a4-foot {
      margin-top: 40px;
      border-top: 1.5px solid #000;
      padding-top: 8px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-size: 13px;
      color: #000;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      font-weight: 700;
    }

    .wm {
      position: fixed; top: 50%; left: 50%;
      transform: translate(-50%,-50%) rotate(-30deg);
      font-size: 90px; color: rgba(0,0,0,0.03);
      white-space: nowrap; pointer-events: none;
      letter-spacing: 8px; text-transform: uppercase;
    }
  }

  .btn-done {
    background: #f97316 !important; border: none; color: #fff !important;
    font-family: 'Share Tech Mono', monospace;
    font-size: 19px; letter-spacing: 2px; font-weight: 700;
    padding: 11px 28px; cursor: pointer !important; text-transform: uppercase;
  }
  .btn-done:hover { background: #e06210 !important; }

  .modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
  }
  .modal-overlay.show { display: flex; }

  .modal-box {
    background: #152030;
    border: 1px solid #00cfee;
    padding: 44px 54px;
    text-align: center;
    min-width: 360px;
    max-width: 500px;
    animation: modalIn 0.2s ease;
    position: relative;
  }

  @keyframes modalIn {
    from { transform: scale(0.85); opacity: 0; }
    to   { transform: scale(1);    opacity: 1; }
  }

  .modal-icon { font-size: 48px; margin-bottom: 14px; display: block; }

  .modal-title {
    font-family: 'Share Tech Mono', monospace;
    font-size: 22px; letter-spacing: 4px; color: #00cfee;
    text-transform: uppercase; margin-bottom: 12px; font-weight: 700;
  }

  .modal-msg {
    font-family: 'Share Tech Mono', monospace;
    font-size: 17px; color: #e8f4ff; letter-spacing: 2px;
    margin-bottom: 28px; line-height: 1.9; font-weight: 700;
  }

  .modal-btn {
    background: #1db954; border: none; color: #fff;
    font-family: 'Share Tech Mono', monospace;
    font-size: 17px; letter-spacing: 3px; font-weight: 700;
    padding: 12px 36px; cursor: pointer; text-transform: uppercase;
    transition: background 0.15s;
  }
  .modal-btn:hover { background: #17a348; }

  .modal-corner { position: absolute; width: 12px; height: 12px; border-color: #f97316; border-style: solid; }
  .modal-corner.tl { top: -1px; left: -1px;  border-width: 2px 0 0 2px; }
  .modal-corner.tr { top: -1px; right: -1px; border-width: 2px 2px 0 0; }
  .modal-corner.bl { bottom: -1px; left: -1px;  border-width: 0 0 2px 2px; }
  .modal-corner.br { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

  .field select {
    background: #0d1b2e; border: 1px solid #1e3245;
    color: #ffffff; font-family: 'Share Tech Mono', monospace;
    font-size: 22px; padding: 9px 13px; outline: none;
    width: 100%; letter-spacing: 1px; cursor: pointer; font-weight: 700;
    appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2300cfee' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center;
  }
  .field select:focus { border-color: #00cfee; background-color: #0a1624; }
  .field select option { background: #0d1b2e; color: #ffffff; }
</style>
</head>
<body>

<div class="topright">
  <div class="dt-boxes">
    <div class="dt-box">Date: <span id="top-date"></span></div>
    <div class="dt-box">Time: <span id="top-time"></span></div>
  </div>
  <a href="ghistory.php" class="btn-history">📋 VIEW HISTORY</a>
</div>

<div class="card">
  <div class="card-header">
    <span class="bolt">⚡</span>
    <h1>Sarthi Sports Wear — Garage Copy</h1>
  </div>
  <div class="card-body">
    <div class="company-row">
      <div>
        <div class="company-name">Sarthi Sports Wear</div>
        <div class="company-meta">Ph: 7620425141 | Mob: 9422107750</div>
      </div>
      <div class="gc-no-box">
        <div class="gc-no-label">Garage Copy No.</div>
        <input class="gc-no-val" id="gc-no" type="text" placeholder="—" readonly />
      </div>
    </div>

    <div class="section-label">Details</div>

    <div class="field-row">
      <div class="field">
        <label>No.</label>
        <div class="no-input-wrap">
          <span class="no-prefix">GC-</span>
          <input type="text" id="no" placeholder="0000" oninput="syncGC(this.value)" />
        </div>
      </div>
      <div class="field">
        <label>Date</label>
        <input type="date" id="date" />
      </div>
    </div>

    <div class="field-row">
      <div class="field">
        <label>Party Name</label>
        <input type="text" id="party-name" placeholder="Enter party name" />
      </div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Garage Name</label>
        <input type="text" id="garage-name" placeholder="Enter garage name" />
      </div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Parcel No.</label>
        <input type="text" id="parcel-no" placeholder="Enter parcel number" />
      </div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Copy Name</label>
        <select id="copy-name">
          <option value="garage">Garage Copy</option>
          <option value="gatepass">Gate Pass</option>
        </select>
      </div>
    </div>

    <div class="actions">
      <button class="btn-gen" onclick="doPrint('garage')">🖨️ GARAGE COPY</button>
      <button class="btn-gp"  onclick="doPrint('gatepass')">🖨️ GATE PASS</button>
      <button class="btn-clr" onclick="clearF()">↺ CLEAR</button>
      <button class="btn-done" onclick="doDone()">✅ DONE</button>
    </div>
  </div>
</div>

<!-- PRINT AREA — only this section was changed -->
<div id="printArea">
  <div class="wm">Sarthi</div>
  <div class="a4-title" id="p-badge">GARAGE COPY</div>
  <div class="a4-head">
    <div>
      <div class="a4-co">Sarthi Sports Wear</div>
      <div class="a4-meta">Ph: 7620425141 | 9422107750</div>
    </div>
    <div style="text-align:right;">
      <div style="font-size:14px; letter-spacing:2px; font-weight:900; text-transform:uppercase; margin-bottom:4px;">Date</div>
      <div style="font-size:26px; font-weight:900; color:#000;" id="p-date"></div>
    </div>
  </div>
  <table class="a4-tbl">
    <tr>
      <td colspan="2"><div class="a4-lbl">No.</div><div class="a4-val" id="p-no"></div></td>
    </tr>
    <tr>
      <td colspan="2"><div class="a4-lbl">Party Name</div><div class="a4-val" id="p-party"></div></td>
    </tr>
    <tr>
      <td colspan="2"><div class="a4-lbl">Garage Name</div><div class="a4-val" id="p-garage"></div></td>
    </tr>
    <tr>
      <td colspan="2"><div class="a4-lbl">Parcel No.</div><div class="a4-val" id="p-parcel"></div></td>
    </tr>
  </table>

  <!-- Signature below the table -->
  <div class="a4-sig">
    <div class="a4-sig-box">
      <div class="a4-sig-line"></div>
      <div class="a4-sig-lbl">Authorized Signature</div>
    </div>
  </div>

  <div class="a4-foot">
    <div>
      <div id="p-foot-label">Sarthi Sports Wear</div>
      <div id="p-gcno"></div>
    </div>
  </div>
</div>

<script>
  function tick() {
    const n = new Date();
    document.getElementById('top-date').textContent =
      `${String(n.getDate()).padStart(2,'0')}/${String(n.getMonth()+1).padStart(2,'0')}/${n.getFullYear()}`;
    document.getElementById('top-time').textContent =
      `${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}:${String(n.getSeconds()).padStart(2,'0')}`;
  }
  tick(); setInterval(tick, 1000);

  const nd = new Date();
  const yyyy = nd.getFullYear();
  const mm   = String(nd.getMonth()+1).padStart(2,'0');
  const dd   = String(nd.getDate()).padStart(2,'0');
  document.getElementById('date').value = `${yyyy}-${mm}-${dd}`;

  function getFormattedDate() {
    const val = document.getElementById('date').value;
    if (!val) return '';
    const [y, m, d] = val.split('-');
    return `${d}-${m}-${y}`;
  }

  const gcNum = String(Math.floor(Math.random() * 9000) + 1000);
  document.getElementById('gc-no').value = 'GC-' + gcNum;
  document.getElementById('no').value    = gcNum;

  function syncGC(val) {
    const digits = val.replace(/\D/g, '');
    document.getElementById('no').value    = digits;
    document.getElementById('gc-no').value = 'GC-' + digits;
  }

  function getFullNo() {
    return 'GC-' + document.getElementById('no').value;
  }

  function saveToDB(type) {
    const fd = new FormData();
    fd.append('action',      'save');
    fd.append('type',        type);
    fd.append('date',        getFormattedDate());
    fd.append('no',          getFullNo());
    fd.append('party_name',  document.getElementById('party-name').value);
    fd.append('parcel_no',   document.getElementById('parcel-no').value);
    fd.append('garage_name', document.getElementById('garage-name').value);

    return fetch('garagecopy.php', { method: 'POST', body: fd })
      .then(res => res.text())
      .then(txt => {
        if (txt.trim() !== 'ok') console.error('Save failed:', txt);
      })
      .catch(err => console.error('Fetch error:', err));
  }

  function doPrint(type) {
    const date        = getFormattedDate();
    const no          = getFullNo();
    const party_name  = document.getElementById('party-name').value;
    const parcel_no   = document.getElementById('parcel-no').value;
    const garage_name = document.getElementById('garage-name').value;

    saveToDB(type);

    document.getElementById('p-date').textContent   = date;
    document.getElementById('p-no').textContent     = no;
    document.getElementById('p-party').textContent  = party_name;
    document.getElementById('p-parcel').textContent = parcel_no;
    document.getElementById('p-garage').textContent = garage_name;
    document.getElementById('p-gcno').textContent   = document.getElementById('gc-no').value;

    if (type === 'gatepass') {
      document.getElementById('p-badge').textContent      = 'GATE PASS';
      document.getElementById('p-foot-label').textContent = 'Sarthi Sports Wear';
    } else {
      document.getElementById('p-badge').textContent      = 'GARAGE COPY';
      document.getElementById('p-foot-label').textContent = 'Sarthi Sports Wear';
    }

    window.print();
  }

  function clearF() {
    ['party-name', 'parcel-no', 'garage-name'].forEach(id => document.getElementById(id).value = '');
  }

  function showModal(icon, title, msg, doClear) {
    document.querySelector('#doneModal .modal-icon').textContent  = icon;
    document.querySelector('#doneModal .modal-title').textContent = title;
    document.querySelector('#doneModal .modal-msg').innerHTML     = msg;
    document.getElementById('doneModal').classList.add('show');
    document.getElementById('doneModal').dataset.clear = doClear ? '1' : '0';
  }

  function closeModal() {
    const modal = document.getElementById('doneModal');
    if (modal.dataset.clear === '1') clearF();
    modal.classList.remove('show');
  }

  function doDone() {
    const party_name  = document.getElementById('party-name').value;
    const parcel_no   = document.getElementById('parcel-no').value;
    const garage_name = document.getElementById('garage-name').value;
    const copy_name   = document.getElementById('copy-name')
                        ? document.getElementById('copy-name').value
                        : 'garage';

    if (!party_name && !parcel_no && !garage_name) {
      showModal('⚠️', 'MISSING DATA', 'Please fill in at least<br>one field before saving.', false);
      return;
    }

    showModal('⚡', 'RECORD SAVED', 'Entry has been logged<br>to history successfully.', true);

    saveToDB(copy_name);
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      const modal = document.getElementById('doneModal');
      if (modal.classList.contains('show')) {
        e.preventDefault();
        closeModal();
      }
    }
  });
</script>

<!-- Custom Alert Modal -->
<div class="modal-overlay" id="doneModal">
  <div class="modal-box">
    <div class="modal-corner tl"></div>
    <div class="modal-corner tr"></div>
    <div class="modal-corner bl"></div>
    <div class="modal-corner br"></div>
    <span class="modal-icon">⚡</span>
    <div class="modal-title">Record Saved</div>
    <div class="modal-msg">Entry has been logged<br>to history successfully.</div>
    <button class="modal-btn" onclick="closeModal()">✓ OK</button>
  </div>
</div>
</body>
</html>