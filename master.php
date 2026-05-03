<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sarkhi sports1";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("<div style='color:red;font-family:sans-serif;padding:30px;font-size:15px;'>"
      . "<b>DB Connection Failed:</b> " . mysqli_connect_error()
      . "<br><br>Fix: edit line 6 and set correct DB name."
      . "</div>");
}



// ── Next MC number ──
$r    = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM master_copy_log");
$row  = $r ? mysqli_fetch_assoc($r) : array('cnt' => 0);
$cnt  = (int)$row['cnt'];
$mc_no = 'MC-' . str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);

$saved        = false;
$save_error   = '';
$saved_mc_no  = '';

// ── Handle DELETE ──
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM master_copy_log WHERE id=$did");
    header("Location: master.php?page=history");
    exit;
}

// ── Handle BULK DELETE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
    $ids = isset($_POST['bulk_ids']) ? $_POST['bulk_ids'] : '';
    $id_arr = array_filter(array_map('intval', explode(',', $ids)));
    if (!empty($id_arr)) {
        $id_list = implode(',', $id_arr);
        mysqli_query($conn, "DELETE FROM master_copy_log WHERE id IN ($id_list)");
    }
    header("Location: master.php?page=history");
    exit;
}

// ── Handle POST save ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $p_mc      = mysqli_real_escape_string($conn, isset($_POST['mc_no'])         ? $_POST['mc_no']         : $mc_no);
    $p_pno     = mysqli_real_escape_string($conn, isset($_POST['party_no'])      ? $_POST['party_no']      : '');
    $p_od      = mysqli_real_escape_string($conn, isset($_POST['order_date'])    ? $_POST['order_date']    : date('Y-m-d'));
    $p_dd      = mysqli_real_escape_string($conn, isset($_POST['delivery_date']) ? $_POST['delivery_date'] : '');
    $p_items   = mysqli_real_escape_string($conn, isset($_POST['items_json'])    ? $_POST['items_json']    : '[]');
    $p_remarks = mysqli_real_escape_string($conn, isset($_POST['remarks'])       ? $_POST['remarks']       : '');
    $dd_sql    = ($p_dd !== '') ? "'$p_dd'" : "NULL";

    $sql = "INSERT INTO master_copy_log
                (mc_no, party_no, order_date, delivery_date, items_json, remarks)
            VALUES
                ('$p_mc','$p_pno','$p_od',$dd_sql,'$p_items','$p_remarks')";

    if (mysqli_query($conn, $sql)) {
    $saved       = true;
    $saved_mc_no = $p_mc;
    $cnt++;
    $mc_no = 'MC-' . str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);
    /* auto-print trigger */
    if (isset($_POST['do_print']) && $_POST['do_print'] === '1') {
        header("Location: master.php?page=form&do_print=1&saved_mc=".urlencode($p_mc));
        exit;
    }
} else {
        $save_error = mysqli_error($conn);
    }
}

$mc_no_safe       = htmlspecialchars($mc_no);
$saved_mc_no_safe = htmlspecialchars($saved_mc_no);
$save_error_safe  = htmlspecialchars($save_error);

// ── Current page ──
$page = isset($_GET['page']) ? $_GET['page'] : 'form';

// ── History search ──
$search     = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_esc = mysqli_real_escape_string($conn, $search);
$where      = $search_esc ? "WHERE mc_no LIKE '%$search_esc%' OR party_no LIKE '%$search_esc%' OR remarks LIKE '%$search_esc%'" : '';
$hist_res   = mysqli_query($conn, "SELECT * FROM master_copy_log $where ORDER BY id DESC");
$hist_rows  = array();
if ($hist_res) {
    while ($hr = mysqli_fetch_assoc($hist_res)) {
        $hist_rows[] = $hr;
    }
}

$total_res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM master_copy_log");
$total_row = mysqli_fetch_assoc($total_res);
$total_mc  = (int)$total_row['c'];
$today_res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM master_copy_log WHERE DATE(created_at)=CURDATE()");
$today_row = mysqli_fetch_assoc($today_res);
$today_mc  = (int)$today_row['c'];
$week_res  = mysqli_query($conn, "SELECT COUNT(*) AS c FROM master_copy_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$week_row  = mysqli_fetch_assoc($week_res);
$week_mc   = (int)$week_row['c'];

// ── Date / Month / Year filter ──
$filter_type  = isset($_GET['ftype'])  ? $_GET['ftype']  : '';
$filter_date  = isset($_GET['fdate'])  ? $_GET['fdate']  : '';
$filter_month = isset($_GET['fmonth']) ? $_GET['fmonth'] : '';
$filter_year  = isset($_GET['fyear'])  ? $_GET['fyear']  : '';

$filter_where = '';
if ($filter_type === 'day' && $filter_date !== '') {
    $fd = mysqli_real_escape_string($conn, $filter_date);
    $filter_where = "WHERE DATE(order_date)='$fd'";
} elseif ($filter_type === 'month' && $filter_month !== '') {
    $fm = mysqli_real_escape_string($conn, $filter_month);
    $filter_where = "WHERE DATE_FORMAT(order_date,'%Y-%m')='$fm'";
} elseif ($filter_type === 'year' && $filter_year !== '') {
    $fy = mysqli_real_escape_string($conn, $filter_year);
    $filter_where = "WHERE YEAR(order_date)='$fy'";
}

if ($search_esc && $filter_where) {
    $filter_where .= " AND (mc_no LIKE '%$search_esc%' OR party_no LIKE '%$search_esc%' OR remarks LIKE '%$search_esc%')";
} elseif ($search_esc) {
    $filter_where = "WHERE mc_no LIKE '%$search_esc%' OR party_no LIKE '%$search_esc%' OR remarks LIKE '%$search_esc%'";
}

$hist_res2 = mysqli_query($conn, "SELECT * FROM master_copy_log $filter_where ORDER BY order_date DESC, id DESC");
$hist_rows  = array();
if ($hist_res2) {
    while ($hr = mysqli_fetch_assoc($hist_res2)) { $hist_rows[] = $hr; }
}

$month_res = mysqli_query($conn,
    "SELECT DATE_FORMAT(order_date,'%Y-%m') AS ym, COUNT(*) AS cnt, SUM(0) AS qty
     FROM master_copy_log WHERE order_date IS NOT NULL
     GROUP BY ym ORDER BY ym DESC LIMIT 12");
$month_data = array();
if ($month_res) {
    while ($mr = mysqli_fetch_assoc($month_res)) $month_data[] = $mr;
}

$cal_month = ($filter_type==='month' && $filter_month) ? $filter_month
           : (isset($_GET['calmonth']) ? $_GET['calmonth'] : date('Y-m'));
$cal_month = preg_match('/^\d{4}-\d{2}$/', $cal_month) ? $cal_month : date('Y-m');
$cal_month_esc = mysqli_real_escape_string($conn, $cal_month);
$day_res = mysqli_query($conn,
    "SELECT DATE(order_date) AS od, COUNT(*) AS cnt
     FROM master_copy_log
     WHERE DATE_FORMAT(order_date,'%Y-%m')='$cal_month_esc' AND order_date IS NOT NULL
     GROUP BY od ORDER BY od");
$day_data = array();
if ($day_res) {
    while ($dr = mysqli_fetch_assoc($day_res)) $day_data[$dr['od']] = (int)$dr['cnt'];
}

$year_res = mysqli_query($conn,
    "SELECT YEAR(order_date) AS yr, COUNT(*) AS cnt
     FROM master_copy_log WHERE order_date IS NOT NULL
     GROUP BY yr ORDER BY yr DESC");
$year_data = array();
if ($year_res) {
    while ($yr = mysqli_fetch_assoc($year_res)) $year_data[] = $yr;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarthi Sports Wear - Master Copy</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#0b1622; --panel:#0f1e2e; --panel2:#152234;
    --bdr:#1e3a55; --blt:#2a4f73;
    --ac:#00ccff; --ac2:#00ffaa;
    --txt:#c8e0f4; --dim:#5a88aa; --lbl:#3a6080;
    --ibg:#080f18; --hbg:#091525;
    --grn:#00cc77; --blu:#0077ee; --red:#dd3333; --gry:#334455;
    --gold:#f0c040;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Barlow',sans-serif;background:var(--bg);color:var(--txt);min-height:100vh;}

.topbar{background:var(--hbg);border-bottom:2px solid var(--ac);padding:7px 18px;
    display:flex;align-items:center;justify-content:space-between;
    font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--white);}
.brand{color:var(--ac);font-size:13px;font-weight:700;letter-spacing:2px;}
.pills{display:flex;gap:12px;}
.pill{background:var(--panel2);padding:2px 10px;border-radius:3px;border:1px solid var(--bdr);}
.pill span{color:var(--ac2);}

.navtabs{background:#060f1b;border-bottom:2px solid var(--bdr);display:flex;gap:0;}
.navtab{padding:11px 30px;font-size:13px;font-weight:700;letter-spacing:.5px;
    color:var(--dim);text-decoration:none;border-bottom:3px solid transparent;
    transition:color .15s,border-color .15s;display:inline-flex;align-items:center;gap:8px;}
.navtab:hover{color:var(--txt);}
.navtab.active{color:var(--ac);border-bottom-color:var(--ac);}
.navtab .nbadge{background:var(--blu);color:#fff;font-size:10px;padding:1px 8px;
    border-radius:10px;font-family:'Share Tech Mono',monospace;}

.page{max-width:1300px;margin:0 auto;padding:22px 24px 60px;}

.shdr{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:20px;}
.shdr h1{font-size:50px;font-weight:700;color:var(--ac);}
.shdr .sub{font-size:30px;color:var(--dim);margin-top:3px;}
.mc-badge{background:var(--blu);border:2px solid var(--ac);border-radius:5px;
    text-align:center;padding:6px 18px;font-family:'Share Tech Mono',monospace;}
.mc-badge .lbl{font-size:20px;color:whitesmoke  ;letter-spacing:1px;text-transform:uppercase;}
.mc-badge .num{font-size:30px;font-weight:700;color:#fff;letter-spacing:2px;}
#F_mcno { color: var(--ac2); font-weight: 700; }
/* ── Field colors ── */
#F_date       { color: var(--ac); }        /* cyan  — date field */
#F_mcno_num   { color: green; }       /* green — MC number input */
#F_pno        { color: var(--ac); }        /* cyan  — party no input */
#F_pno::placeholder { color: var(--ac); opacity: 0.55; } /* cyan placeholder */
#F_ddate      { color: var(--gold); }      /* gold  — delivery date  ← FIXED (was --white) */
.mc-prefix-span { color: var(--ac2) !important; } /* green — MC- prefix span */
.slbl{font-size:35px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;
    color:var(--ac);display:flex;align-items:center;gap:8px;margin-bottom:12px;}
.slbl::before{content:'▶';font-size:8px;}
.slbl::after{content:'';flex:1;height:1px;background:var(--bdr);}

.card{background:var(--panel);border:1px solid var(--bdr);border-radius:6px;
    padding:18px 20px;margin-bottom:16px;}

.fg{display:grid;gap:12px;}
.fg2{grid-template-columns:1fr 1fr;}
.fld{display:flex;flex-direction:column;gap:5px;}
.fld label{font-size:20px;text-transform:uppercase;letter-spacing:.8px;color:#dce8f0;font-weight:700;}
.fld input,.fld select{background:var(--ibg);border:1px solid var(--bdr);border-radius:4px;
    color:var(--txt);font-size:25px;font-family:'Share Tech Mono',monospace;padding:9px 12px;}
.fld input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1) sepia(1) saturate(5) hue-rotate(170deg);
    cursor: pointer;
}
.fld input:focus,.fld select:focus{outline:none;border-color:var(--white);background:#040c14;}
.fld input[readonly]{color:var(--white);}
.fld input::placeholder{color:var(--white);font-size:20px;}
.span2{grid-column:span 2;}

.itbl-wrap{overflow-x:auto;}
.ith{display:grid;grid-template-columns:44px 3fr 220px 160px;
    gap:6px;padding:10px 14px;background:var(--hbg);
    border:1px solid var(--bdr);border-radius:5px 5px 0 0;min-width:500px;
    font-size:20px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--white);}
.itb{border:1px solid var(--bdr);border-top:none;border-radius:0 0 5px 5px;
    overflow:hidden;min-width:500px;}
.pwrap{border-bottom:1px solid var(--bdr);}
.pwrap:last-child{border-bottom:none;}

.prow{display:grid;grid-template-columns:44px 3fr 220px 160px;
    gap:6px;padding:8px 14px;align-items:center;background:var(--panel);}
.pwrap:nth-child(even) .prow{background:#0d1a28;}
.prow .srn{font-size:20px;color:var(--dim);text-align:center;
    font-family:'Share Tech Mono',monospace;font-weight:700;}
.prow select,.prow input{background:transparent;border:none;color:var(--txt);
    font-size:20px;font-family:'Barlow',sans-serif;padding:7px 8px;width:100%;border-radius:3px;}
.prow select:focus,.prow input:focus{outline:none;background:var(--ibg);}
.prow select option{background:#0f1e2e;}
.ract{display:flex;gap:5px;align-items:center;}

.nrow{display:none;padding:10px 14px 10px 14px;background:whitesmoke;border-top:1px dashed var(--bdr);}
.nrow.open{display:block;}
.nrow textarea{background:var(--ibg);border:1px solid var(--bdr);border-radius:5px;
    color:var(--dim);font-size:20px;padding:10px 12px;width:100%;
    min-height:72px;resize:vertical;font-family:'Barlow',sans-serif;line-height:1.5;}
.nrow textarea:focus{outline:none;border-color:var(--ac);color:var(--txt);}
.nrow-lbl{font-size:18px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;
    color:var(--lbl);margin-bottom:5px;display:block;}

.szpan{display:none;background:#000;border-top:2px solid #111;}
.szpan.open{display:block;}

.sz-head{
    display:grid;
    grid-template-columns: 170px 130px 1fr 1fr 44px;
    gap:8px;
    padding:10px 14px 8px 66px;
    border-bottom:1px solid #181818;
    background:#050505;
    min-width:700px;
}
.sz-head span{
    font-size:20px;font-weight:700;text-transform:uppercase;
    letter-spacing:1px;color:#444;font-family:'Share Tech Mono',monospace;
}

.szinn{display:flex;flex-direction:column;gap:0;}

.sze{
    display:grid;
    grid-template-columns: 170px 130px 1fr 1fr 44px;
    gap:8px;
    align-items:center;
    padding:7px 14px 7px 66px;
    border-bottom:1px solid #0f0f0f;
    background:#000;
    transition:background .1s;
    min-width:700px;
}
.sze:hover{background:#070707;}

.sze input.sz-val{
    background:#111;border:2px solid #2a2a2a;color:#ffffff;
    font-family:'Share Tech Mono',monospace;font-size:25px;font-weight:700;
    text-align:center;width:100%;height:48px;border-radius:6px;padding:0 8px;
    transition:border-color .15s,background .15s;
}
.sze input.sz-val:focus{outline:none;border-color:var(--ac);background:#0a0a0a;box-shadow:0 0 0 3px rgba(0,204,255,.12);}
.sze input.sz-val::placeholder{color:#2a2a2a;font-size:20px;}

.sze input.sz-qty{
    background:#111;border:2px solid #1a3322;color:#00ffaa;
    font-family:'Share Tech Mono',monospace;font-size:25px;font-weight:700;
    text-align:center;width:100%;height:48px;border-radius:6px;padding:0 8px;
    transition:border-color .15s,background .15s;
}
.sze input.sz-qty:focus{outline:none;border-color:var(--ac2);background:#0a0a0a;box-shadow:0 0 0 3px rgba(0,255,170,.10);}
.sze input.sz-qty::placeholder{color:#1a3322;font-size:20px;}

.sz-color-wrap,.sz-fabric-wrap{display:flex;flex-direction:column;gap:0;}
.sze select.sz-color-sel,.sze select.sz-fabric-sel{
    background:#0d1a10;border:2px solid #1a3322;color:#c8e0f4;
    font-family:'Barlow',sans-serif;font-size:20px;height:48px;
    border-radius:6px;padding:0 10px;width:100%;cursor:pointer;
    transition:border-color .15s,border-radius .1s;
}
.sze select.sz-color-sel:focus,.sze select.sz-fabric-sel:focus{outline:none;border-color:var(--ac2);background:#050d08;}
.sze select.sz-color-sel option,.sze select.sz-fabric-sel option{background:#0f1e2e;}
.sze select.sz-color-sel.custom-open,.sze select.sz-fabric-sel.custom-open{border-radius:6px 6px 0 0;}
.sze input.sz-color-custom,.sze input.sz-fabric-custom{
    display:none;background:#060f08;border:2px solid #00ffaa;border-top:none;
    color:#00ffaa;font-family:'Share Tech Mono',monospace;font-size:20px;
    height:34px;border-radius:0 0 6px 6px;padding:0 8px;width:100%;
}
.sze input.sz-color-custom.visible,.sze input.sz-fabric-custom.visible{display:block;}
.sze input.sz-color-custom:focus,.sze input.sz-fabric-custom:focus{
    outline:none;border-color:#00ffaa;background:#040c06;box-shadow:0 0 0 2px rgba(0,255,170,.10);}
.sze input.sz-color-custom::placeholder,.sze input.sz-fabric-custom::placeholder{color:#1a4a1a;font-size:20px;}

.rm-sz{
    background:transparent;border:none;color:#3a1515;font-size:25px;cursor:pointer;
    line-height:1;transition:color .15s;text-align:center;
    width:36px;height:36px;border-radius:4px;align-self:center;
}
.rm-sz:hover{color:#ff4444;background:rgba(255,50,50,.08);}

.addszbtn{
    display:block;width:100%;background:#000;border:none;border-top:1px dashed #1a1a1a;
    color:#1d5540;font-size:20px;font-weight:700;
    padding:11px 14px 11px 66px;text-align:left;cursor:pointer;
    letter-spacing:.5px;transition:color .15s,background .15s;font-family:'Barlow',sans-serif;
}
.addszbtn:hover{color:#00ffaa;background:#050505;}

.btn{padding:10px 24px;border:none;border-radius:4px;font-size:20px;font-weight:700;
    cursor:pointer;font-family:'Barlow',sans-serif;display:inline-flex;align-items:center;
    gap:6px;letter-spacing:.5px;transition:filter .15s,transform .1s;}
.btn:hover{filter:brightness(1.18);}
.btn:active{transform:scale(.97);}
.btn-grn{background:var(--grn);color:#000;}
.btn-blu{background:var(--blu);color:#fff;}
.btn-gry{background:var(--gry);color:#ccc;}
.btn-pur{background:#7733cc;color:#fff;}
.btn-org{background:#cc6600;color:#fff;}
.btn-sm{padding:5px 13px;font-size:20px;font-weight:600;}
.delbtn{background:transparent;border:1px solid #332020;color:var(--red);
    border-radius:3px;padding:3px 8px;font-size:13px;cursor:pointer;}
.delbtn:hover{background:rgba(220,50,50,.15);border-color:var(--red);}
.togbtn{background:transparent;border:1px solid var(--bdr);color:var(--ac2);
    font-size:20px;font-weight:600;padding:4px 10px;border-radius:3px;cursor:pointer;white-space:nowrap;}
.togbtn:hover{background:rgba(0,255,170,.07);}

.abar{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;padding-top:14px;border-top:1px solid var(--bdr);}
.addbar{display:flex;gap:8px;margin-top:10px;}

.bnr{padding:10px 16px;font-size:20px;font-weight:600;border-radius:5px;border:1px solid;margin-bottom:14px;}
.bnr-ok{background:rgba(0,204,119,.12);border-color:var(--grn);color:var(--grn);}
.bnr-er{background:rgba(220,50,50,.12);border-color:var(--red);color:#ff8888;}

.summary-stats{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:20px;}
.stat-box{background:var(--panel);border:1px solid var(--bdr);border-radius:6px;
    padding:14px 22px;text-align:center;flex:1;min-width:120px;}
.stat-box .sv{font-size:40px;font-weight:700;color:var(--ac);font-family:'Share Tech Mono',monospace;}
.stat-box .sl{font-size:20px;color:var(--dim);text-transform:uppercase;letter-spacing:.8px;margin-top:3px;}

.hist-header{display:flex;justify-content:space-between;align-items:center;
    flex-wrap:wrap;gap:12px;margin-bottom:16px;}
.hist-title{font-size:30px;font-weight:700;color:var(--ac);}
.hist-header-right{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.search-bar{display:flex;gap:8px;align-items:center;}
.search-bar input{background:var(--ibg);border:1px solid var(--bdr);color:var(--txt);
    font-size:20px;padding:8px 14px;border-radius:4px;width:230px;font-family:'Share Tech Mono',monospace;}
.search-bar input:focus{outline:none;border-color:var(--ac);}

.htbl{width:100%;border-collapse:collapse;font-size:20px;}
.htbl thead th{background:#060f1b;color:var(--dim);font-size:18px;text-transform:uppercase;
    letter-spacing:.8px;font-weight:700;padding:9px 12px;text-align:left;
    border-bottom:2px solid var(--bdr);white-space:nowrap;}
.htbl tbody tr{border-bottom:1px solid var(--bdr);transition:background .12s;}
.htbl tbody tr:hover{background:var(--panel2);}
.htbl tbody td{padding:10px 12px;vertical-align:top;}

.mc-pill{display:inline-block;background:var(--blu);color:#fff;
    font-family:'Share Tech Mono',monospace;font-size:20px;font-weight:700;
    padding:3px 10px;border-radius:4px;letter-spacing:1px;}
.item-chip{display:inline-block;background:var(--panel2);border:1px solid var(--bdr);
    color:var(--txt);font-size:20px;padding:2px 8px;border-radius:3px;margin:2px 3px 2px 0;}
.sz-chip-h{display:inline-block;background:#0a1520;border:1px solid #1e3a55;
    color:var(--ac2);font-family:'Share Tech Mono',monospace;font-size:10px;
    padding:1px 7px;border-radius:2px;margin:1px 2px;}

.expand-btn{background:transparent;border:1px solid var(--bdr);color:var(--ac);
    font-size:20px;padding:3px 9px;border-radius:3px;cursor:pointer;margin-top:5px;display:inline-block;}
.expand-btn:hover{background:rgba(0,204,255,.08);}

.detail-row{display:none;background:#060e16;}
.detail-row.open{display:table-row;}
.detail-row > td{padding:12px 16px;border-bottom:2px solid var(--bdr);}
.detail-inner{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;}
.di-card{background:var(--panel);border:1px solid var(--bdr);border-radius:5px;padding:11px 14px;}
.di-prod{font-size:20px;font-weight:700;color:var(--ac2);margin-bottom:4px;}
.di-meta{font-size:20px;color:var(--dim);margin-bottom:6px;}
.di-sizes{display:flex;flex-wrap:wrap;gap:4px;}

.del-link{color:var(--red);font-size:20px;text-decoration:none;
    border:1px solid #332020;border-radius:3px;padding:3px 9px;transition:background .12s;
    display:inline-block;}
.del-link:hover{background:rgba(220,50,50,.15);}
.date-col{font-family:'Share Tech Mono',monospace;font-size:20px;color:var(--dim);}

.empty-state{text-align:center;padding:60px 20px;}
.empty-state .ei{font-size:56px;margin-bottom:12px;}
.empty-state p{font-size:20px;color:var(--dim);}

.action-group{display:flex;gap:5px;align-items:center;flex-wrap:nowrap;}
.print-row-btn{
    background:transparent;border:1px solid #1a3a5a;color:#00aacc;
    font-size:20px;border-radius:3px;padding:3px 9px;cursor:pointer;
    transition:background .12s;white-space:nowrap;
}
.print-row-btn:hover{background:rgba(0,170,204,.15);border-color:#00aacc;}

#PRINTDOC{display:none;}
#PRINT_HIST_ALL{display:none;}
#PRINT_SINGLE{display:none;}


/* ══════════════════════
   DATE FILTER PANEL
══════════════════════ */
.dfilter-wrap{margin-bottom:20px;}
.dfilter-tabs{display:flex;gap:0;margin-bottom:0;border-bottom:2px solid var(--bdr);}
.dftab{padding:8px 22px;font-size:20px;font-weight:700;letter-spacing:.5px;cursor:pointer;
    color:var(--dim);border:none;background:transparent;border-bottom:3px solid transparent;
    transition:color .15s,border-color .15s;font-family:'Barlow',sans-serif;}
.dftab:hover{color:var(--txt);}
.dftab.active{color:var(--ac);border-bottom-color:var(--ac);}

.dfilter-body{background:var(--panel);border:1px solid var(--bdr);border-top:none;
    border-radius:0 0 8px 8px;padding:16px 18px;}

.year-pills{display:flex;gap:10px;flex-wrap:wrap;}
.yr-pill{background:var(--panel2);border:1px solid var(--bdr);border-radius:20px;
    padding:6px 20px;font-size:20px;font-weight:700;cursor:pointer;
    color:var(--dim);font-family:'Share Tech Mono',monospace;transition:all .15s;}
.yr-pill:hover{border-color:var(--ac);color:var(--ac);}
.yr-pill.active{background:var(--ac);color:#000;border-color:var(--ac);}
.yr-pill .yc{font-size:20px;font-weight:400;margin-left:6px;opacity:.7;}

.month-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin-bottom:14px;}
.mon-cell{background:var(--panel2);border:1px solid var(--bdr);border-radius:6px;
    padding:10px 6px;text-align:center;cursor:pointer;transition:all .15s;}
.mon-cell:hover{border-color:var(--ac);background:rgba(0,204,255,.05);}
.mon-cell.active{background:var(--blu);border-color:var(--ac);color:#fff;}
.mon-cell.empty{cursor:default;opacity:.3;}
.mon-cell .mn{font-size:20px;font-weight:700;color:var(--txt);}
.mon-cell .mc{font-size:25px;font-weight:900;color:var(--ac);font-family:'Share Tech Mono',monospace;}
.mon-cell.active .mn,.mon-cell.active .mc{color:#fff;}
.mon-cell .my{font-size:18px;color:var(--dim);}

.cal-wrap{margin-top:4px;}
.cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.cal-nav-title{font-size:20px;font-weight:700;color:var(--ac);font-family:'Share Tech Mono',monospace;}
.cal-nav-btn{background:var(--panel2);border:1px solid var(--bdr);color:var(--txt);
    border-radius:4px;padding:4px 12px;cursor:pointer;font-size:20px;transition:border-color .15s;}
.cal-nav-btn:hover{border-color:var(--ac);}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
.cal-dow{font-size:18px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
    color:var(--dim);text-align:center;padding:4px 0;}
.cal-day{background:var(--panel2);border:1px solid var(--bdr);border-radius:4px;
    padding:6px 4px;text-align:center;cursor:pointer;transition:all .15s;min-height:42px;}
.cal-day:hover{border-color:var(--ac);background:rgba(0,204,255,.06);}
.cal-day.active{background:var(--blu);border-color:var(--ac);}
.cal-day.has-data{border-color:#1e5080;}
.cal-day.empty{background:transparent;border-color:transparent;cursor:default;}
.cal-day .cd{font-size:20px;font-weight:700;color:var(--txt);}
.cal-day .cc{font-size:20px;color:var(--ac2);font-family:'Share Tech Mono',monospace;font-weight:700;}
.cal-day.active .cd,.cal-day.active .cc{color:#fff;}
.cal-day.today-cell{border-color:var(--ac2) !important;}

.filter-active-bar{display:flex;align-items:center;gap:10px;padding:8px 14px;
    background:rgba(0,119,238,.12);border:1px solid var(--blu);border-radius:5px;
    margin-bottom:14px;font-size:20px;}
.filter-active-bar strong{color:var(--ac);}
.filter-clear{background:transparent;border:1px solid var(--bdr);color:var(--dim);
    border-radius:3px;padding:3px 10px;cursor:pointer;font-size:20px;}
.filter-clear:hover{border-color:var(--red);color:var(--red);}

/* ═══════════════════════════════════════
   PRINT STYLES — ALL FIXES APPLIED
═══════════════════════════════════════ */
@media print{
    /* Hide everything on-screen */
    .topbar,.page,.navtabs,
    #PRINTDOC,#PRINT_HIST_ALL,#PRINT_SINGLE{display:none !important;}

    /* Show only the correct print block */
    body.print-form     #PRINTDOC      {display:block !important;}
    body.print-hist-all #PRINT_HIST_ALL{display:block !important;}
    body.print-single   #PRINT_SINGLE  {display:block !important;}

    /* Base print styles */
    #PRINTDOC,#PRINT_HIST_ALL,#PRINT_SINGLE{
        font-family:Arial,sans-serif;
        font-size:11px;
        color:#000;
        background:#fff;
        padding:10px 14px;
        max-width:720px;
        margin:0 auto;
        display:block;
    }

    @page {
        size: auto;
        /* Remove top/bottom browser header & footer (URL, date, title) */
        margin: 0mm 10mm 0mm 10mm;
    }
    /* Extra padding on the content itself so it's not flush to edge */
    #PRINTDOC, #PRINT_SINGLE, #PRINT_HIST_ALL {
        padding-top: 8mm !important;
        padding-bottom: 8mm !important;
    }

    body { display:flex; justify-content:center; }

    /* ── MASTER COPY main title at top ── */
    .print-main-title {
        text-align: center;
        font-size: 22px;
        font-weight: 900;
        letter-spacing: 6px;
        text-transform: uppercase;
        color: #000;
        padding: 4px 0;
        margin-bottom: 10px;
    }

    /* ── HEADER: no thick lines, clean & centered ── */
    .ph{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        padding-bottom:8px;
        margin-bottom:10px;
        /* REMOVED: border-bottom:3px double #000 */
    }
    .ph h1{font-size:24px;font-weight:900;letter-spacing:1px;}
    .ph .phc{font-size:12px;color:#333;margin-top:3px;}
    .ph .phr{text-align:right;}

    /* FIX 2: Remove "SARTHI SPORTS — MASTER COPY" badge from top */
    .ph .phbadge{display:none !important;}

    /* FIX 3: Center the MC No at top */
    .ph .phno{ font-size:18px; font-weight:900; margin-top:4px; text-align:right; letter-spacing:2px; }
    .ph .phdate{font-size:12px;color:#555;margin-top:3px;text-align:right;}

    /* Info grid box — even columns */
    .pig{
        display:grid !important;
        grid-template-columns:1fr 1fr;
        border:1px solid #999;
        margin-bottom:10px;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }
    .pigc{display:flex;border:.5px solid #ccc;min-height:24px;}
    .pigl{
        background:#ebebeb !important;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
        font-size:12px;font-weight:700;text-transform:uppercase;
        letter-spacing:.5px;color:#333;
        padding:7px 10px;
        width:130px;min-width:130px;max-width:130px;
        display:flex;align-items:center;
        border-right:1px solid #bbb;
    }
    .pigv{
        padding:7px 10px;
        font-size:13px;
        font-weight:700;
        display:flex;
        align-items:center;
        flex:1;
        word-break:break-word;
        overflow-wrap:anywhere;
    }

    .premark{
        margin-bottom:8px;
        padding:7px 12px;
        border:1px solid #e0c000;
        border-left:3px solid #cc9900;
        background:#fffde7 !important;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
        font-size:13px;
    }

    .ptlbl{
        font-size:13px;font-weight:700;text-transform:uppercase;
        letter-spacing:1px;
        border-bottom:1.5px solid #999;
        padding-bottom:4px;
        margin-bottom:8px;
    }

    /* Product table */
    table.ptt{
        width:100%;border-collapse:collapse;font-size:12px;margin-bottom:8px;
        table-layout:fixed;
    }
    table.ptt thead th{
        background:#222 !important;color:#fff !important;
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
        padding:8px 7px;text-align:center;font-size:12px;
        text-transform:uppercase;letter-spacing:.5px;
        border:1px solid #333;vertical-align:middle;
        word-break:break-word;
    }
    /* Sr(30px) | Product(120px) | Sizes(fill) | Qty(80px) */
    table.ptt thead th:nth-child(1){ width:30px; }
    table.ptt thead th:nth-child(2){ width:120px; }
    table.ptt thead th:nth-child(3){ }
    table.ptt thead th:nth-child(4){ width:95px; white-space:nowrap; }
    table.ptt tbody td{
        border:1px solid #ddd;
        padding:7px 8px;
        vertical-align:middle;
        word-break:break-word;
        overflow-wrap:anywhere;
        font-size:12px;
    }
    table.ptt tbody td:nth-child(1){ text-align:center; font-weight:700; font-size:13px; }
    table.ptt tbody td:nth-child(2){ font-size:13px; font-weight:700; }
    table.ptt tbody td:nth-child(4){ text-align:center; font-weight:900; font-size:14px; width:95px; white-space:nowrap; }
    table.ptt tfoot td{
        background:#e8e8e8 !important;
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
        border:1px solid #bbb;padding:8px 9px;font-weight:700;font-size:13px;
    }
    table.ptt tfoot td:last-child{ text-align:center; font-weight:900; font-size:15px; width:95px; white-space:nowrap; }
    table.ptt tbody tr:nth-child(even) td{
        background:#f7f7f7 !important;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }

    /* Size chips */
    .szchip{
        display:inline-block;
        border:1px solid #ccc;
        border-radius:2px;
        padding:2px 6px;
        margin:2px 3px 2px 0;
        font-size:11px;
        background:#f2f2f2 !important;
        white-space:nowrap;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }
    .sz-sub{font-size:10px;color:#666;font-style:italic;}

    /* FIX 3: Signature — only line, no text labels */
    .psig{
        display:flex;
        justify-content:flex-end;
        margin-top:16px;
        padding-top:10px;
        border-top:1px dashed #bbb;
        page-break-inside:avoid;
    }
    .psigb{text-align:center;min-width:200px;}
    .psigline{border-top:1.5px solid #000;width:180px;margin:0 auto 6px;}
    .psiglbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#000;}
    .psigsub{display:none !important;}
    /* hide all footer text */

    /* FIX 7: Remove page footer text */
    .pfoot{display:none !important;}

    /* ── History all print ── */
    .hall-header{
        padding-bottom:6px;
        margin-bottom:10px;
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        /* REMOVED thick border */
    }
    .hall-header h1{font-size:16px;font-weight:900;}
    .hall-header .hall-sub{font-size:9px;color:#555;}
    .hall-badge{
        background:#222 !important;color:#fff !important;
        font-size:9px;font-weight:700;padding:3px 10px;
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
    }
    .hall-stats{display:flex;gap:16px;margin-bottom:10px;font-size:10px;}
    .hall-stats span{font-weight:700;}

    table.htbl-print{width:100%;border-collapse:collapse;font-size:9px;margin-bottom:10px;}
    table.htbl-print thead th{
        background:#222 !important;color:#fff !important;
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
        padding:5px 6px;text-align:left;font-size:8px;
        text-transform:uppercase;letter-spacing:.5px;border:1px solid #333;
    }
    table.htbl-print tbody td{
        border:1px solid #ddd;padding:4px 6px;vertical-align:top;
        word-break:break-word;overflow-wrap:anywhere;
    }
    table.htbl-print tbody tr:nth-child(even) td{
        background:#f6f6f6 !important;
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
    }
    .hmc-pill{
        background:#222 !important;color:#fff !important;
        font-size:9px;font-weight:700;padding:2px 7px;border-radius:2px;
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
    }
    .hitem-chip{font-size:8px;display:inline-block;border:1px solid #ccc;
        padding:1px 4px;margin:1px 2px 1px 0;border-radius:2px;}
    .hsz-chip{
        display:inline-block;border:1px solid #ccc;padding:1px 4px;
        margin:1px 2px;font-size:7px;background:#f2f2f2 !important;
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
    }

    /* FIX 7: Remove history footer */
    .hall-pfoot{display:none !important;}
}

/* ── BULK SELECT ── */
.chk-th { display:none !important; }
.row-chk-col { display:none !important; }
body.bulk-mode .chk-th { display:table-cell !important; width:38px; text-align:center; }
body.bulk-mode .row-chk-col { display:table-cell !important; text-align:center; vertical-align:middle; }

/* ── Bulk toggle trigger button (⋮) ── */

.bulk-toggle-btn {
    background:var(--panel2); border:2px solid var(--bdr); color:var(--dim);
    border-radius:6px; padding:6px 16px; cursor:pointer; font-size:30px;
    line-height:1; font-weight:900; letter-spacing:3px;
    transition:all .15s; display:inline-flex; align-items:center;
    font-family:'Share Tech Mono',monospace; height:38px;
}
.bulk-toggle-btn:hover { border-color:var(--ac); color:var(--ac); }
.bulk-toggle-btn.active { background:rgba(0,204,255,.18); border-color:var(--ac); color:var(--ac); }

/* ── Bulk toolbar — hidden until bulk mode ── */
#bulk-toolbar {
    display:none;
    align-items:center;
    gap:0;
    background:var(--panel);
    border:1px solid var(--bdr);
    border-radius:6px;
    overflow:hidden;
    margin-bottom:12px;
    white-space:nowrap;
}
body.bulk-mode #bulk-toolbar { display:flex; }

.btbar-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 16px; font-size:13px; font-weight:700;
    border:none; border-right:1px solid var(--bdr);
    cursor:pointer; font-family:'Barlow',sans-serif;
    letter-spacing:.4px; transition:background .15s;
    white-space:nowrap;
}
.btbar-btn:last-child { border-right:none; }
.btbar-chk { width:15px; height:15px; cursor:pointer; accent-color:var(--ac); }

.btbar-selall  { background:var(--panel2); color:var(--ac);  }
.btbar-selall:hover  { background:rgba(0,204,255,.12); }
.btbar-desel   { background:var(--panel2); color:var(--dim); }
.btbar-desel:hover   { background:rgba(255,255,255,.04); }
.btbar-count   {
    padding:9px 16px; font-size:13px; font-weight:700;
    color:var(--txt); background:var(--panel2);
    border-right:1px solid var(--bdr);
    font-family:'Share Tech Mono',monospace;
    white-space:nowrap;
}
.btbar-count span { color:#ff6666; }
.btbar-delete  { background:#3a1515; color:#ff6666; margin-left:auto; }
.btbar-delete:hover  { background:#4a1a1a; color:#ff8888; }
.btbar-clear   { background:var(--panel2); color:var(--ac); border-right:none; }
.btbar-clear:hover   { background:rgba(0,204,255,.10); }

/* hide old floating elements */
#bulk-del-btn  { display:none !important; }
.bulk-bar      { display:none !important; }

tr.bulk-checked td { background:rgba(221,51,51,.09) !important; }
.row-chk { width:16px; height:16px; cursor:pointer; accent-color:#dd3333; }
.select-all-chk { width:14px; height:14px; cursor:pointer; accent-color:var(--ac); }

/* ── Field colors ── */
#F_date       { color: #dce8f0; }        /* dark white — date field */
#F_mcno_num   { color: #dce8f0; }        /* dark white — MC number input */
#F_pno        { color: #dce8f0; }        /* dark white — party no input */
#F_pno::placeholder { color: #dce8f0; opacity: 0.55; } /* dark white placeholder */
#F_ddate      { color: #dce8f0; }        /* dark white — delivery date */
.mc-prefix-span { color: #dce8f0 !important; } /* dark white — MC- prefix span */

</style>
</head>
<body>

<!-- ═══════════════════════════════
     PRINT: FORM / NEW MC
═══════════════════════════════ -->
<div id="PRINTDOC">
    <div class="print-main-title">MASTER COPY</div>
    <div class="ph">
        <div>
            <h1>Sarthi Sports Wear</h1>
            <div class="phc">Ph: 9422107750 &nbsp;|&nbsp; Mob: 7620425141 &nbsp;|&nbsp; Nagpur, Maharashtra</div>
        </div>
        <div class="phr">
            <!-- phbadge hidden via CSS -->
            <div class="phbadge">Master Copy</div>
            <div class="phno" id="p_mcno"></div>
            <!-- FIX 5: Only printed date shown here via JS -->
            <div class="phdate" id="p_date"></div>
        </div>
    </div>
    <div class="pig" id="p_grid"></div>
    <div id="p_remark"></div>
    <div class="ptlbl" id="p_lbl"></div>
    <div id="p_table"></div>
    <div class="psig">
        <div class="psigb">
            <div class="psigline"></div>
            <div class="psiglbl">Authorised Signature</div>
            <div class="psigsub">Sarthi Sports Wear, Nagpur</div>
        </div>
    </div>
    <!-- pfoot hidden via CSS -->
    <div class="pfoot" id="p_foot"></div>
</div>

<!-- ═══════════════════════════════
     PRINT: HISTORY ALL
═══════════════════════════════ -->
<div id="PRINT_HIST_ALL">
    <div class="hall-header">
        <div>
            <h1>Sarthi Sports Wear — Master Copy History</h1>
            <div class="hall-sub">Ph: 9422107750 &nbsp;|&nbsp; Mob: 7620425141 &nbsp;|&nbsp; Nagpur, Maharashtra</div>
        </div>
        <div>
            <div class="hall-badge">HISTORY REPORT</div>
            <div style="font-size:10px;color:#555;margin-top:4px;" id="ha_printdate"></div>
        </div>
    </div>
    <div class="hall-stats" id="ha_stats"></div>
    <div id="ha_table"></div>
    <!-- hall-pfoot hidden via CSS -->
    <div class="hall-pfoot" id="ha_foot"></div>
</div>

<!-- ═══════════════════════════════
     PRINT: SINGLE ROW
═══════════════════════════════ -->
<div id="PRINT_SINGLE">
    <div class="print-main-title">MASTER COPY</div>
    <div class="ph">
        <div>
            <h1>Sarthi Sports Wear</h1>
            <div class="phc">Ph: 9422107750 &nbsp;|&nbsp; Mob: 7620425141 &nbsp;|&nbsp; Nagpur, Maharashtra</div>
        </div>
        <div class="phr">
            <!-- phbadge hidden via CSS -->
            <div class="phbadge">Master Copy</div>
            <div class="phno" id="sr_mcno"></div>
            <!-- FIX 5: Only printed date shown -->
            <div class="phdate" id="sr_date"></div>
        </div>
    </div>
    <div class="pig" id="sr_grid"></div>
    <div id="sr_remark"></div>
    <div class="ptlbl" id="sr_lbl"></div>
    <div id="sr_table"></div>
    <div class="psig">
        <div class="psigb">
            <div class="psigline"></div>
            <div class="psiglbl">Authorised Signature</div>
            <div class="psigsub">Sarthi Sports Wear, Nagpur</div>
        </div>
    </div>
    <!-- pfoot hidden via CSS -->
    <div class="pfoot" id="sr_foot"></div>
</div>

<!-- ═══ TOPBAR ═══ -->
<div class="topbar">
    <span class="brand">&#9889; SARTHI SPORTS WEAR &mdash; MASTER COPY</span>
    <div class="pills">
        <span class="pill">Date: <span id="ld"></span></span>
        <span class="pill">Time: <span id="lt"></span></span>
        <?php if($page === 'history'): ?>
        <button class="bulk-toggle-btn" id="bulkToggleBtn" onclick="toggleBulkMode()" title="Bulk Select">&#8942;</button>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ NAV TABS ═══ -->
<div class="navtabs">
    <a class="navtab <?php echo $page==='form'    ? 'active':'' ?>" href="master.php?page=form">
        &#128196; New Master Copy
    </a>
    <a class="navtab <?php echo $page==='history' ? 'active':'' ?>" href="master.php?page=history">
        &#128202; History
        <span class="nbadge"><?php echo $total_mc; ?></span>
    </a>
</div>

<?php if ($page === 'form'): ?>
<div class="page">

    <?php if ($saved): ?>
    <div class="bnr bnr-ok">&#10004; Saved: <strong><?php echo $saved_mc_no_safe; ?></strong>
        &nbsp;&mdash;&nbsp;
        <a href="master.php?page=history" style="color:var(--ac2);">View History &rarr;</a>
    </div>
    <?php endif; ?>
    <?php if ($save_error !== ''): ?>
    <div class="bnr bnr-er">&#10005; DB Error: <?php echo $save_error_safe; ?></div>
    <?php endif; ?>

    <div class="shdr">
        <div>
            <h1>Sarthi Sports Wear</h1>
            <div class="sub">Ph: 9422107750 &nbsp;|&nbsp; Mob: 7620425141</div>
        </div>
       <div class="mc-badge">
            <div class="lbl">Master Copy No.</div>
            <div class="num" id="BADGE_mcno"><?php echo $mc_no_safe; ?></div>
        </div>
    </div>

    <div class="slbl">Details</div>
    <div class="card">
        <div class="fg fg2">
            <div class="fld">
                <label>Date</label>
                <input type="date" id="F_date">
            </div>
            <div class="fld">
                <label>Master Copy No.</label>
                <div style="display:flex;align-items:center;gap:6px;">
                    <!-- ← FIXED: removed inline color:var(--dim), added class mc-prefix-span so green applies -->
                    <span class="mc-prefix-span" style="background:var(--ibg);border:1px solid var(--bdr);border-radius:4px 0 0 4px;font-family:'Share Tech Mono',monospace;font-size:25px;padding:9px 12px;white-space:nowrap;border-right:none;">MC-</span>
                    <input type="text" id="F_mcno_num" value="<?php echo ltrim(str_replace('MC-','',$mc_no_safe),'0') ?: '1'; ?>"
                        style="border-radius:0 4px 4px 0;width:100%;"
                        oninput="syncMC()" placeholder="0001">
                </div>
                <input type="hidden" id="F_mcno" value="<?php echo $mc_no_safe; ?>">
            </div>
            <div class="fld">
                <label>Party No</label>
                <input type="text" id="F_pno" placeholder="Enter party no.">
            </div>
            <div class="fld">
                <label>Delivery Date</label>
                <input type="date" id="F_ddate">
            </div>
            <div class="fld span2">
                <label>Remarks / Notes</label>
                <input type="text" id="F_remarks" placeholder="Special instructions...">
            </div>
        </div>
    </div>

    <div class="slbl">Products &amp; Sizes</div>
    <div class="itbl-wrap">
        <div class="ith">
            <span>#</span><span>Product</span><span>Pattern</span><span>Actions</span>
        </div>
        <div class="itb" id="ITB"></div>
    </div>

    <div class="addbar">
        <button class="btn btn-blu btn-sm" onclick="addRow()">+ Add Product</button>
    </div>
    <div class="abar">
        <button class="btn btn-grn"  onclick="doSave()">&#128190; SAVE</button>
        <button class="btn btn-blu"  onclick="doPrint()">&#128424; PRINT</button>
        <button class="btn btn-gry"  onclick="doClear()">&#8635; CLEAR</button>
    </div>
</div>
<?php endif; ?>

<?php if ($page === 'history'): ?>
<div class="page">

    <div class="summary-stats">
        <div class="stat-box"><div class="sv"><?php echo $total_mc; ?></div><div class="sl">Total Records</div></div>
        <div class="stat-box"><div class="sv"><?php echo $today_mc; ?></div><div class="sl">Today</div></div>
        <div class="stat-box"><div class="sv"><?php echo $week_mc; ?></div><div class="sl">This Week</div></div>
    </div>

    <!-- ════ DATE FILTER PANEL ════ -->
    <?php
    $active_filter_label = '';
    if ($filter_type==='day' && $filter_date)
        $active_filter_label = 'Day: '.date('d M Y', strtotime($filter_date));
    elseif ($filter_type==='month' && $filter_month)
        $active_filter_label = 'Month: '.date('F Y', strtotime($filter_month.'-01'));
    elseif ($filter_type==='year' && $filter_year)
        $active_filter_label = 'Year: '.$filter_year;
    ?>
    <div class="dfilter-wrap">
        <div class="dfilter-tabs">
            <button class="dftab <?php echo (!$filter_type||$filter_type==='year')?'active':''; ?>" onclick="switchTab('year')">&#128197; By Year</button>
            <button class="dftab <?php echo $filter_type==='month'?'active':''; ?>" onclick="switchTab('month')">&#128198; By Month</button>
            <button class="dftab <?php echo $filter_type==='day'?'active':''; ?>" onclick="switchTab('day')">&#128467; By Day</button>
        </div>
        <div class="dfilter-body">

            <!-- YEAR TAB -->
            <div id="tab-year" class="df-tab-content" style="<?php echo (!$filter_type||$filter_type==='year')?'':'display:none'; ?>">
                <div class="year-pills">
                <?php if(empty($year_data)): ?>
                    <span style="color:var(--dim);font-size:12px;">No data yet.</span>
                <?php else: foreach($year_data as $yd): ?>
                    <a href="master.php?page=history&ftype=year&fyear=<?php echo $yd['yr']; ?>"
                       class="yr-pill <?php echo ($filter_type==='year'&&$filter_year==$yd['yr'])?'active':''; ?>">
                        <?php echo $yd['yr']; ?>
                        <span class="yc"><?php echo $yd['cnt']; ?> MC</span>
                    </a>
                <?php endforeach; endif; ?>
                    <a href="master.php?page=history" class="yr-pill <?php echo !$filter_type?'active':''; ?>">
                        All &nbsp;<span class="yc"><?php echo $total_mc; ?> MC</span>
                    </a>
                </div>
            </div>

            <!-- MONTH TAB -->
            <div id="tab-month" class="df-tab-content" style="<?php echo $filter_type==='month'?'':'display:none'; ?>">
                <?php if(empty($month_data)): ?>
                    <span style="color:var(--dim);font-size:12px;">No data yet.</span>
                <?php else: ?>
                <div class="month-grid">
                <?php foreach($month_data as $md):
                    $mLabel = date('M', strtotime($md['ym'].'-01'));
                    $mYear  = date('Y', strtotime($md['ym'].'-01'));
                    $isAct  = ($filter_type==='month' && $filter_month===$md['ym']);
                ?>
                    <a href="master.php?page=history&ftype=month&fmonth=<?php echo $md['ym']; ?>"
                       class="mon-cell <?php echo $isAct?'active':''; ?>" style="text-decoration:none;">
                        <div class="mc"><?php echo $md['cnt']; ?></div>
                        <div class="mn"><?php echo $mLabel; ?></div>
                        <div class="my"><?php echo $mYear; ?></div>
                    </a>
                <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- DAY TAB / CALENDAR -->
            <div id="tab-day" class="df-tab-content" style="<?php echo $filter_type==='day'?'':'display:none'; ?>">
                <div class="cal-wrap">
                    <div class="cal-nav">
                        <?php
                        $calParts = explode('-', $cal_month);
                        $calY = (int)$calParts[0]; $calM = (int)$calParts[1];
                        $prevM = $calM===1 ? sprintf('%04d-12',$calY-1) : sprintf('%04d-%02d',$calY,$calM-1);
                        $nextM = $calM===12 ? sprintf('%04d-01',$calY+1) : sprintf('%04d-%02d',$calY,$calM+1);
                        ?>
                        <a href="master.php?page=history&ftype=day&calmonth=<?php echo $prevM; ?>" class="cal-nav-btn">&#8592;</a>
                        <span class="cal-nav-title"><?php echo date('F Y', strtotime($cal_month.'-01')); ?></span>
                        <a href="master.php?page=history&ftype=day&calmonth=<?php echo $nextM; ?>" class="cal-nav-btn">&#8594;</a>
                    </div>
                    <div class="cal-grid">
                        <?php $dows = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                        foreach($dows as $dw) echo '<div class="cal-dow">'.$dw.'</div>'; ?>
                        <?php
                        $firstDay = date('w', strtotime($cal_month.'-01'));
                        $daysInMonth = date('t', strtotime($cal_month.'-01'));
                        $todayStr = date('Y-m-d');
                        for($b=0;$b<$firstDay;$b++) echo '<div class="cal-day empty"></div>';
                        for($d=1;$d<=$daysInMonth;$d++):
                            $ds = sprintf('%04d-%02d-%02d',$calY,$calM,$d);
                            $cnt2 = isset($day_data[$ds]) ? $day_data[$ds] : 0;
                            $isToday = ($ds===$todayStr);
                            $isAct2  = ($filter_type==='day' && $filter_date===$ds);
                            $cls = 'cal-day';
                            if($cnt2>0) $cls.=' has-data';
                            if($isToday) $cls.=' today-cell';
                            if($isAct2) $cls.=' active';
                        ?>
                        <?php if($cnt2>0): ?>
                        <a href="master.php?page=history&ftype=day&fdate=<?php echo $ds; ?>" class="<?php echo $cls; ?>" style="text-decoration:none;">
                        <?php else: ?>
                        <div class="<?php echo $cls; ?>">
                        <?php endif; ?>
                            <div class="cd"><?php echo $d; ?></div>
                            <?php if($cnt2>0): ?><div class="cc"><?php echo $cnt2; ?></div><?php endif; ?>
                        <?php if($cnt2>0): ?></a><?php else: ?></div><?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($active_filter_label): ?>
    <div class="filter-active-bar">
        &#128269; Showing results for: <strong><?php echo $active_filter_label; ?></strong>
        &nbsp;&mdash;&nbsp; <?php echo count($hist_rows); ?> record(s) found
        <a href="master.php?page=history" class="filter-clear">&#10005; Clear Filter</a>
    </div>
    <?php endif; ?>

    <div class="hist-header">
        <div class="hist-title">&#128202; Master Copy History</div>
        <div class="hist-header-right">
            <button class="btn btn-pur btn-sm" onclick="printAllHistory()" title="Print full history report">
                &#128424; Print All History
            </button>
            <form method="GET" action="master.php" class="search-bar">
                <input type="hidden" name="page" value="history">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search MC No, Phone, Remarks...">
                <button type="submit" class="btn btn-blu btn-sm">&#128269;</button>
                <?php if ($search): ?>
                <a href="master.php?page=history" class="btn btn-gry btn-sm">&#10005;</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- ── Bulk Toolbar ── -->
    <div id="bulk-toolbar">
        <button class="btbar-btn btbar-selall" onclick="selectAllRows()">&#9745; Select All</button>
        <button class="btbar-btn btbar-desel"  onclick="deselectAllRows()">&#9744; Deselect All</button>
        <div class="btbar-count">Selected: <span id="sel-count">0</span></div>
        <button class="btbar-btn btbar-delete" onclick="deleteSelected()">&#128465; Delete Selected</button>
        <button class="btbar-btn btbar-clear"  onclick="toggleBulkMode()">&#10005; Clear</button>
    </div>

    <form id="bulkDelForm" method="POST" action="master.php?page=history" style="display:none;">
        <input type="hidden" name="action" value="bulk_delete">
        <input type="hidden" name="bulk_ids" id="bulk_ids_input" value="">
    </form>

    <?php if (empty($hist_rows)): ?>
    <div class="empty-state">
        <div class="ei">&#128196;</div>
        <p><?php echo $search ? 'No records found for &ldquo;'.htmlspecialchars($search).'&rdquo;' : 'No master copies saved yet. Create your first one!'; ?></p>
    </div>
    <?php else: ?>

    <script>
    var HIST_DATA = <?php
        $js_rows = array();
        foreach ($hist_rows as $hr) {
            $js_rows[] = array(
                'id'            => $hr['id'],
                'mc_no'         => $hr['mc_no'],
                'party_no'      => $hr['party_no'],
                'order_date'    => $hr['order_date'],
                'delivery_date' => $hr['delivery_date'],
                'remarks'       => $hr['remarks'],
                'created_at'    => $hr['created_at'],
                'items_json'    => $hr['items_json'],
            );
        }
        echo json_encode($js_rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    ?>;
    var TOTAL_MC  = <?php echo $total_mc; ?>;
    var TODAY_MC  = <?php echo $today_mc; ?>;
    var WEEK_MC   = <?php echo $week_mc; ?>;
    </script>

    <div style="overflow-x:auto;">
    <table class="htbl">
       <thead><tr>
            <th class="chk-th">
                <input type="checkbox" class="select-all-chk" id="selectAllChk"
                       onchange="toggleSelectAll(this)" title="Select All">
            </th>
            <th>MC No.</th>
            <th>Order Date</th>
            <th>Delivery Date</th>
            <th>Party No.</th><!-- ← FIXED: was "Party Phone" -->
            <th>Products &amp; Qty</th>
            <th>Remarks</th>
            <th>Action</th>
        </tr></thead>
        <tbody>
        <?php foreach ($hist_rows as $hr):
            $items_data = @json_decode($hr['items_json'], true);
            if (!is_array($items_data)) $items_data = array();
            $grand_qty = 0;
            foreach ($items_data as $it) $grand_qty += isset($it['totalQty']) ? (int)$it['totalQty'] : 0;
        ?>
        <tr id="row_<?php echo $hr['id']; ?>">
            <td class="row-chk-col">
                <input type="checkbox" class="row-chk"
                       data-id="<?php echo $hr['id']; ?>"
                       onchange="onRowCheck(this)">
            </td>
            <td><span class="mc-pill"><?php echo htmlspecialchars($hr['mc_no']); ?></span></td>
            <td class="date-col" style="color:#dce8f0;font-weight:600;"><?php echo $hr['order_date'] ?: '—'; ?></td>
            <td class="date-col" style="color:<?php echo $hr['delivery_date'] ? 'var(--gold)' : 'var(--dim)' ?>">
                <?php echo $hr['delivery_date'] ?: '—'; ?>
            </td>
            <td style="font-size:20px;font-weight:700;color:#dce8f0;"><?php echo $hr['party_no'] ? htmlspecialchars($hr['party_no']) : '<span style="color:var(--dim)">—</span>'; ?></td>
            <td>
                <?php foreach ($items_data as $it): if(empty($it['product'])) continue; ?>
                <span class="item-chip"><?php echo htmlspecialchars($it['product']); ?>
                    <span style="color:var(--ac2);font-family:'Share Tech Mono',monospace;"> ×<?php echo (int)$it['totalQty']; ?></span>
                </span>
                <?php endforeach; ?>
                <?php if(count($items_data)>0): ?>
                <br><span style="font-size:10px;color:var(--dim);">
                    <?php echo count($items_data); ?> item(s) — Total: <b style="color:var(--ac)"><?php echo $grand_qty; ?></b>
                </span>
                <br><button class="expand-btn" onclick="toggleDetail(<?php echo $hr['id']; ?>)">&#9660; View Sizes</button>
                <?php endif; ?>
            </td>
            <td style="font-size:11px;color:var(--dim);max-width:150px;">
                <?php echo $hr['remarks'] ? htmlspecialchars($hr['remarks']) : '<span style="color:#333">—</span>'; ?>
            </td>
            <td>
                <div class="action-group">
                    <button class="print-row-btn" onclick="printSingleRow(<?php echo $hr['id']; ?>)" title="Print this master copy">
                        &#128424; Print
                    </button>
                    <button class="del-link"
                       onclick="showCustomConfirm('Delete <?php echo htmlspecialchars($hr['mc_no']); ?>? This cannot be undone.', function(){ window.location='master.php?delete_id=<?php echo $hr['id']; ?>&page=history'; })">
                       &#128465; Del
                    </button>
                </div>
            </td>
        </tr>
        <tr class="detail-row" id="detail_<?php echo $hr['id']; ?>">
            <td colspan="8">
                <div class="detail-inner">
                <?php foreach ($items_data as $it):
                    if(empty($it['product'])) continue;
                    $specs = array();
                    if(!empty($it['color']))   $specs[] = $it['color'];
                    if(!empty($it['fabric']))  $specs[] = $it['fabric'];
                    if(!empty($it['pattern'])) $specs[] = $it['pattern'];
                ?>
                <div class="di-card">
                    <div class="di-prod">&#9654; <?php echo htmlspecialchars($it['product']); ?>
                        <span style="color:var(--ac);font-size:12px;font-family:'Share Tech Mono',monospace;"> &mdash; Qty: <?php echo (int)$it['totalQty']; ?></span>
                    </div>
                    <?php if($specs): ?>
                    <div class="di-meta"><?php echo htmlspecialchars(implode(' / ',$specs)); ?></div>
                    <?php endif; ?>
                    <?php if(!empty($it['note'])): ?>
                    <div class="di-meta" style="font-style:italic;color:#888;">&#128221; <?php echo htmlspecialchars($it['note']); ?></div>
                    <?php endif; ?>
                    <div class="di-sizes">
                    <?php if(!empty($it['sizes'])): ?>
                        <?php foreach($it['sizes'] as $sz):
                            $szExtra = array();
                            if(!empty($sz['color']))  $szExtra[] = $sz['color'];
                            if(!empty($sz['fabric'])) $szExtra[] = $sz['fabric'];
                        ?>
                        <span class="sz-chip-h">
                            <b><?php echo htmlspecialchars($sz['size']); ?></b> &mdash; <?php echo (int)$sz['qty']; ?>
                            <?php if($szExtra): ?>
                            <span style="color:var(--dim);font-size:9px;"> (<?php echo htmlspecialchars(implode('/',$szExtra)); ?>)</span>
                            <?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color:var(--dim);font-size:11px;">No sizes</span>
                    <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
var PRODUCTS=['','T-Shirt','Tracksuit','Half Pant','Lower','Blazer','Tie','Belt','Socks','Shoes','Bag','Tunic','Frock','Skirt','Salwar-Suit','Waist Coat','Jacket',];
var NO_SIZE_PRODUCTS={};
var BELT_PRODUCTS={};
var BAG_PRODUCTS={};
var DEFAULT_SIZES={
    'T-Shirt':         ['18','20','22','24','26','28','30','32','34','36','38','40','42','44','46'],
    'Tracksuit':       ['18','20','22','24','26','28','30','32','34','36','38','40','42','44','46'],
    'Half Pant':       ['10','11','12','13','14','15','16','17','18','20','22','24','26','28','30'],
    'Lower':           ['18','20','22','24','26','28','30','32','34','36','38','40','42','44','46'],
    'Blazer':          ['18','20','22','24','26','28','30','32','34','36','38','40','42','44','46'],
    'Tie':             ['10','12','14','16','18','20'],
    'Socks':           ['0','1','2','3','4','5','6','7','Free Size'],
    'Shoes':           ['6','7','8','9','10','11'],
    'Tunic':           ['20','22','24','26','28','30','32','34','36','38','40','42','44','46'],
    'Frock':           ['20','22','24','26','28','30','32','34','36','38','40','42','44','46'],
    'Skirt':           ['10','12','14','16','18','20','22','24','26','28','30','32','34','36'],
    'Salwar-Suit':     ['32','34','36','38','40','42','44','46'],
    'Waist Coat':      ['26','28','30','32','34','36','38','40','42','44','46'],
    'Jacket':          ['18','20','22','24','26','28','30','32','34','36','38','40','42','44','46'],
};
var PATTERNS={
    'T-Shirt':         ['Round Neck','Kollar','Plain Pippin','Double Pippin'],
    'Tracksuit':       ['Plain','Single Pippin','Double Pippin'],
    'Half Pant':       ['Plain','Lining','Check'],
    'Lower':           ['Plain','Single Pippin','Double Pippin'],
    'Blazer':          ['Plain'],
    'Tie':             ['Plain','Cross Lining','Work']
};
var COLORS=['','Navy Blue','Black','Light Grey','Dark Grey','White','Red','Yellow','Blue','Green','Samre','Sky Blue','Kiwi (Sea Green)','Maroon','Orange','Lemon','Multi Color',];
var FABRICS=['','P.P','H/C','Mpp','DT (Dot Net)','S.P','Metti','Peanut','Shirting','Suiting','Fleece','Dri-Fit',];

function zeroPad(n){return(n<10?'0':'')+n;}
function tick(){
    var d=new Date();
    var ld=document.getElementById('ld'),lt=document.getElementById('lt');
    if(ld)ld.textContent=zeroPad(d.getDate())+'/'+zeroPad(d.getMonth()+1)+'/'+d.getFullYear();
    if(lt)lt.textContent=zeroPad(d.getHours())+':'+zeroPad(d.getMinutes())+':'+zeroPad(d.getSeconds());
}
setInterval(tick,1000);tick();

var dateEl=document.getElementById('F_date');
if(dateEl)dateEl.value=new Date().toISOString().split('T')[0];

(function(){
    var inp = document.getElementById('F_mcno');
    var badge = document.getElementById('BADGE_mcno');
    if(inp && badge){
        inp.addEventListener('input', function(){
            badge.textContent = inp.value || '--';
        });
    }
})();

/* ── Auto-print after save-redirect ── */
(function(){
    var urlP=new URLSearchParams(window.location.search);
    if(urlP.get('do_print')==='1'){
        var pd=sessionStorage.getItem('pendingPrint');
        if(!pd)return;
        var data=JSON.parse(pd);
        sessionStorage.removeItem('pendingPrint');
        setTimeout(function(){
            var now=new Date();
            var zeroPad=function(n){return(n<10?'0':'')+n;};
            var pdate=zeroPad(now.getDate())+'/'+zeroPad(now.getMonth()+1)+'/'+now.getFullYear();
            var ptime=zeroPad(now.getHours())+':'+zeroPad(now.getMinutes());

            document.getElementById('p_mcno').textContent=data.mc_no||'--';
            /* FIX 5: Only printed date/time in header */
            document.getElementById('p_date').textContent='Printed: '+pdate+' '+ptime;

            var gridRows=[
                ['Master Copy No.',data.mc_no||'--'],
                ['Order Date',data.odate||'--'],
                ['Party No.',data.pno||'--'],
                ['Delivery Date',data.ddate||'--'],
                ['Remarks',data.remarks||'--']
            ];
            var gh='';
            for(var i=0;i<gridRows.length;i++) gh+='<div class="pigc"><div class="pigl">'+gridRows[i][0]+'</div><div class="pigv">'+gridRows[i][1]+'</div></div>';
            document.getElementById('p_grid').innerHTML=gh;
            document.getElementById('p_remark').innerHTML=data.remarks?'<div class="premark">&#128221; <strong>Note:</strong> '+data.remarks+'</div>':'';
            var res=buildItemTableHTML(data.items);
            document.getElementById('p_lbl').textContent='\u25ba Order Details - '+res.srNo+' Item(s)  |  Total Qty: '+res.grandQty;
            document.getElementById('p_table').innerHTML=
                '<table class="ptt"><colgroup><col style="width:32px"><col style="width:120px"><col><col style="width:95px"></colgroup><thead><tr>'
                +'<th style="width:32px;text-align:center;">Sr.</th>'
                +'<th style="text-align:left;width:120px;">Product / Description</th>'
                +'<th style="text-align:left;">Size Breakup (Size &mdash; Qty | Color / Fabric)</th>'
                +'<th style="width:95px;white-space:nowrap;">Total Qty</th>'
                +'</tr></thead><tbody>'+res.tbody+'</tbody>'
                +'<tfoot><tr><td colspan="3" style="text-align:right;letter-spacing:1px;text-transform:uppercase;">Grand Total Quantity</td>'
                +'<td style="text-align:center;font-size:15px;font-weight:900;width:95px;white-space:nowrap;">'+res.grandQty+'</td></tr></tfoot></table>';
            /* p_foot is hidden via CSS — set anyway */
            document.getElementById('p_foot').textContent='';
            document.body.className='print-form';
            var _ot=document.title;document.title=' ';
            var _ou=window.location.href;history.replaceState(null,'','');
            setTimeout(function(){window.print();document.body.className='';document.title=_ot;history.replaceState(null,'',_ou);},200);
        },300);
    }
})();

var RC=0,SC=0;
if(document.getElementById('ITB')){addRow();addRow();addRow();}

function buildColorOpts(){
    var o='';
    for(var i=0;i<COLORS.length;i++) o+='<option value="'+COLORS[i]+'">'+(COLORS[i]||'-- Color --')+'</option>';
    return o;
}
function buildFabricOpts(){
    var o='';
    for(var i=0;i<FABRICS.length;i++) o+='<option value="'+FABRICS[i]+'">'+(FABRICS[i]||'-- Fabric --')+'</option>';
    return o;
}

function addRow(){
    RC++;var rn=RC;
    var body=document.getElementById('ITB');
    var pOpts='';
    for(var i=0;i<PRODUCTS.length;i++) pOpts+='<option value="'+PRODUCTS[i]+'">'+(PRODUCTS[i]||'-- Select Product --')+'</option>';
    var wrap=document.createElement('div');
    wrap.className='pwrap';wrap.id='W'+rn;
    wrap.innerHTML=
        '<div class="prow" id="PR'+rn+'">'
      +   '<span class="srn">'+rn+'</span>'
      +   '<select id="P'+rn+'" onchange="onProd('+rn+')">'+pOpts+'</select>'
      +   '<select id="PT'+rn+'"><option value="">-- Pattern --</option></select>'
      +   '<div class="ract">'
      +     '<button id="SZB'+rn+'" class="togbtn" onclick="toggleSz('+rn+')">Sizes</button>'
      +     '<button class="delbtn" onclick="delRow('+rn+')">&#10005;</button>'
      +   '</div>'
      + '</div>'
      + '<div class="nrow" id="NR'+rn+'"><span class="nrow-lbl">&#128221; Notes / Special Instructions</span><textarea id="N'+rn+'" placeholder="Enter notes, special instructions, or remarks for this product..."></textarea></div>'
      + '<div class="szpan" id="SZP'+rn+'">'
      +   '<div class="sz-head">'
      +     '<span>Size</span><span>Qty</span>'
      +     '<span>Color (per size)</span><span>Fabric (per size)</span>'
      +     '<span></span>'
      +   '</div>'
      +   '<div class="szinn" id="SZI'+rn+'"></div>'
      +   '<button class="addszbtn" onclick="addSzRow('+rn+')">+ Add Size</button>'
      + '</div>'
      + '<div id="BELT_EXT'+rn+'" style="display:none;flex-direction:column;gap:10px;padding:12px 16px 14px 66px;background:#000;border-top:1px dashed #1a1a1a;">'
      +   '<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">'
      +     '<div style="display:flex;flex-direction:column;gap:4px;">'
      +       '<label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--lbl);">Color</label>'
      +       '<select id="BELT_COL'+rn+'" style="background:#0d1a10;border:2px solid #1a3322;color:#c8e0f4;font-family:\'Barlow\',sans-serif;font-size:15px;height:44px;border-radius:6px;padding:0 10px;min-width:160px;">'
      +         '<option value="">-- Color --</option><option>Black</option><option>White</option><option>Navy Blue</option><option>Brown</option><option>Red</option><option>Custom</option>'
      +       '</select>'
      +     '</div>'
      +     '<div style="display:flex;flex-direction:column;gap:4px;">'
      +       '<label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--lbl);">Qty</label>'
      +       '<input type="number" id="DQ'+rn+'" min="0" placeholder="0" style="width:100px;background:#111;border:2px solid #1a3322;border-radius:6px;color:#00ffaa;font-family:\'Share Tech Mono\',monospace;font-size:24px;font-weight:700;padding:6px 10px;text-align:center;height:44px;">'
      +     '</div>'
      +   '</div>'
      + '</div>'
      + '<div id="BAG_EXT'+rn+'" style="display:none;flex-direction:column;gap:10px;padding:12px 16px 14px 66px;background:#000;border-top:1px dashed #1a1a1a;">'
      +   '<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">'
      +     '<div style="display:flex;flex-direction:column;gap:4px;">'
      +       '<label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--lbl);">Color</label>'
      +       '<select id="BAG_COL'+rn+'" style="background:#0d1a10;border:2px solid #1a3322;color:#c8e0f4;font-family:\'Barlow\',sans-serif;font-size:15px;height:44px;border-radius:6px;padding:0 10px;min-width:140px;">'
      +         '<option value="">-- Color --</option><option>Black</option><option>White</option><option>Custom</option>'
      +       '</select>'
      +     '</div>'
      +     '<div style="display:flex;flex-direction:column;gap:4px;">'
      +       '<label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--lbl);">Fabric</label>'
      +       '<select id="BAG_FAB'+rn+'" style="background:#0d1a10;border:2px solid #1a3322;color:#c8e0f4;font-family:\'Barlow\',sans-serif;font-size:15px;height:44px;border-radius:6px;padding:0 10px;min-width:140px;">'
      +         '<option value="">-- Fabric --</option><option>Leather</option><option>Raisin</option><option>Custom</option>'
      +       '</select>'
      +     '</div>'
      +     '<div style="display:flex;flex-direction:column;gap:4px;">'
      +       '<label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--lbl);">Qty</label>'
      +       '<input type="number" id="DQ'+rn+'" min="0" placeholder="0" style="width:100px;background:#111;border:2px solid #1a3322;border-radius:6px;color:#00ffaa;font-family:\'Share Tech Mono\',monospace;font-size:24px;font-weight:700;padding:6px 10px;text-align:center;height:44px;">'
      +     '</div>'
      +   '</div>'
      + '</div>';
    body.appendChild(wrap);
}

function onProd(rn){
    var prod=document.getElementById('P'+rn).value;
    var patSel=document.getElementById('PT'+rn);
    var pats=PATTERNS[prod]||[];
    var ph='<option value="">-- Pattern --</option>';
    for(var i=0;i<pats.length;i++) ph+='<option value="'+pats[i]+'">'+pats[i]+'</option>';
    patSel.innerHTML=ph;
    var nr=document.getElementById('NR'+rn);
    if(nr)nr.className=prod?'nrow open':'nrow';
    var panel=document.getElementById('SZP'+rn);
    var szb=document.getElementById('SZB'+rn);
    var beltExt=document.getElementById('BELT_EXT'+rn);
    var bagExt=document.getElementById('BAG_EXT'+rn);
    if(!prod){
        panel.className='szpan';
        if(szb) szb.style.display='';
        if(beltExt) beltExt.style.display='none';
        if(bagExt)  bagExt.style.display='none';
        return;
    }
    if(beltExt) beltExt.style.display='none';
if(bagExt)  bagExt.style.display='none';
if(szb) szb.style.display='';
panel.className='szpan open';
var inn=document.getElementById('SZI'+rn);
if(inn)inn.innerHTML='';
}
function toggleSz(rn){
    var p=document.getElementById('SZP'+rn);
    p.className=(p.className.indexOf('open')>=0)?'szpan':'szpan open';
}

function onSzColorChange(sel){
    var wrap=sel.parentNode;
    var custom=wrap.querySelector('input.sz-color-custom');
    if(!custom)return;
    if(sel.value==='Custom'){
        sel.classList.add('custom-open');
        custom.classList.add('visible');
        custom.focus();
    } else {
        sel.classList.remove('custom-open');
        custom.classList.remove('visible');
        custom.value='';
    }
}
function onSzFabricChange(sel){
    var wrap=sel.parentNode;
    var custom=wrap.querySelector('input.sz-fabric-custom');
    if(!custom)return;
    if(sel.value==='Custom'){
        sel.classList.add('custom-open');
        custom.classList.add('visible');
        custom.focus();
    } else {
        sel.classList.remove('custom-open');
        custom.classList.remove('visible');
        custom.value='';
    }
}

function addSzRow(rn){
    SC++;var sid=SC;
    var inn=document.getElementById('SZI'+rn);
    if(!inn)return;
    var row=document.createElement('div');
    row.className='sze';row.id='SZE'+sid;
    var cOpts=buildColorOpts();
    var fOpts=buildFabricOpts();
    row.innerHTML=
        '<input type="text" class="sz-val" placeholder="e.g. 18, M, XL">'
      + '<input type="number" class="sz-qty" min="0" placeholder="Qty">'
      + '<div class="sz-color-wrap">'
      +   '<select class="sz-color-sel" onchange="onSzColorChange(this)">'+cOpts+'</select>'
      +   '<input type="text" class="sz-color-custom" placeholder="✏️ or type your own color..." style="display:block;margin-top:4px;background:#060f08;border:2px solid #1a3322;color:#00ffaa;font-family:\'Share Tech Mono\',monospace;font-size:14px;height:34px;border-radius:6px;padding:0 8px;width:100%;">'
      + '</div>'
      + '<div class="sz-fabric-wrap">'
      +   '<select class="sz-fabric-sel" onchange="onSzFabricChange(this)">'+fOpts+'</select>'
      +   '<input type="text" class="sz-fabric-custom" placeholder="✏️ or type your own fabric..." style="display:block;margin-top:4px;background:#060f08;border:2px solid #1a3322;color:#00ffaa;font-family:\'Share Tech Mono\',monospace;font-size:14px;height:34px;border-radius:6px;padding:0 8px;width:100%;">'
      + '</div>'
      + '<button class="rm-sz" onclick="var e=document.getElementById(\'SZE'+sid+'\');e.parentNode.removeChild(e);">&#10005;</button>';
    inn.appendChild(row);
    row.querySelector('input.sz-val').focus();
}
function delRow(rn){var el=document.getElementById('W'+rn);if(el)el.parentNode.removeChild(el);}

function getSzColorVal(sze){
    var sel=sze.querySelector('select.sz-color-sel');
    var custom=sze.querySelector('input.sz-color-custom');
    if(custom&&custom.value.trim()!=='') return custom.value.trim();
    return sel?sel.value:'';
}
function getSzFabricVal(sze){
    var sel=sze.querySelector('select.sz-fabric-sel');
    var custom=sze.querySelector('input.sz-fabric-custom');
    if(custom&&custom.value.trim()!=='') return custom.value.trim();
    return sel?sel.value:'';
}
function collectData(){
    var items=[];
    var wraps=document.querySelectorAll('#ITB .pwrap');
    for(var w=0;w<wraps.length;w++){
        var rn=parseInt(wraps[w].id.replace('W',''));
        var pEl=document.getElementById('P'+rn);
        if(!pEl||!pEl.value)continue;
        var prod=pEl.value;
        var col='';
        var fab='';
        var pat=document.getElementById('PT'+rn)?document.getElementById('PT'+rn).value:'';
        var note=document.getElementById('N'+rn)?document.getElementById('N'+rn).value:'';
        var sizes=[];
        var totalQty=0;
        if(NO_SIZE_PRODUCTS[prod]){
            var dqEl=document.getElementById('DQ'+rn);
            totalQty=parseInt(dqEl?dqEl.value:'0')||0;
            var beltColEl=document.getElementById('BELT_COL'+rn);
            if(beltColEl&&beltColEl.value) col=beltColEl.value;
            var bagColEl=document.getElementById('BAG_COL'+rn);
            var bagFabEl=document.getElementById('BAG_FAB'+rn);
            if(bagColEl&&bagColEl.value)  col=bagColEl.value;
            if(bagFabEl&&bagFabEl.value)  fab=bagFabEl.value;
        } else {
            var inn=document.getElementById('SZI'+rn);
            if(inn){
                var entries=inn.querySelectorAll('.sze');
                for(var e=0;e<entries.length;e++){
                    var szIn=entries[e].querySelector('input.sz-val');
                    var qIn=entries[e].querySelector('input.sz-qty');
                    var sz=(szIn?szIn.value:'').replace(/^\s+|\s+$/g,'');
                    var qty=parseInt(qIn?qIn.value:'0')||0;
                    var szColor=getSzColorVal(entries[e]);
                    var szFabric=getSzFabricVal(entries[e]);
                    if(sz)sizes.push({size:sz,qty:qty,color:szColor,fabric:szFabric});
                }
            }
            for(var s=0;s<sizes.length;s++)totalQty+=sizes[s].qty;
        }
        items.push({product:prod,color:col,fabric:fab,pattern:pat,note:note,sizes:sizes,totalQty:totalQty});
    }
    return items;
}

function buildItemTableHTML(items){
    var grandQty=0,srNo=0,tbody='';
    for(var k=0;k<items.length;k++){
        var it=items[k];if(!it.product)continue;
        srNo++;grandQty+=it.totalQty;
        var bg=(srNo%2===0)?'#f6f6f6':'#fff';
        var spec='';
        if(it.pattern&&it.fabric)spec=it.pattern+' / '+it.fabric;
        else if(it.pattern)spec=it.pattern;
        else if(it.fabric)spec=it.fabric;
        var chips='';
        for(var s=0;s<it.sizes.length;s++){
            var szExtra='';
            if(it.sizes[s].color)  szExtra+=it.sizes[s].color;
            if(it.sizes[s].fabric) szExtra+=(szExtra?' / ':'')+it.sizes[s].fabric;
            chips+='<span class="szchip">'
                  +'<b>'+it.sizes[s].size+'</b> &mdash; '+it.sizes[s].qty
                  +(szExtra?'<br><span class="sz-sub">'+szExtra+'</span>':'')
                  +'</span>';
        }
        if(!chips)chips='&mdash;';
        tbody+='<tr style="background:'+bg+';">'
            +'<td style="text-align:center;font-weight:700;">'+srNo+'</td>'
            +'<td><div style="font-weight:700;font-size:11px;">'+esc(it.product)+'</div>'
            +(spec?'<div style="font-size:9px;color:#555;">'+esc(spec)+'</div>':'')
            +(it.note?'<div style="font-size:8px;color:#888;font-style:italic;">'+esc(it.note)+'</div>':'')
            +'</td>'
            +'<td style="padding:4px 5px;line-height:1.8;word-break:break-word;">'+chips+'</td>'
            +'<td style="text-align:center;font-weight:900;font-size:14px;white-space:nowrap;width:95px;">'+it.totalQty+'</td></tr>';
    }
    return {tbody:tbody,grandQty:grandQty,srNo:srNo};
}

function esc(s){
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function populatePrint(){
    var mcno=document.getElementById('F_mcno').value||'--';
    var pno=document.getElementById('F_pno').value||'--';
    var odate=document.getElementById('F_date').value||'--';
    var ddate=document.getElementById('F_ddate').value||'--';
    var remarks=document.getElementById('F_remarks').value||'';
    var now=new Date();
    var pd=zeroPad(now.getDate())+'/'+zeroPad(now.getMonth()+1)+'/'+now.getFullYear();
    var pt=zeroPad(now.getHours())+':'+zeroPad(now.getMinutes());

    document.getElementById('p_mcno').textContent=mcno;
    document.getElementById('p_date').textContent='Printed: '+pd+' '+pt;

    var gridRows=[['Master Copy No.',mcno],['Order Date',odate],['Party No.',pno],['Delivery Date',ddate],['Remarks',remarks||'--']];
    var gh='';
    for(var i=0;i<gridRows.length;i++) gh+='<div class="pigc"><div class="pigl">'+gridRows[i][0]+'</div><div class="pigv">'+gridRows[i][1]+'</div></div>';
    document.getElementById('p_grid').innerHTML=gh;
    document.getElementById('p_remark').innerHTML=remarks?'<div class="premark">&#128221; <strong>Note:</strong> '+remarks+'</div>':'';
    var items=collectData();
    var res=buildItemTableHTML(items);
    document.getElementById('p_lbl').textContent='\u25ba Order Details - '+res.srNo+' Item(s)  |  Total Qty: '+res.grandQty;
    document.getElementById('p_table').innerHTML=
        '<table class="ptt"><colgroup><col style="width:32px"><col style="width:120px"><col><col style="width:95px"></colgroup><thead><tr>'
        +'<th style="width:32px;text-align:center;">Sr.</th>'
        +'<th style="text-align:left;width:120px;">Product / Description</th>'
        +'<th style="text-align:left;">Size Breakup (Size &mdash; Qty | Color / Fabric)</th>'
        +'<th style="width:95px;white-space:nowrap;">Total Qty</th>'
        +'</tr></thead><tbody>'+res.tbody+'</tbody>'
        +'<tfoot><tr><td colspan="3" style="text-align:right;letter-spacing:1px;text-transform:uppercase;">Grand Total Quantity</td>'
        +'<td style="text-align:center;font-size:15px;font-weight:900;width:95px;white-space:nowrap;">'+res.grandQty+'</td></tr></tfoot></table>';
    document.getElementById('p_foot').textContent='';
}

function doPrint(){
    var items=collectData(),ok=false;
    for(var i=0;i<items.length;i++)if(items[i].product){ok=true;break;}
    if(!ok){showCustomAlert('Please add at least one product before printing.');return;}

    var printData={
        mc_no:   document.getElementById('F_mcno').value,
        pno:     document.getElementById('F_pno').value,
        odate:   document.getElementById('F_date').value,
        ddate:   document.getElementById('F_ddate').value,
        remarks: document.getElementById('F_remarks').value,
        items:   items
    };
    sessionStorage.setItem('pendingPrint', JSON.stringify(printData));
    var form=document.createElement('form');
    form.method='POST';form.action='';
    var names=['action','mc_no','party_no','order_date','delivery_date','items_json','remarks'];
    var values=['save',
        printData.mc_no, printData.pno, printData.odate, printData.ddate,
        JSON.stringify(items), printData.remarks];
    for(var k=0;k<names.length;k++){
        var inp=document.createElement('input');
        inp.type='hidden';inp.name=names[k];inp.value=values[k];
        form.appendChild(inp);
    }
    var pInp=document.createElement('input');
    pInp.type='hidden';pInp.name='do_print';pInp.value='1';
    form.appendChild(pInp);
    document.body.appendChild(form);form.submit();
}

function buildMCBlock(row){
    var items=[];
    try{items=JSON.parse(row.items_json)||[];}catch(e){}
    var res=buildItemTableHTML(items);
    var gridRows=[
        ['Master Copy No.',esc(row.mc_no)],
        ['Order Date',esc(row.order_date||'—')],
        ['Party No.',esc(row.party_no||'—')],
        ['Delivery Date',esc(row.delivery_date||'—')],
        ['Remarks',esc(row.remarks||'—')]
    ];
    var gh='';
    for(var i=0;i<gridRows.length;i++) gh+='<div class="pigc"><div class="pigl">'+gridRows[i][0]+'</div><div class="pigv">'+gridRows[i][1]+'</div></div>';
    var remarkHTML=row.remarks?'<div class="premark">&#128221; <strong>Note:</strong> '+esc(row.remarks)+'</div>':'';
    var tableHTML='<table class="ptt"><colgroup><col style="width:32px"><col style="width:120px"><col><col style="width:95px"></colgroup><thead><tr>'
        +'<th style="width:32px;text-align:center;">Sr.</th>'
        +'<th style="text-align:left;width:120px;">Product / Description</th>'
        +'<th style="text-align:left;">Size Breakup</th>'
        +'<th style="width:95px;white-space:nowrap;">Total Qty</th>'
        +'</tr></thead><tbody>'+res.tbody+'</tbody>'
        +'<tfoot><tr><td colspan="3" style="text-align:right;text-transform:uppercase;letter-spacing:1px;">Grand Total Quantity</td>'
        +'<td style="text-align:center;font-size:15px;font-weight:900;width:95px;white-space:nowrap;">'+res.grandQty+'</td></tr></tfoot></table>';
    var sigHTML='<div class="psig">'
        +'<div class="psigb">'
        +'<div class="psigline"></div>'
        +'<div class="psiglbl">Authorised Signature</div>'
        +'</div></div>';
    return '<div class="mc-block">'
        +'<div class="print-main-title">MASTER COPY</div>'
        +'<div class="ph">'
        +'<div><h1>Sarthi Sports Wear</h1><div class="phc">Ph: 9422107750 | Mob: 7620425141 | Nagpur, Maharashtra</div></div>'
        +'<div class="phr"><div class="phbadge">Master Copy</div><div class="phno">'+esc(row.mc_no)+'</div></div>'
        +'</div>'
        +'<div class="pig" style="display:grid;grid-template-columns:1fr 1fr;border:1px solid #ccc;margin-bottom:10px;">'+gh+'</div>'
        +remarkHTML
        +'<div class="ptlbl">\u25ba Order Details &mdash; '+res.srNo+' Item(s) | Total Qty: '+res.grandQty+'</div>'
        +tableHTML
        +sigHTML
        +'</div>';
}

function printAllHistory(){
    if(typeof HIST_DATA==='undefined'||!HIST_DATA||HIST_DATA.length===0){
        showCustomAlert('No history records to print.');return;
    }
    var now=new Date();
    var pd=zeroPad(now.getDate())+'/'+zeroPad(now.getMonth()+1)+'/'+now.getFullYear();
    var pt=zeroPad(now.getHours())+':'+zeroPad(now.getMinutes());
    document.getElementById('ha_printdate').textContent='Printed: '+pd+' '+pt;
    document.getElementById('ha_stats').innerHTML=
        '<b>Total Records: '+TOTAL_MC+'</b> &nbsp;|&nbsp; Today: '+TODAY_MC+' &nbsp;|&nbsp; This Week: '+WEEK_MC;

    var rows='';
    for(var i=0;i<HIST_DATA.length;i++){
        var r=HIST_DATA[i];
        var items=[];
        try{items=JSON.parse(r.items_json)||[];}catch(e){}
        var prodCell='';
        var grandQty=0;
        for(var k=0;k<items.length;k++){
            var it=items[k];
            if(!it.product)continue;
            grandQty+=it.totalQty||0;
            var szTxt='';
            if(it.sizes&&it.sizes.length){
                for(var s=0;s<it.sizes.length;s++){
                    szTxt+='<span class="hsz-chip"><b>'+esc(it.sizes[s].size)+'</b>&#8212;'+it.sizes[s].qty+'</span>';
                }
            }
            prodCell+='<div style="margin-bottom:3px;">'
                +'<span class="hitem-chip"><b>'+esc(it.product)+'</b>'
                +(it.color?' &bull; '+esc(it.color):'')
                +(it.fabric?' / '+esc(it.fabric):'')
                +(it.pattern?' / '+esc(it.pattern):'')
                +' <span style="color:#1a1a1a;font-size:8px;">&#215;'+it.totalQty+'</span>'
                +'</span>'
                +(szTxt?'<br>'+szTxt:'')
                +'</div>';
        }
        if(!prodCell)prodCell='<span style="color:#aaa;">—</span>';
        var bg=(i%2===0)?'#ffffff':'#f4f4f4';
        rows+='<tr style="background:'+bg+' !important;">'
            +'<td style="text-align:center;font-weight:700;font-size:9px;color:#000;">'+(i+1)+'</td>'
            +'<td><span class="hmc-pill">'+esc(r.mc_no)+'</span></td>'
            +'<td style="font-size:9px;white-space:nowrap;">'+esc(r.order_date||'—')+'</td>'
            +'<td style="font-size:9px;white-space:nowrap;font-weight:700;">'+esc(r.delivery_date||'—')+'</td>'
            +'<td style="font-size:9px;">'+esc(r.party_no||'—')+'</td>'
            +'<td>'+prodCell+'</td>'
            +'<td style="text-align:center;font-size:12px;font-weight:900;color:#000;">'+grandQty+'</td>'
            +'<td style="font-size:8px;color:#555;max-width:90px;word-break:break-word;">'+esc(r.remarks||'—')+'</td>'
            +'</tr>';
    }

    var tableHTML='<table class="htbl-print">'
        +'<thead><tr>'
        +'<th style="width:22px;">#</th>'
        +'<th>MC No.</th>'
        +'<th>Order Date</th>'
        +'<th>Delivery Date</th>'
        +'<th>Party No.</th>'
        +'<th>Products &amp; Sizes</th>'
        +'<th style="width:46px;">Qty</th>'
        +'<th>Remarks</th>'
        +'</tr></thead>'
        +'<tbody>'+rows+'</tbody>'
        +'</table>';

    document.getElementById('ha_table').innerHTML=tableHTML;
    document.getElementById('ha_foot').textContent='';

    document.body.className='print-hist-all';
    var _ot=document.title;document.title=' ';
    var _ou=window.location.href;history.replaceState(null,'','');
    setTimeout(function(){window.print();document.body.className='';document.title=_ot;history.replaceState(null,'',_ou);},200);
}

function printSingleRow(id){
    if(typeof HIST_DATA==='undefined'||!HIST_DATA){
        showCustomAlert('Data not available.');return;
    }
    var row=null;
    for(var i=0;i<HIST_DATA.length;i++){
        if(parseInt(HIST_DATA[i].id)===parseInt(id)){row=HIST_DATA[i];break;}
    }
    if(!row){ showCustomAlert('Record not found.'); return; }

    var now=new Date();
    var pd=zeroPad(now.getDate())+'/'+zeroPad(now.getMonth()+1)+'/'+now.getFullYear();
    var pt=zeroPad(now.getHours())+':'+zeroPad(now.getMinutes());

    var items=[];
    try{items=JSON.parse(row.items_json)||[];}catch(e){}
    var res=buildItemTableHTML(items);

    document.getElementById('sr_mcno').textContent=row.mc_no;
    document.getElementById('sr_date').textContent='Printed: '+pd+' '+pt;

    var gridRows=[
        ['Master Copy No.',esc(row.mc_no)],
        ['Order Date',esc(row.order_date||'—')],
        ['Party No.',esc(row.party_no||'—')],
        ['Delivery Date',esc(row.delivery_date||'—')],
        ['Remarks',esc(row.remarks||'—')]
    ];
    var gh='';
    for(var i=0;i<gridRows.length;i++) gh+='<div class="pigc"><div class="pigl">'+gridRows[i][0]+'</div><div class="pigv">'+gridRows[i][1]+'</div></div>';
    document.getElementById('sr_grid').innerHTML=gh;
    document.getElementById('sr_remark').innerHTML=row.remarks?'<div class="premark">&#128221; <strong>Note:</strong> '+esc(row.remarks)+'</div>':'';
    document.getElementById('sr_lbl').textContent='\u25ba Order Details - '+res.srNo+' Item(s)  |  Total Qty: '+res.grandQty;
    document.getElementById('sr_table').innerHTML=
        '<table class="ptt"><colgroup><col style="width:32px"><col style="width:120px"><col><col style="width:95px"></colgroup><thead><tr>'
        +'<th style="width:32px;text-align:center;">Sr.</th>'
        +'<th style="text-align:left;width:120px;">Product / Description</th>'
        +'<th style="text-align:left;">Size Breakup (Size &mdash; Qty | Color / Fabric)</th>'
        +'<th style="width:95px;white-space:nowrap;">Total Qty</th>'
        +'</tr></thead><tbody>'+res.tbody+'</tbody>'
        +'<tfoot><tr><td colspan="3" style="text-align:right;letter-spacing:1px;text-transform:uppercase;">Grand Total Quantity</td>'
        +'<td style="text-align:center;font-size:15px;font-weight:900;width:95px;white-space:nowrap;">'+res.grandQty+'</td></tr></tfoot></table>';
    document.getElementById('sr_foot').textContent='';

    document.body.className='print-single';
    var _ot=document.title;document.title=' ';
    var _ou=window.location.href;history.replaceState(null,'','');
    setTimeout(function(){window.print();document.body.className='';document.title=_ot;history.replaceState(null,'',_ou);},200);
}

function doSave(){
    var items=collectData(),ok=false;
    for(var i=0;i<items.length;i++)if(items[i].product){ok=true;break;}
    if(!ok){showCustomAlert('Please add at least one product.');return;}
    var form=document.createElement('form');
    form.method='POST';form.action='';
    var names=['action','mc_no','party_no','order_date','delivery_date','items_json','remarks'];
    var values=['save',
        document.getElementById('F_mcno').value,
        document.getElementById('F_pno').value,
        document.getElementById('F_date').value,
        document.getElementById('F_ddate').value,
        JSON.stringify(items),
        document.getElementById('F_remarks').value];
    for(var k=0;k<names.length;k++){
        var inp=document.createElement('input');
        inp.type='hidden';inp.name=names[k];inp.value=values[k];
        form.appendChild(inp);
    }
    document.body.appendChild(form);form.submit();
}

function doClear(){
    showCustomConfirm('Clear all data and reset form?', function(){
        document.getElementById('F_pno').value='';
        document.getElementById('F_ddate').value='';
        document.getElementById('F_remarks').value='';
        document.getElementById('ITB').innerHTML='';
        RC=0;SC=0;
        addRow();addRow();addRow();
    });
}

var bulkModeOn = false;
function toggleBulkMode(){
    bulkModeOn = !bulkModeOn;
    var btn = document.getElementById('bulkToggleBtn');
    if(bulkModeOn){
        document.body.classList.add('bulk-mode');
        btn.classList.add('active');
    } else {
        document.body.classList.remove('bulk-mode');
        btn.classList.remove('active');
        var chks = document.querySelectorAll('.row-chk');
        for(var i=0;i<chks.length;i++){
            chks[i].checked = false;
            var row = chks[i].closest('tr');
            if(row) row.classList.remove('bulk-checked');
        }
        updateSelCount();
    }
}
function selectAllRows(){
    var chks = document.querySelectorAll('.row-chk');
    for(var i=0;i<chks.length;i++){
        chks[i].checked = true;
        var row = chks[i].closest('tr');
        if(row) row.classList.add('bulk-checked');
    }
    updateSelCount();
}
function deselectAllRows(){
    var chks = document.querySelectorAll('.row-chk');
    for(var i=0;i<chks.length;i++){
        chks[i].checked = false;
        var row = chks[i].closest('tr');
        if(row) row.classList.remove('bulk-checked');
    }
    updateSelCount();
}
function toggleSelectAll(chk){
    var chks = document.querySelectorAll('.row-chk');
    for(var i=0;i<chks.length;i++){
        chks[i].checked = chk.checked;
        var row = chks[i].closest('tr');
        if(row){ if(chk.checked) row.classList.add('bulk-checked');
                 else row.classList.remove('bulk-checked'); }
    }
    updateSelCount();
}
function onRowCheck(chk){
    var row = chk.closest('tr');
    if(row){ if(chk.checked) row.classList.add('bulk-checked');
             else row.classList.remove('bulk-checked'); }
    updateSelCount();
}
function updateSelCount(){
    var n = document.querySelectorAll('.row-chk:checked').length;
    var sc = document.getElementById('sel-count');
    if(sc) sc.textContent = n;
    var ct = document.getElementById('bulk-count-txt');
    if(ct) ct.textContent = n + ' selected';
}
function deleteSelected(){
    var chks = document.querySelectorAll('.row-chk:checked');
    if(chks.length === 0){ showCustomAlert('Please tick at least one row to delete.'); return; }
    var ids = [];
    for(var i=0;i<chks.length;i++) ids.push(chks[i].getAttribute('data-id'));
    showCustomConfirm('Delete ' + ids.length + ' selected record(s)? This cannot be undone.', function(){
        document.getElementById('bulk_ids_input').value = ids.join(',');
        document.getElementById('bulkDelForm').submit();
    });
}

function toggleDetail(id){
    var dr=document.getElementById('detail_'+id);
    if(!dr)return;
    dr.className=(dr.className.indexOf('open')>=0)?'detail-row':'detail-row open';
}

function switchTab(tab){
    var tabs=['year','month','day'];
    for(var i=0;i<tabs.length;i++){
        var el=document.getElementById('tab-'+tabs[i]);
        if(el) el.style.display=(tabs[i]===tab)?'block':'none';
    }
    var btns=document.querySelectorAll('.dftab');
    for(var j=0;j<btns.length;j++){
        btns[j].classList.remove('active');
        if(btns[j].getAttribute('onclick')==="switchTab('"+tab+"')") btns[j].classList.add('active');
    }
}
function syncMC(){
    var numEl  = document.getElementById('F_mcno_num');
    var hidden = document.getElementById('F_mcno');
    var badge  = document.getElementById('BADGE_mcno');
    var num    = numEl ? numEl.value.replace(/\D/g,'') : '';
    var full   = 'MC-' + (num ? num.padStart(4,'0') : '0001');
    if(hidden) hidden.value = full;
    if(badge)  badge.textContent = full;
}
</script>

<!-- ═══ CUSTOM ALERT & CONFIRM ═══ -->
<style>
.ssw-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,0.72);z-index:99999;justify-content:center;align-items:center;}
.ssw-overlay.active{display:flex;}
.ssw-box{background:#0f1e2e;border:1px solid #00ccff;border-radius:12px;
    padding:34px 30px;max-width:400px;width:90%;
    box-shadow:0 8px 40px rgba(0,204,255,0.15);text-align:center;
    font-family:'Barlow',sans-serif;}
.ssw-box h5{margin:0 0 8px;font-size:20px;font-weight:700;color:#00ccff;
    letter-spacing:3px;text-transform:uppercase;}
.ssw-box p{margin:0 0 24px;color:#c8e0f4;font-size:20px;line-height:1.6;}
.ssw-btns{display:flex;gap:10px;justify-content:center;}
.ssw-btn-ok{background:#0077ee;color:#fff;border:none;padding:10px 32px;
    border-radius:20px;cursor:pointer;font-size:20px;font-weight:700;
    font-family:'Barlow',sans-serif;transition:background .2s;}
.ssw-btn-ok:hover{background:#0099ff;}
.ssw-btn-ok.red{background:#dd3333;}
.ssw-btn-ok.red:hover{background:#ff4444;}
.ssw-btn-cancel{background:transparent;color:#5a88aa;border:1px solid #1e3a55;
    padding:10px 32px;border-radius:20px;cursor:pointer;font-size:20px;font-weight:700;
    font-family:'Barlow',sans-serif;transition:all .2s;}
.ssw-btn-cancel:hover{border-color:#00ccff;color:#c8e0f4;}
</style>

<div class="ssw-overlay" id="sswAlertOverlay">
  <div class="ssw-box">
    <h5>Sarthi Sports Wear</h5>
    <p id="sswAlertMsg"></p>
    <div class="ssw-btns">
      <button class="ssw-btn-ok" onclick="document.getElementById('sswAlertOverlay').classList.remove('active')">OK</button>
    </div>
  </div>
</div>

<div class="ssw-overlay" id="sswConfirmOverlay">
  <div class="ssw-box">
    <h5>Sarthi Sports Wear</h5>
    <p id="sswConfirmMsg"></p>
    <div class="ssw-btns">
      <button class="ssw-btn-ok red" id="sswConfirmOK">OK</button>
      <button class="ssw-btn-cancel" onclick="document.getElementById('sswConfirmOverlay').classList.remove('active')">Cancel</button>
    </div>
  </div>
</div>

<script>
function showCustomAlert(msg){
    document.getElementById('sswAlertMsg').textContent=msg;
    document.getElementById('sswAlertOverlay').classList.add('active');
}
function showCustomConfirm(msg,onConfirm){
    document.getElementById('sswConfirmMsg').textContent=msg;
    document.getElementById('sswConfirmOverlay').classList.add('active');
    document.getElementById('sswConfirmOK').onclick=function(){
        document.getElementById('sswConfirmOverlay').classList.remove('active');
        if(onConfirm)onConfirm();
    };
}
</script>
</body>
</html>