<?php
// ================= DATABASE CONNECTION =================
$conn = mysqli_connect("localhost", "root", "", "sarkhi sports1");
if (!$conn) die("Database connection failed: " . mysqli_connect_error());

// ================= HANDLE DELETE POST =================
$delete_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $raw_ids = explode(',', $_POST['delete_ids']);
    $safe_ids = array_filter(array_map('intval', $raw_ids), function($id){ return $id > 0; });
    if (!empty($safe_ids)) {
        $placeholders = implode(',', $safe_ids);
        $del_result = mysqli_query($conn, "DELETE FROM orders3 WHERE id IN ($placeholders)");
        if ($del_result) {
            $deleted_count = mysqli_affected_rows($conn);
            $delete_msg = "success:{$deleted_count}";
        } else {
            $delete_msg = "error:" . mysqli_error($conn);
        }
    }
    // Redirect back to same URL to avoid form resubmission
    $redirect_tab = isset($_POST['redirect_tab']) ? $_POST['redirect_tab'] : 'bydate';
    $redirect_from = isset($_POST['redirect_from']) ? '&from_date='.urlencode($_POST['redirect_from']) : '';
    $redirect_to   = isset($_POST['redirect_to'])   ? '&to_date='.urlencode($_POST['redirect_to'])     : '';
    $redirect_month= isset($_POST['redirect_month'])? '&sel_month='.intval($_POST['redirect_month'])   : '';
    $redirect_year = isset($_POST['redirect_year']) ? '&sel_year='.intval($_POST['redirect_year'])     : '';
    $msg_enc = urlencode($delete_msg);
    header("Location: {$_SERVER['PHP_SELF']}?tab={$redirect_tab}{$redirect_from}{$redirect_to}{$redirect_month}{$redirect_year}&dmsg={$msg_enc}");
    exit;
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'bydate';
$dmsg_flash = isset($_GET['dmsg']) ? urldecode($_GET['dmsg']) : '';
$today      = date('Y-m-d');
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : $today;
$to_date   = isset($_GET['to_date'])   ? $_GET['to_date']   : $today;
$sel_year  = isset($_GET['sel_year'])  ? intval($_GET['sel_year'])  : intval(date('Y'));
$sel_month = isset($_GET['sel_month']) ? intval($_GET['sel_month']) : intval(date('m'));
$m_from    = sprintf('%04d-%02d-01', $sel_year, $sel_month);
$m_to      = date('Y-m-t', strtotime($m_from));
$min_year  = intval(date('Y')) - 5;
$max_year  = intval(date('Y')) + 1;

$month_names = [
    1=>'January',2=>'February',3=>'March',4=>'April',
    5=>'May',6=>'June',7=>'July',8=>'August',
    9=>'September',10=>'October',11=>'November',12=>'December'
];

function col($row, $key, $default = '') { return isset($row[$key]) ? $row[$key] : $default; }
function row_gross($o) {
    if (!empty($o['gross_amount']) && floatval($o['gross_amount']) > 0) return floatval($o['gross_amount']);
    $p = floatval(col($o,'price',0)); $q = floatval(col($o,'quantity',0)); $t = floatval(col($o,'total',0));
    return ($p > 0 && $q > 0) ? ($p * $q) : $t;
}
function row_net($o) {
    if (!empty($o['net_amount']) && floatval($o['net_amount']) > 0) return floatval($o['net_amount']);
    return floatval(col($o,'total',0));
}

$fd = mysqli_real_escape_string($conn, $from_date);
$td = mysqli_real_escape_string($conn, $to_date);
$res_date = mysqli_query($conn, "SELECT * FROM orders3 WHERE order_date >= '$fd' AND order_date <= '$td' ORDER BY order_date ASC, id ASC");
$orders_date = [];
while ($r = mysqli_fetch_assoc($res_date)) $orders_date[] = $r;

$d_qty=0; $d_gross=0; $d_disc=0; $d_net=0; $d_prod_grp=[]; $d_cust_grp=[]; $d_day_grp=[];
foreach ($orders_date as $o) {
    $g = row_gross($o); $n = row_net($o);
    $d_qty += floatval(col($o,'quantity',0)); $d_gross += $g; $d_disc += ($g - $n); $d_net += $n;
    $p = col($o,'product','—'); if (!isset($d_prod_grp[$p])) $d_prod_grp[$p] = ['c'=>0,'q'=>0,'n'=>0];
    $d_prod_grp[$p]['c']++; $d_prod_grp[$p]['q'] += floatval(col($o,'quantity',0)); $d_prod_grp[$p]['n'] += $n;
    $c = col($o,'customer_name','—'); if (!isset($d_cust_grp[$c])) $d_cust_grp[$c] = ['c'=>0,'q'=>0,'n'=>0];
    $d_cust_grp[$c]['c']++; $d_cust_grp[$c]['q'] += floatval(col($o,'quantity',0)); $d_cust_grp[$c]['n'] += $n;
    $dd = col($o,'order_date',$today); if (!isset($d_day_grp[$dd])) $d_day_grp[$dd] = ['c'=>0,'q'=>0,'n'=>0];
    $d_day_grp[$dd]['c']++; $d_day_grp[$dd]['q'] += floatval(col($o,'quantity',0)); $d_day_grp[$dd]['n'] += $n;
}

$mf = mysqli_real_escape_string($conn, $m_from); $mt = mysqli_real_escape_string($conn, $m_to);
$res_month = mysqli_query($conn, "SELECT * FROM orders3 WHERE order_date >= '$mf' AND order_date <= '$mt' ORDER BY order_date ASC, id ASC");
$orders_month = [];
while ($r = mysqli_fetch_assoc($res_month)) $orders_month[] = $r;

$m_qty=0; $m_gross=0; $m_disc=0; $m_net=0; $m_prod_grp=[]; $m_cust_grp=[]; $m_day_grp=[];
foreach ($orders_month as $o) {
    $g = row_gross($o); $n = row_net($o);
    $m_qty += floatval(col($o,'quantity',0)); $m_gross += $g; $m_disc += ($g - $n); $m_net += $n;
    $p = col($o,'product','—'); if (!isset($m_prod_grp[$p])) $m_prod_grp[$p] = ['c'=>0,'q'=>0,'n'=>0];
    $m_prod_grp[$p]['c']++; $m_prod_grp[$p]['q'] += floatval(col($o,'quantity',0)); $m_prod_grp[$p]['n'] += $n;
    $c = col($o,'customer_name','—'); if (!isset($m_cust_grp[$c])) $m_cust_grp[$c] = ['c'=>0,'q'=>0,'n'=>0];
    $m_cust_grp[$c]['c']++; $m_cust_grp[$c]['q'] += floatval(col($o,'quantity',0)); $m_cust_grp[$c]['n'] += $n;
    $dd = col($o,'order_date',$today); if (!isset($m_day_grp[$dd])) $m_day_grp[$dd] = ['c'=>0,'q'=>0,'n'=>0];
    $m_day_grp[$dd]['c']++; $m_day_grp[$dd]['q'] += floatval(col($o,'quantity',0)); $m_day_grp[$dd]['n'] += $n;
}

$yr_res = mysqli_query($conn, "SELECT MONTH(order_date) as mon, COUNT(*) as cnt, SUM(COALESCE(NULLIF(net_amount,0), total)) as net FROM orders3 WHERE YEAR(order_date)='$sel_year' GROUP BY MONTH(order_date)");
$yr_data = []; $max_bar = 0;
while ($r = mysqli_fetch_assoc($yr_res)) { $yr_data[intval($r['mon'])] = $r; if ($r['net'] > $max_bar) $max_bar = $r['net']; }

function format_sizes($o) {
    $sj = col($o, 'size_rate_json', '');
    if ($sj) { $arr = json_decode($sj, true); if (is_array($arr) && count($arr) > 0) { $parts = []; foreach ($arr as $s) { if (!empty($s['size'])) $parts[] = $s['size'].($s['qty']>0?'×'.$s['qty']:''); } if ($parts) return implode(', ', $parts); } }
    return col($o,'size','') ?: '—';
}

function render_row($o, $sr) {
    $g = row_gross($o); $n = row_net($o); $disc = $g - $n;
    $qty = floatval(col($o,'quantity',0)); $price = floatval(col($o,'price',0));
    $ddate = col($o,'delivery_date',''); $adate = col($o,'advance_date','');
    $pc = floatval(col($o,'print_charges',0));
    $odate_val = col($o,'order_date','') !== '' ? col($o,'order_date','') : date('Y-m-d');
    $id = col($o,'id',0);
    echo "<tr data-id='{$id}'>";
    echo "<td class='chk-td'><input type='checkbox' class='row-chk' onchange='rowChk(this,{$id})'></td>";
    echo "<td class='l c-dim'>{$sr}</td>";
    echo "<td class='c-inv'>".htmlspecialchars(col($o,'invoice_no',''))."</td>";
    echo "<td class='l'>".htmlspecialchars(col($o,'customer_name',''))."</td>";
    echo "<td>".date('d/m/Y',strtotime($odate_val))."</td>";
    echo "<td>".($ddate?date('d/m/Y',strtotime($ddate)):'—')."</td>";
    echo "<td>".($adate?date('d/m/y',strtotime($adate)):'—')."</td>";
    echo "<td>".ucfirst(htmlspecialchars(col($o,'product','')))."</td>";
    echo "<td>".htmlspecialchars(format_sizes($o))."</td>";
    echo "<td>".htmlspecialchars(col($o,'pattern',''))."</td>";
    echo "<td>".htmlspecialchars(col($o,'fabric',''))."</td>";
    echo "<td>".htmlspecialchars(col($o,'color',''))."</td>";
    echo "<td class='c-amt'>".number_format($qty)."</td>";
    echo "<td class='c-amt'>₹".number_format($price,2)."</td>";
    echo "<td class='c-amt'>₹".number_format($g,2)."</td>";
    echo "<td class='c-disc'>-₹".number_format($disc,2)."</td>";
    echo "<td class='c-net'>₹".number_format($n,2)."</td>";
    echo "<td class='c-dim'>".($pc>0?'₹'.number_format($pc,2):'—')."</td>";
    echo "<td class='c-dim'>".htmlspecialchars(col($o,'payment_mode','—'))."</td>";
    echo "<td class='c-dim'>".htmlspecialchars(col($o,'bill_status',''))."</td>";
    echo "<td class='c-dim c-small'>".htmlspecialchars(col($o,'remarks',''))."</td>";
    echo "</tr>";
}

function build_print_rows($orders, $today) {
    $rows = [];
    foreach ($orders as $o) {
        $g = row_gross($o); $n = row_net($o); $od = col($o,'order_date',$today);
        $dd = col($o,'delivery_date',''); $ad = col($o,'advance_date','');
        $rows[] = ['order_date'=>$od,'date_label'=>date('l, d F Y',strtotime($od)),'order_date_fmt'=>date('d/m/Y',strtotime($od)),
            'invoice_no'=>col($o,'invoice_no',''),'customer_name'=>col($o,'customer_name',''),
            'delivery_date'=>$dd?date('d/m/Y',strtotime($dd)):'','advance_date'=>$ad?date('d/m/y',strtotime($ad)):'',
            'product'=>ucfirst(col($o,'product','')),'sizes'=>format_sizes($o),'pattern'=>col($o,'pattern',''),
            'fabric'=>col($o,'fabric',''),'color'=>col($o,'color',''),'qty'=>floatval(col($o,'quantity',0)),
            'price'=>floatval(col($o,'price',0)),'gross'=>$g,'disc'=>$g-$n,'net'=>$n,
            'print_charges'=>floatval(col($o,'print_charges',0)),'payment_mode'=>col($o,'payment_mode',''),
            'bill_status'=>col($o,'bill_status',''),'remarks'=>col($o,'remarks',''),
            'id'=>intval(col($o,'id',0))];
    }
    return $rows;
}
function build_print_prod($grp) { $out=[]; foreach($grp as $k=>$v) $out[]=(['name'=>$k,'c'=>$v['c'],'q'=>$v['q'],'n'=>$v['n']]); return $out; }
function build_print_day($grp)  { $out=[]; foreach($grp as $k=>$v) $out[]=(['date'=>date('d/m/Y',strtotime($k)),'c'=>$v['c'],'q'=>$v['q'],'n'=>$v['n']]); return $out; }
function build_print_cust($grp) { $out=[]; foreach($grp as $k=>$v) $out[]=(['name'=>$k,'c'=>$v['c'],'q'=>$v['q'],'n'=>$v['n']]); return $out; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarthi Sports — Sales Register</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0f1720;--panel:#1e2a3a;--panel2:#243040;--header-bg:#162030;
  --border:#2e4060;--border-l:#3a5070;--accent:#00aaff;--accent2:#00d4aa;
  --text:#d0e4f7;--text-dim:#7a9ab8;--text-label:#5a7a9a;--input-bg:#111c28;
  --row-even:#1a2535;--row-odd:#1e2d40;--row-hover:#0a2a4a;
  --btn-green:#00c47a;--btn-blue:#0088ee;--btn-gray:#445566;
  --red:#ff6060;--gold:#f0c040;--highlight:#004a88;--btn-red:#e04040;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Barlow',sans-serif;background:var(--bg);min-height:100vh;padding:14px;color:var(--text);font-size:19px;}
.wrap{max-width:1500px;margin:0 auto;}

/* ── TOPBAR ── */
.topbar{background:var(--header-bg);border:1px solid var(--border);border-bottom:2px solid var(--accent);border-radius:6px 6px 0 0;padding:9px 18px;display:flex;align-items:center;justify-content:space-between;font-family:'Share Tech Mono',monospace;font-size:18px;color:var(--text-dim);}
.topbar .brand{color:var(--accent);font-size:19px;font-weight:700;letter-spacing:1px;}
.topbar-right{display:flex;align-items:center;gap:10px;}
.pills{display:flex;gap:10px;}
.pill{background:var(--panel2);border:1px solid var(--border);border-radius:3px;padding:2px 10px;}
.pill span{color:var(--accent2);}

/* ── THREE DOT BUTTON ── */
.dots-btn{background:var(--panel2);border:1px solid var(--border);border-radius:4px;color:var(--text-dim);font-size:22px;font-weight:900;padding:2px 11px 5px;cursor:pointer;letter-spacing:-1px;transition:all 0.2s;font-family:monospace;line-height:1;}
.dots-btn:hover{border-color:var(--accent);color:var(--accent);}
.dots-btn.active{border-color:var(--btn-red);color:var(--btn-red);background:rgba(224,64,64,0.1);}

/* ── SELECTION BAR ── */
.sel-bar{display:none;background:rgba(224,64,64,0.06);border:1px solid rgba(224,64,64,0.35);border-top:none;padding:9px 16px;align-items:center;gap:12px;flex-wrap:wrap;}
.sel-bar.show{display:flex;}
.sel-bar-count{font-family:'Share Tech Mono',monospace;font-size:18px;color:var(--text-dim);}
.sel-bar-count strong{color:var(--btn-red);font-size:18px;}
.sel-bar-btn{border-radius:4px;font-size:18px;font-family:'Share Tech Mono',monospace;padding:7px 16px;cursor:pointer;transition:background 0.15s,filter 0.15s;letter-spacing:0.3px;}
.sel-bar-btn-all{background:transparent;border:1px solid var(--accent);color:var(--accent);}
.sel-bar-btn-all:hover{background:rgba(0,170,255,0.1);}
.sel-bar-btn-none{background:transparent;border:1px solid var(--border-l);color:var(--text-dim);}
.sel-bar-btn-none:hover{background:rgba(255,255,255,0.05);}
.sel-bar-btn-del{background:var(--btn-red);border:none;color:#fff;font-weight:700;font-family:'Barlow',sans-serif;}
.sel-bar-btn-del:hover{filter:brightness(1.18);}
.sel-bar-btn-del:disabled{background:#553030;color:#886666;cursor:not-allowed;filter:none;}
.sel-bar-btn-close{margin-left:auto;background:transparent;border:1px solid var(--border-l);border-radius:4px;color:var(--text-dim);font-size:18px;font-family:'Share Tech Mono',monospace;padding:7px 14px;cursor:pointer;transition:all 0.2s;}
.sel-bar-btn-close:hover{background:rgba(224,64,64,0.12);border-color:var(--btn-red);color:var(--btn-red);}

/* Checkbox columns — hidden until sel-mode */
.chk-th{width:36px;text-align:center!important;display:none;}
.chk-td{text-align:center;display:none;}
body.sel-mode .chk-th,body.sel-mode .chk-td{display:table-cell;}
tbody tr.row-sel{background:rgba(224,64,64,0.1)!important;}
input.row-chk{width:14px;height:14px;accent-color:var(--btn-red);cursor:pointer;}

/* ── TABS ── */
.tab-bar{background:var(--header-bg);border:1px solid var(--border);border-top:none;display:flex;}
.tab-btn{padding:13px 34px;font-size:19px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;font-family:'Barlow',sans-serif;background:transparent;border:none;border-right:1px solid var(--border);border-bottom:3px solid transparent;color:var(--text-dim);cursor:pointer;transition:all 0.2s;}
.tab-btn:hover{background:var(--panel2);color:var(--text);}
.tab-btn.active{background:var(--panel);color:var(--accent);border-bottom-color:var(--accent);}
.panel{display:none;}.panel.active{display:block;}

/* ── FILTER BAR ── */
.filter-bar{background:var(--panel);border:1px solid var(--border);border-top:none;padding:10px 16px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;}
.fg{display:flex;flex-direction:column;gap:3px;}
.fg label{font-size:19px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-label);font-weight:700;}
.fg input,.fg select{background:var(--input-bg);border:1px solid var(--border);border-radius:4px;color:var(--text);font-size:19px;font-family:'Share Tech Mono',monospace;padding:8px 12px;min-width:150px;}
.fg input:focus,.fg select:focus{outline:none;border-color:var(--accent);}
.btn{padding:9px 20px;border:none;border-radius:4px;font-size:19px;font-weight:700;cursor:pointer;font-family:'Barlow',sans-serif;letter-spacing:0.4px;transition:filter 0.15s;display:inline-flex;align-items:center;gap:5px;text-decoration:none;}
.btn:hover{filter:brightness(1.18);}
.btn-green{background:var(--btn-green);color:#000;}
.btn-blue{background:var(--btn-blue);color:#fff;}
.btn-gray{background:var(--btn-gray);color:#ccc;}

/* ── SEARCH BAR ── */
.search-bar{background:var(--panel);border:1px solid var(--border);border-top:none;padding:8px 14px;display:flex;align-items:center;gap:10px;}
.search-wrap{position:relative;flex:1;max-width:520px;}
.search-icon-left{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-label);font-size:18px;pointer-events:none;}
.search-input{width:100%;background:var(--input-bg);border:1px solid var(--border);border-radius:5px;color:var(--text);font-size:19px;font-family:'Share Tech Mono',monospace;padding:9px 36px 9px 34px;outline:none;transition:border-color 0.2s,box-shadow 0.2s;}
.search-input:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(0,170,255,0.15);}
.search-input::placeholder{color:var(--text-label);}
.search-clear-btn{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:rgba(46,64,96,0.8);border:none;border-radius:50%;color:var(--text-dim);font-size:19px;font-weight:900;width:18px;height:18px;line-height:18px;text-align:center;padding:0;cursor:pointer;display:none;transition:background 0.15s,color 0.15s,transform 0.1s;z-index:2;}
.search-clear-btn:hover{background:var(--btn-red);color:#fff;transform:translateY(-50%) scale(1.15);}
.search-clear-btn.visible{display:block;}
.search-count{font-family:'Share Tech Mono',monospace;font-size:19px;color:var(--text-dim);white-space:nowrap;}
.search-count .sc-match{color:var(--accent2);font-weight:700;font-size:18px;}
.search-count .sc-none{color:var(--red);}

/* ── KPI STRIP ── */
.kpi-strip{background:var(--panel);border:1px solid var(--border);border-top:none;padding:10px 14px;display:flex;gap:8px;flex-wrap:wrap;}
.kpi{background:var(--input-bg);border:1px solid var(--border);border-radius:5px;padding:9px 18px;display:flex;flex-direction:column;align-items:center;gap:2px;min-width:115px;}
.kpi-val{font-family:'Share Tech Mono',monospace;font-size:22px;font-weight:bold;color:var(--accent2);}
.kpi-label{font-size:19px;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-label);font-weight:600;}
.kpi.blue .kpi-val{color:var(--accent);}
.kpi.red .kpi-val{color:var(--red);}
.kpi.gold .kpi-val{color:var(--gold);font-size:19px;}

/* ── MONTH OVERVIEW ── */
.month-overview{background:var(--panel);border:1px solid var(--border);border-top:none;padding:12px 16px;}
.section-title{font-size:19px;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:10px;font-family:'Share Tech Mono',monospace;}
.month-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:6px;}
.mc{background:var(--input-bg);border:1px solid var(--border);border-radius:5px;padding:8px 5px;text-align:center;cursor:pointer;transition:all 0.15s;}
.mc:hover{border-color:var(--accent);background:var(--row-hover);}
.mc.active{border-color:var(--accent);background:var(--highlight);}
.mc-name{font-size:19px;font-weight:700;color:var(--text-dim);margin-bottom:4px;}
.mc.active .mc-name{color:var(--accent);}
.mc-amt{font-family:'Share Tech Mono',monospace;font-size:19px;color:var(--accent2);}
.mc-cnt{font-size:19px;color:var(--text-label);margin-top:2px;}
.mc-bar{height:3px;background:var(--border);border-radius:2px;margin-top:5px;overflow:hidden;}
.mc-fill{height:100%;background:var(--accent2);border-radius:2px;}
.mc-empty{font-size:19px;color:var(--text-label);margin-top:4px;}

/* ── TABLE ── */
.reg-wrap{background:var(--panel);border:1px solid var(--border);border-top:none;overflow-x:auto;}
.reg-header{background:var(--header-bg);padding:10px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;font-size:18px;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:0.5px;}
.reg-header small{color:var(--text-dim);font-weight:400;font-size:19px;text-transform:none;}
table.reg{width:100%;border-collapse:collapse;font-size:18px;min-width:1400px;}
table.reg thead tr{background:var(--header-bg);}
table.reg thead th{padding:9px 8px;text-align:center;font-size:19px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-dim);border-right:1px solid var(--border);border-bottom:2px solid var(--border-l);white-space:nowrap;}
table.reg thead th.l{text-align:left;padding-left:10px;}
table.reg tbody tr:nth-child(even){background:var(--row-even);}
table.reg tbody tr:nth-child(odd){background:var(--row-odd);}
table.reg tbody tr:hover{background:var(--row-hover);}
table.reg tbody td{padding:7px 8px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);font-family:'Share Tech Mono',monospace;font-size:19px;text-align:center;white-space:nowrap;}
table.reg tbody td.l{text-align:left;font-family:'Barlow',sans-serif;}
table.reg tbody td.c-small{font-size:19px;white-space:normal;max-width:130px;}
tr.sep td{background:#0d1e2e!important;color:var(--accent);font-size:19px;font-weight:700;padding:7px 14px;border-bottom:1px solid var(--border-l);text-align:left;font-family:'Share Tech Mono',monospace;}
table.reg tfoot tr{background:var(--highlight)!important;border-top:2px solid var(--accent);}
table.reg tfoot td{padding:9px 8px;font-weight:700;font-family:'Share Tech Mono',monospace;font-size:18px;color:#fff;border-right:1px solid var(--border-l);text-align:center;}
table.reg tfoot td.l{text-align:left;padding-left:12px;color:var(--accent);}
.c-inv{color:var(--accent2);}.c-amt{color:#e0f0ff;}.c-disc{color:var(--red);}
.c-net{color:var(--accent2);font-weight:600;}.c-dim{color:var(--text-dim);}
.no-results-row td{text-align:center!important;padding:28px!important;color:var(--text-dim)!important;font-family:'Barlow',sans-serif!important;font-size:19px!important;}
mark.sh{background:rgba(0,170,255,0.25);color:var(--accent);border-radius:2px;padding:0 1px;}

/* ── SUMMARY STRIP ── */
.summary-strip{display:grid;grid-template-columns:1fr 1fr 1fr;border:1px solid var(--border);border-top:none;}
.sum-block{background:var(--panel2);border-right:1px solid var(--border);}
.sum-block:last-child{border-right:none;}
.sum-head{background:var(--header-bg);padding:8px 14px;font-size:19px;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid var(--border);}
table.mini{width:100%;border-collapse:collapse;}
table.mini thead th{background:var(--panel);padding:7px 10px;text-align:left;font-size:19px;text-transform:uppercase;color:var(--text-label);border-bottom:1px solid var(--border);font-weight:600;}
table.mini thead th:not(:first-child){text-align:right;}
table.mini tbody td{padding:7px 10px;border-bottom:1px solid var(--border);font-family:'Share Tech Mono',monospace;font-size:19px;color:var(--text);}
table.mini tbody td:not(:first-child){text-align:right;color:var(--accent2);}
table.mini tbody tr:hover{background:var(--row-hover);}
table.mini tfoot td{padding:7px 10px;font-weight:700;font-family:'Share Tech Mono',monospace;font-size:19px;background:var(--highlight);border-top:1px solid var(--accent);color:#fff;}
table.mini tfoot td:not(:first-child){text-align:right;color:var(--accent2);}

.no-data{text-align:center;padding:40px;color:var(--text-dim);font-size:18px;}
.no-data .icon{font-size:40px;margin-bottom:10px;}
.btn-bar{background:var(--header-bg);border:1px solid var(--border);border-top:none;border-radius:0 0 6px 6px;padding:10px 16px;display:flex;gap:10px;align-items:center;}

/* ── CONFIRM DIALOG ── */
.msgbox-overlay{display:none;position:fixed;inset:0;background:rgba(5,10,18,0.82);backdrop-filter:blur(4px);z-index:99999;align-items:center;justify-content:center;}
.msgbox-overlay.show{display:flex;animation:mbFI 0.18s ease;}
@keyframes mbFI{from{opacity:0}to{opacity:1}}
@keyframes mbSU{from{opacity:0;transform:translateY(24px) scale(0.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.msgbox-card{background:var(--panel);border-radius:10px;min-width:320px;max-width:440px;overflow:hidden;box-shadow:0 0 0 1px var(--border),0 24px 64px rgba(0,0,0,0.7);animation:mbSU 0.22s cubic-bezier(0.34,1.56,0.64,1);}
.msgbox-card.type-confirm{border-top:3px solid var(--btn-red);}
.msgbox-header{padding:13px 20px;background:var(--header-bg);border-bottom:1px solid var(--border);font-family:'Share Tech Mono',monospace;font-size:19px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;color:var(--text-label);display:flex;align-items:center;gap:8px;}
.msgbox-dot{width:8px;height:8px;border-radius:50%;background:var(--btn-red);}
.msgbox-body{padding:22px 22px 16px;display:flex;gap:16px;align-items:flex-start;}
.msgbox-icon{font-size:32px;flex-shrink:0;line-height:1;}
.msgbox-title{font-size:19px;font-weight:700;color:var(--text);margin-bottom:7px;}
.msgbox-msg{font-size:19px;color:var(--text-dim);line-height:1.6;font-family:'Share Tech Mono',monospace;white-space:pre-line;}
.msgbox-footer{padding:0 22px 18px;display:flex;justify-content:flex-end;gap:10px;}
.msgbox-btn{padding:10px 26px;border:none;border-radius:5px;font-size:19px;font-weight:700;cursor:pointer;font-family:'Barlow',sans-serif;transition:filter 0.15s;}
.msgbox-btn:hover{filter:brightness(1.18);}
.msgbox-btn-confirm{background:var(--btn-red);color:#fff;}
.msgbox-btn-cancel{background:var(--btn-gray);color:#ccc;}

/* ── PRINT OVERLAY ── */
#print-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;flex-direction:column;gap:14px;}
#print-overlay.show{display:flex;}
#print-overlay .po-msg{color:#00d4aa;font-family:'Share Tech Mono',monospace;font-size:18px;letter-spacing:1px;}
#print-overlay .po-spin{width:40px;height:40px;border:4px solid #2e4060;border-top-color:#00aaff;border-radius:50%;animation:spin 0.8s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}

/* ── TOAST NOTIFICATION ── */
#toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--panel);border:1px solid var(--border);border-radius:6px;padding:14px 26px;font-family:'Share Tech Mono',monospace;font-size:19px;color:var(--text);box-shadow:0 8px 32px rgba(0,0,0,0.5);z-index:99999;opacity:0;transition:opacity 0.3s,transform 0.3s;pointer-events:none;white-space:nowrap;}
#toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
#toast.success{border-color:var(--btn-green);color:#00e088;}
#toast.error{border-color:var(--red);color:var(--red);}
@media print{body>*{display:none!important;}}
</style>
</head>
<body>
<div class="wrap">

<!-- TOPBAR -->
<div class="topbar">
  <span class="brand">🧾 SARTHI SPORTS WEAR — SALES REGISTER</span>
  <div class="topbar-right">
    <div class="pills">
      <span class="pill">User: <span>ADMIN</span></span>
      <span class="pill">Date: <span id="ld"></span></span>
      <span class="pill">Time: <span id="lt"></span></span>
    </div>
    <button class="dots-btn" id="dotsBtn" onclick="toggleSelMode()" title="Clear selection">⋮</button>
  </div>
</div>

<!-- TABS -->
<div class="tab-bar">
  <button class="tab-btn <?= $active_tab=='bydate'?'active':'' ?>" onclick="switchTab('bydate',this)">📅 By Date</button>
  <button class="tab-btn <?= $active_tab=='bymonth'?'active':'' ?>" onclick="switchTab('bymonth',this)">📆 By Month</button>
</div>

<!-- SELECTION BAR (shared, always visible below tabs) -->
<div class="sel-bar" id="selBar">
  <input type="checkbox" class="row-chk" id="chkAll" onchange="toggleChkAll(this)" style="width:14px;height:14px;accent-color:var(--btn-red);cursor:pointer;">
  <button class="sel-bar-btn sel-bar-btn-all"  onclick="selAll()">☑ Select All</button>
  <button class="sel-bar-btn sel-bar-btn-none" onclick="selNone()">☐ Deselect All</button>
  <span class="sel-bar-count">Selected: <strong id="selCount">0</strong></span>
  <button class="sel-bar-btn sel-bar-btn-del" id="selDelBtn" disabled onclick="selDelete()">🗑 Delete Selected</button>
  <button class="sel-bar-btn-close" onclick="toggleSelMode()">✕ Clear</button>
</div>

<!-- ══════════════════════════════════════════
     PANEL: BY DATE
══════════════════════════════════════════ -->
<div class="panel <?= $active_tab=='bydate'?'active':'' ?>" id="panel-bydate">
  <form method="GET" action=""><input type="hidden" name="tab" value="bydate">
    <div class="filter-bar">
      <div class="fg"><label>From Date</label><input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>"></div>
      <div class="fg"><label>To Date</label><input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>"></div>
      <button type="submit" class="btn btn-green">▶️ Show</button>
      <button type="button" class="btn btn-blue" onclick="doPrint('bydate')">🖨 Print</button>
    </div>
  </form>

  <!-- SEARCH BAR -->
  <div class="search-bar">
    <div class="search-wrap">
      <span class="search-icon-left">🔍</span>
      <input type="text" class="search-input" id="search-date"
             placeholder="Search customer, invoice, product, fabric, color, pattern…"
             oninput="doSearch('date',this.value)" autocomplete="off">
      <button class="search-clear-btn" id="sclear-date" title="Clear" onclick="clearSearch('date')">✕</button>
    </div>
    <span class="search-count" id="scount-date"></span>
  </div>

  <div class="kpi-strip">
    <div class="kpi blue"><span class="kpi-val"><?= count($orders_date) ?></span><span class="kpi-label">Orders</span></div>
    <div class="kpi"><span class="kpi-val"><?= number_format($d_qty) ?></span><span class="kpi-label">Total Qty</span></div>
    <div class="kpi"><span class="kpi-val">₹<?= number_format($d_gross,2) ?></span><span class="kpi-label">Gross</span></div>
    <div class="kpi red"><span class="kpi-val">₹<?= number_format($d_disc,2) ?></span><span class="kpi-label">Discount</span></div>
    <div class="kpi"><span class="kpi-val">₹<?= number_format($d_net,2) ?></span><span class="kpi-label">Net Amount</span></div>
    <div class="kpi gold" style="margin-left:auto;"><span class="kpi-val"><?= date('d/m/Y',strtotime($from_date)) ?> → <?= date('d/m/Y',strtotime($to_date)) ?></span><span class="kpi-label">Date Range</span></div>
  </div>

  <div class="reg-wrap">
    <div class="reg-header">📋 Sales Register — By Date
      <small><?= count($orders_date) ?> records &nbsp;|&nbsp; <?= date('d/m/Y',strtotime($from_date)) ?> to <?= date('d/m/Y',strtotime($to_date)) ?></small>
    </div>
    <?php if(empty($orders_date)): ?>
    <div class="no-data"><div class="icon">📭</div><div>No records found.</div></div>
    <?php else: ?>
    <table class="reg" id="tbl-date"><thead><tr>
      <th class="chk-th"></th>
      <th class="l">#</th><th>Invoice</th><th class="l">Customer</th><th>Order Date</th><th>Delivery</th><th>Advance</th>
      <th>Product</th><th>Sizes</th><th>Pattern</th><th>Fabric</th><th>Color</th>
      <th>Qty</th><th>Rate</th><th>Gross</th><th>Disc</th><th>Net</th><th>Print</th><th>Payment</th><th>Status</th><th>Remarks</th>
    </tr></thead>
    <tbody id="tbody-date">
    <?php $sr=1;$prev=''; foreach($orders_date as $o): $od=col($o,'order_date',$today); if($od!==$prev):$prev=$od;?>
      <tr class="sep" data-sep="1"><td class="chk-td"></td><td colspan="20">▶️ <?=date('l, d F Y',strtotime($od))?></td></tr>
    <?php endif; render_row($o,$sr++); endforeach;?>
    </tbody>
    <tfoot><tr>
      <td class="chk-td"></td>
      <td class="l" colspan="11">TOTAL — <?=count($orders_date)?> Orders</td>
      <td><?=number_format($d_qty)?></td><td>—</td><td>₹<?=number_format($d_gross,2)?></td>
      <td class="c-disc">-₹<?=number_format($d_disc,2)?></td><td>₹<?=number_format($d_net,2)?></td><td colspan="4">—</td>
    </tr></tfoot></table>
    <?php endif;?>
  </div>

  <div class="summary-strip">
    <div class="sum-block"><div class="sum-head">📦 Product Wise</div><table class="mini"><thead><tr><th>Product</th><th>Orders</th><th>Qty</th><th>Net (₹)</th></tr></thead><tbody>
    <?php if(empty($d_prod_grp)):?><tr><td colspan="4" style="text-align:center;padding:10px;color:var(--text-dim)">No data</td></tr>
    <?php else:foreach($d_prod_grp as $p=>$pg):?><tr><td><?=ucfirst(htmlspecialchars($p))?></td><td><?=$pg['c']?></td><td><?=$pg['q']?></td><td>₹<?=number_format($pg['n'],2)?></td></tr><?php endforeach;endif;?>
    </tbody><tfoot><tr><td>Total</td><td><?=count($orders_date)?></td><td><?=$d_qty?></td><td>₹<?=number_format($d_net,2)?></td></tr></tfoot></table></div>
    <div class="sum-block"><div class="sum-head">📅 Date Wise</div><table class="mini"><thead><tr><th>Date</th><th>Orders</th><th>Qty</th><th>Net (₹)</th></tr></thead><tbody>
    <?php if(empty($d_day_grp)):?><tr><td colspan="4" style="text-align:center;padding:10px;color:var(--text-dim)">No data</td></tr>
    <?php else:foreach($d_day_grp as $dd=>$dg):?><tr><td><?=date('d/m/Y',strtotime($dd))?></td><td><?=$dg['c']?></td><td><?=$dg['q']?></td><td>₹<?=number_format($dg['n'],2)?></td></tr><?php endforeach;endif;?>
    </tbody><tfoot><tr><td>Total</td><td><?=count($orders_date)?></td><td><?=$d_qty?></td><td>₹<?=number_format($d_net,2)?></td></tr></tfoot></table></div>
    <div class="sum-block"><div class="sum-head">👤 Customer Wise</div><table class="mini"><thead><tr><th>Customer</th><th>Orders</th><th>Qty</th><th>Net (₹)</th></tr></thead><tbody>
    <?php if(empty($d_cust_grp)):?><tr><td colspan="4" style="text-align:center;padding:10px;color:var(--text-dim)">No data</td></tr>
    <?php else:foreach($d_cust_grp as $c=>$cg):?><tr><td><?=htmlspecialchars($c)?></td><td><?=$cg['c']?></td><td><?=$cg['q']?></td><td>₹<?=number_format($cg['n'],2)?></td></tr><?php endforeach;endif;?>
    </tbody><tfoot><tr><td>Total</td><td><?=count($orders_date)?></td><td><?=$d_qty?></td><td>₹<?=number_format($d_net,2)?></td></tr></tfoot></table></div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     PANEL: BY MONTH
══════════════════════════════════════════ -->
<div class="panel <?= $active_tab=='bymonth'?'active':'' ?>" id="panel-bymonth">
  <form method="GET" action="" id="fMonth"><input type="hidden" name="tab" value="bymonth">
    <div class="filter-bar">
      <div class="fg"><label>Month</label><select name="sel_month" onchange="this.form.submit()">
        <?php foreach($month_names as $num=>$name):?><option value="<?=$num?>" <?=($num==$sel_month)?'selected':''?>><?=$name?></option><?php endforeach;?>
      </select></div>
      <div class="fg"><label>Year</label><select name="sel_year" onchange="this.form.submit()">
        <?php for($y=$max_year;$y>=$min_year;$y--):?><option value="<?=$y?>" <?=($y==$sel_year)?'selected':''?>><?=$y?></option><?php endfor;?>
      </select></div>
      <button type="button" class="btn btn-blue" onclick="doPrint('bymonth')">🖨 Print</button>
    </div>
  </form>

  <div class="kpi-strip">
    <div class="kpi gold"><span class="kpi-val"><?=$month_names[$sel_month]?> <?=$sel_year?></span><span class="kpi-label">Period</span></div>
    <div class="kpi blue"><span class="kpi-val"><?=count($orders_month)?></span><span class="kpi-label">Orders</span></div>
    <div class="kpi"><span class="kpi-val"><?=number_format($m_qty)?></span><span class="kpi-label">Total Qty</span></div>
    <div class="kpi"><span class="kpi-val">₹<?=number_format($m_gross,2)?></span><span class="kpi-label">Gross</span></div>
    <div class="kpi red"><span class="kpi-val">₹<?=number_format($m_disc,2)?></span><span class="kpi-label">Discount</span></div>
    <div class="kpi"><span class="kpi-val">₹<?=number_format($m_net,2)?></span><span class="kpi-label">Net Amount</span></div>
  </div>

  <div class="month-overview">
    <div class="section-title">📊 <?=$sel_year?> Overview — Click a month to view</div>
    <div class="month-grid">
    <?php for($m=1;$m<=12;$m++): $yd=isset($yr_data[$m])?$yr_data[$m]:null; $has=($yd&&$yd['cnt']>0); $pct=($max_bar>0&&$has)?round(($yd['net']/$max_bar)*100):0;?>
    <div class="mc <?=($m==$sel_month)?'active':''?>" onclick="selMonth(<?=$m?>)">
      <div class="mc-name"><?=substr($month_names[$m],0,3)?></div>
      <?php if($has):?><div class="mc-amt">₹<?=number_format($yd['net'],0)?></div><div class="mc-cnt"><?=$yd['cnt']?> orders</div><div class="mc-bar"><div class="mc-fill" style="width:<?=$pct?>%"></div></div>
      <?php else:?><div class="mc-empty">—</div><?php endif;?>
    </div>
    <?php endfor;?>
    </div>
  </div>

  <!-- SEARCH BAR -->
  <div class="search-bar">
    <div class="search-wrap">
      <span class="search-icon-left">🔍</span>
      <input type="text" class="search-input" id="search-month"
             placeholder="Search customer, invoice, product, fabric, color, pattern…"
             oninput="doSearch('month',this.value)" autocomplete="off">
      <button class="search-clear-btn" id="sclear-month" title="Clear" onclick="clearSearch('month')">✕</button>
    </div>
    <span class="search-count" id="scount-month"></span>
  </div>

  <div class="reg-wrap" id="monthDetail">
    <div class="reg-header">📋 Sales Register — <?=$month_names[$sel_month]?> <?=$sel_year?>
      <small><?=count($orders_month)?> records &nbsp;|&nbsp; 01/<?=str_pad($sel_month,2,'0',STR_PAD_LEFT)?>/<?=$sel_year?> — <?=date('t',strtotime($m_from))?>/<?=str_pad($sel_month,2,'0',STR_PAD_LEFT)?>/<?=$sel_year?></small>
    </div>
    <?php if(empty($orders_month)):?>
    <div class="no-data"><div class="icon">📭</div><div>No sales found for <?=$month_names[$sel_month]?> <?=$sel_year?>.</div></div>
    <?php else:?>
    <table class="reg" id="tbl-month"><thead><tr>
      <th class="chk-th"></th>
      <th class="l">#</th><th>Invoice</th><th class="l">Customer</th><th>Order Date</th><th>Delivery</th><th>Advance</th>
      <th>Product</th><th>Sizes</th><th>Pattern</th><th>Fabric</th><th>Color</th>
      <th>Qty</th><th>Rate</th><th>Gross</th><th>Disc</th><th>Net</th><th>Print</th><th>Payment</th><th>Status</th><th>Remarks</th>
    </tr></thead>
    <tbody id="tbody-month">
    <?php $sr=1;$prev=''; foreach($orders_month as $o): $od=col($o,'order_date',$today); if($od!==$prev):$prev=$od;?>
      <tr class="sep" data-sep="1"><td class="chk-td"></td><td colspan="20">▶️ <?=date('l, d F Y',strtotime($od))?></td></tr>
    <?php endif; render_row($o,$sr++); endforeach;?>
    </tbody>
    <tfoot><tr>
      <td class="chk-td"></td>
      <td class="l" colspan="11">TOTAL — <?=$month_names[$sel_month]?> <?=$sel_year?> — <?=count($orders_month)?> Orders</td>
      <td><?=number_format($m_qty)?></td><td>—</td><td>₹<?=number_format($m_gross,2)?></td>
      <td class="c-disc">-₹<?=number_format($m_disc,2)?></td><td>₹<?=number_format($m_net,2)?></td><td colspan="4">—</td>
    </tr></tfoot></table>
    <?php endif;?>
  </div>

  <div class="summary-strip">
    <div class="sum-block"><div class="sum-head">📦 Product Wise</div><table class="mini"><thead><tr><th>Product</th><th>Orders</th><th>Qty</th><th>Net (₹)</th></tr></thead><tbody>
    <?php if(empty($m_prod_grp)):?><tr><td colspan="4" style="text-align:center;padding:10px;color:var(--text-dim)">No data</td></tr>
    <?php else:foreach($m_prod_grp as $p=>$pg):?><tr><td><?=ucfirst(htmlspecialchars($p))?></td><td><?=$pg['c']?></td><td><?=$pg['q']?></td><td>₹<?=number_format($pg['n'],2)?></td></tr><?php endforeach;endif;?>
    </tbody><tfoot><tr><td>Total</td><td><?=count($orders_month)?></td><td><?=$m_qty?></td><td>₹<?=number_format($m_net,2)?></td></tr></tfoot></table></div>
    <div class="sum-block"><div class="sum-head">📅 Day Wise</div><table class="mini"><thead><tr><th>Date</th><th>Orders</th><th>Qty</th><th>Net (₹)</th></tr></thead><tbody>
    <?php if(empty($m_day_grp)):?><tr><td colspan="4" style="text-align:center;padding:10px;color:var(--text-dim)">No data</td></tr>
    <?php else:foreach($m_day_grp as $dd=>$dg):?><tr><td><?=date('d/m/Y',strtotime($dd))?></td><td><?=$dg['c']?></td><td><?=$dg['q']?></td><td>₹<?=number_format($dg['n'],2)?></td></tr><?php endforeach;endif;?>
    </tbody><tfoot><tr><td>Total</td><td><?=count($orders_month)?></td><td><?=$m_qty?></td><td>₹<?=number_format($m_net,2)?></td></tr></tfoot></table></div>
    <div class="sum-block"><div class="sum-head">👤 Customer Wise</div><table class="mini"><thead><tr><th>Customer</th><th>Orders</th><th>Qty</th><th>Net (₹)</th></tr></thead><tbody>
    <?php if(empty($m_cust_grp)):?><tr><td colspan="4" style="text-align:center;padding:10px;color:var(--text-dim)">No data</td></tr>
    <?php else:foreach($m_cust_grp as $c=>$cg):?><tr><td><?=htmlspecialchars($c)?></td><td><?=$cg['c']?></td><td><?=$cg['q']?></td><td>₹<?=number_format($cg['n'],2)?></td></tr><?php endforeach;endif;?>
    </tbody><tfoot><tr><td>Total</td><td><?=count($orders_month)?></td><td><?=$m_qty?></td><td>₹<?=number_format($m_net,2)?></td></tr></tfoot></table></div>
  </div>
</div>

<div class="btn-bar">
  <a href="order_form.php" class="btn btn-green">+ New Order</a>
  <a href="order_form.php" class="btn btn-gray">← Back</a>
</div>
</div><!-- .wrap -->

<div id="toast"></div>

<!-- Hidden print iframe -->
<iframe id="__printFrame" style="position:fixed;top:0;left:0;width:0;height:0;border:none;visibility:hidden;"></iframe>

<!-- Print loading overlay -->
<div id="print-overlay">
  <div class="po-spin"></div>
  <div class="po-msg">⏳ Preparing print document…</div>
</div>

<!-- Confirm dialog -->
<div class="msgbox-overlay" id="msgboxOverlay">
  <div class="msgbox-card type-confirm" id="msgboxCard">
    <div class="msgbox-header"><div class="msgbox-dot"></div><span id="mb-header">CONFIRM DELETE</span></div>
    <div class="msgbox-body">
      <div class="msgbox-icon">🗑️</div>
      <div><div class="msgbox-title" id="mb-title">Delete Records?</div><div class="msgbox-msg" id="mb-msg"></div></div>
    </div>
    <div class="msgbox-footer">
      <button class="msgbox-btn msgbox-btn-cancel" onclick="hideMsgbox()">Cancel</button>
      <button class="msgbox-btn msgbox-btn-confirm" id="mb-confirm-btn">Yes, Delete</button>
    </div>
  </div>
</div>

<script>
// ── PRINT DATA ──────────────────────────────────────────────
var PRINT_DATA = {
  date:  { rows:<?=json_encode(build_print_rows($orders_date,$today))?>, kpi:<?=json_encode(['orders'=>count($orders_date),'qty'=>$d_qty,'gross'=>$d_gross,'disc'=>$d_disc,'net'=>$d_net,'range'=>date('d/m/Y',strtotime($from_date)).' to '.date('d/m/Y',strtotime($to_date))])?>, prod:<?=json_encode(build_print_prod($d_prod_grp))?>, day:<?=json_encode(build_print_day($d_day_grp))?>, cust:<?=json_encode(build_print_cust($d_cust_grp))?> },
  month: { rows:<?=json_encode(build_print_rows($orders_month,$today))?>, kpi:<?=json_encode(['orders'=>count($orders_month),'qty'=>$m_qty,'gross'=>$m_gross,'disc'=>$m_disc,'net'=>$m_net,'range'=>$month_names[$sel_month].' '.$sel_year])?>, prod:<?=json_encode(build_print_prod($m_prod_grp))?>, day:<?=json_encode(build_print_day($m_day_grp))?>, cust:<?=json_encode(build_print_cust($m_cust_grp))?> }
};

// ── CLOCK ───────────────────────────────────────────────────
function pad(n){ return String(n).padStart(2,'0'); }
function tick(){
  var d=new Date();
  document.getElementById('ld').textContent=pad(d.getDate())+'/'+pad(d.getMonth()+1)+'/'+d.getFullYear();
  document.getElementById('lt').textContent=pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds());
}
setInterval(tick,1000); tick();

// ── TAB / MONTH SELECT ──────────────────────────────────────
function switchTab(tab,btn){
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('panel-'+tab).classList.add('active');
  btn.classList.add('active');
  var url=new URL(window.location.href); url.searchParams.set('tab',tab); window.history.replaceState({},'',url);
}
function selMonth(m){ document.querySelector('#fMonth select[name="sel_month"]').value=m; document.getElementById('fMonth').submit(); }
window.addEventListener('load',function(){
  if('<?=$active_tab?>'==='bymonth'){
    var el=document.getElementById('monthDetail');
    if(el) setTimeout(function(){el.scrollIntoView({behavior:'smooth',block:'start'});},200);
  }
});

// ════════════════════════════════════════════════════════════
//  SEARCH
// ════════════════════════════════════════════════════════════
var _origText = {};

function initOrig(tbodyId){
  if(_origText[tbodyId]) return;
  _origText[tbodyId] = [];
  var rows = document.querySelectorAll('#'+tbodyId+' tr:not([data-sep])');
  rows.forEach(function(tr){
    var cells=[]; tr.querySelectorAll('td').forEach(function(td){ cells.push(td.textContent); });
    _origText[tbodyId].push(cells);
  });
}

function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escRe(s){ return s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); }
function highlight(text,q){ if(!q) return escHtml(text); return escHtml(text).replace(new RegExp('('+escRe(q)+')','gi'),'<mark class="sh">$1</mark>'); }

function doSearch(panel, raw){
  var tbodyId = 'tbody-'+panel;
  var clearBtn = document.getElementById('sclear-'+panel);
  var countEl  = document.getElementById('scount-'+panel);
  var tbody    = document.getElementById(tbodyId);
  if(!tbody) return;

  initOrig(tbodyId);
  var q = raw.trim();

  // show/hide clear button
  clearBtn.classList.toggle('visible', q.length > 0);

  var dataRows = Array.from(tbody.querySelectorAll('tr:not([data-sep])'));
  var sepRows  = Array.from(tbody.querySelectorAll('tr[data-sep]'));
  var cache    = _origText[tbodyId];

  // remove old no-results row
  var old = tbody.querySelector('.no-results-row');
  if(old) old.remove();

  if(!q){
    dataRows.forEach(function(tr,i){
      tr.style.display='';
      tr.querySelectorAll('td').forEach(function(td,ci){ if(cache[i]) td.innerHTML=escHtml(cache[i][ci]||''); });
    });
    sepRows.forEach(function(tr){ tr.style.display=''; });
    countEl.innerHTML='';
    updateSelUI(); return;
  }

  var lq=q.toLowerCase(), matched=0;
  dataRows.forEach(function(tr,i){
    var texts=cache[i]||[];
    var show=(texts.join(' ').toLowerCase().indexOf(lq)!==-1);
    tr.style.display=show?'':'none';
    if(show){
      matched++;
      tr.querySelectorAll('td').forEach(function(td,ci){ td.innerHTML=highlight(texts[ci]||'',q); });
    } else {
      tr.querySelectorAll('td').forEach(function(td,ci){ td.innerHTML=escHtml(texts[ci]||''); });
    }
  });

  // show/hide sep rows
  sepRows.forEach(function(sep){
    var hasVis=false, nx=sep.nextElementSibling;
    while(nx && !nx.getAttribute('data-sep')){ if(nx.style.display!=='none'){hasVis=true;break;} nx=nx.nextElementSibling; }
    sep.style.display=hasVis?'':'none';
  });

  if(matched===0){
    countEl.innerHTML='<span class="sc-none">No results for "'+escHtml(q)+'"</span>';
    var nr=document.createElement('tr'); nr.className='no-results-row';
    nr.innerHTML='<td colspan="22" style="text-align:center;padding:28px;color:var(--text-dim);">🔍 No matching records for "<strong>'+escHtml(q)+'</strong>"</td>';
    tbody.appendChild(nr);
  } else {
    countEl.innerHTML='<span class="sc-match">'+matched+'</span> result'+(matched!==1?'s':'')+' found';
  }
  updateSelUI();
}

function clearSearch(panel){
  var inp=document.getElementById('search-'+panel);
  inp.value=''; doSearch(panel,''); inp.focus();
}

// Keyboard: / or Ctrl+F to focus search; Esc to clear
document.addEventListener('keydown',function(e){
  var ap=document.querySelector('.panel.active');
  if(!ap) return;
  var key=ap.id.replace('panel-by',''); // 'date' or 'month'
  var inp=document.getElementById('search-'+key);
  if(!inp) return;
  if((e.key==='/' && document.activeElement.tagName!=='INPUT' && document.activeElement.tagName!=='SELECT') || (e.ctrlKey && e.key==='f')){
    e.preventDefault(); inp.focus(); inp.select();
  }
  if(e.key==='Escape' && document.activeElement===inp){ clearSearch(key); inp.blur(); }
});

// ════════════════════════════════════════════════════════════
//  THREE-DOT — SELECT MODE & DELETE
// ════════════════════════════════════════════════════════════
var selModeOn  = false;
var selectedIds = new Set(); // stores order DB ids

function toggleSelMode(){
  selModeOn = !selModeOn;
  document.body.classList.toggle('sel-mode', selModeOn);
  document.getElementById('dotsBtn').classList.toggle('active', selModeOn);
  document.getElementById('selBar').classList.toggle('show', selModeOn);
  if(!selModeOn){ selectedIds.clear(); uncheckAll(); }
  updateSelUI();
}

function rowChk(chk, id){
  if(chk.checked) selectedIds.add(id); else selectedIds.delete(id);
  var row=chk.closest('tr');
  if(row) row.classList.toggle('row-sel', chk.checked);
  updateSelUI();
}

function toggleChkAll(masterChk){
  if(masterChk.checked) selAll(); else selNone();
}

function selAll(){
  ['tbody-date','tbody-month'].forEach(function(tid){
    var tbody=document.getElementById(tid); if(!tbody) return;
    tbody.querySelectorAll('tr:not([data-sep])').forEach(function(tr){
      if(tr.style.display==='none') return;
      var chk=tr.querySelector('.row-chk'); var id=parseInt(tr.getAttribute('data-id'));
      if(chk && id){ chk.checked=true; selectedIds.add(id); tr.classList.add('row-sel'); }
    });
  });
  if(selectedIds.size > 0){
    document.getElementById('selBar').classList.add('show');
    document.getElementById('dotsBtn').classList.add('active');
  }
  updateSelUI();
}

function selNone(){
  selectedIds.clear(); uncheckAll();
  document.getElementById('selBar').classList.remove('show');
  document.getElementById('dotsBtn').classList.remove('active');
  updateSelUI();
}

function uncheckAll(){
  document.querySelectorAll('.row-chk').forEach(function(c){ c.checked=false; c.closest('tr') && c.closest('tr').classList.remove('row-sel'); });
  var ca=document.getElementById('chkAll'); if(ca){ca.checked=false;ca.indeterminate=false;}
}

function updateSelUI(){
  var count=selectedIds.size;
  var el=document.getElementById('selCount'); if(el) el.textContent=count;
  var btn=document.getElementById('selDelBtn');
  if(btn){ btn.disabled=(count===0); btn.textContent=count>0?'🗑 Delete Selected ('+count+')':'🗑 Delete Selected'; }
  // master checkbox state
  var allVisible=[];
  ['tbody-date','tbody-month'].forEach(function(tid){
    var tb=document.getElementById(tid); if(!tb) return;
    tb.querySelectorAll('tr:not([data-sep])').forEach(function(tr){
      if(tr.style.display!=='none'){ var id=parseInt(tr.getAttribute('data-id')); if(id) allVisible.push(id); }
    });
  });
  var selVis=allVisible.filter(function(id){ return selectedIds.has(id); }).length;
  var ca=document.getElementById('chkAll');
  if(ca){
    if(allVisible.length>0 && selVis===allVisible.length){ca.checked=true;ca.indeterminate=false;}
    else if(selVis>0){ca.checked=false;ca.indeterminate=true;}
    else{ca.checked=false;ca.indeterminate=false;}
  }
}

function selDelete(){
  var count=selectedIds.size;
  if(!count) return;
  showConfirm(
    'Delete '+count+' Order Record'+(count!==1?'s':'')+'?',
    'You are about to permanently delete '+count+' selected order record'+(count!==1?'s':'')+' from the database.\n\nThis cannot be undone.',
    'Yes, Delete',
    function(){
      var ids = Array.from(selectedIds);
      var isMonth = document.getElementById('panel-bymonth').classList.contains('active');
      var f = document.createElement('form');
      f.method = 'POST';
      f.action = window.location.pathname;
      function addField(name, val){
        var inp=document.createElement('input'); inp.type='hidden'; inp.name=name; inp.value=val; f.appendChild(inp);
      }
      addField('delete_ids', ids.join(','));
      addField('redirect_tab', isMonth ? 'bymonth' : 'bydate');
      if(!isMonth){
        var fdEl = document.querySelector('input[name="from_date"]');
        var tdEl = document.querySelector('input[name="to_date"]');
        if(fdEl) addField('redirect_from', fdEl.value);
        if(tdEl) addField('redirect_to',   tdEl.value);
      } else {
        var smEl = document.querySelector('select[name="sel_month"]');
        var syEl = document.querySelector('select[name="sel_year"]');
        if(smEl) addField('redirect_month', smEl.value);
        if(syEl) addField('redirect_year',  syEl.value);
      }
      document.body.appendChild(f);
      f.submit();
    }
  );
}

// ── CONFIRM / ALERT DIALOGS ─────────────────────────────────
function showConfirm(title, msg, okText, callback){
  document.getElementById('mb-title').textContent=title;
  document.getElementById('mb-msg').textContent=msg;
  var btn=document.getElementById('mb-confirm-btn');
  btn.textContent=okText||'Yes, Delete';
  btn.onclick=function(){ hideMsgbox(); callback(); };
  document.getElementById('msgboxOverlay').classList.add('show');
  setTimeout(function(){btn.focus();},100);
}
function showAlert(msg){
  document.getElementById('mb-title').textContent='Notice';
  document.getElementById('mb-msg').textContent=msg;
  var btn=document.getElementById('mb-confirm-btn');
  btn.textContent='OK';
  btn.onclick=function(){ hideMsgbox(); };
  document.getElementById('msgboxOverlay').classList.add('show');
}
function hideMsgbox(){ document.getElementById('msgboxOverlay').classList.remove('show'); }
document.getElementById('msgboxOverlay').addEventListener('click',function(e){ if(e.target===this) hideMsgbox(); });
document.addEventListener('keydown',function(e){ if(e.key==='Escape') hideMsgbox(); });

// ════════════════════════════════════════════════════════════
//  PRINT
// ════════════════════════════════════════════════════════════
function doPrint(tab){
  if(!tab) tab=(document.getElementById('panel-bydate').classList.contains('active')?'bydate':'bymonth');
  var isMonth=(tab==='bymonth'), data=isMonth?PRINT_DATA.month:PRINT_DATA.date;
  var tabLabel=isMonth?'By Month':'By Date', rows=data.rows, kpi=data.kpi;
  var now=new Date(), pt=pad(now.getDate())+'/'+pad(now.getMonth()+1)+'/'+now.getFullYear()+' '+pad(now.getHours())+':'+pad(now.getMinutes())+':'+pad(now.getSeconds());

  function esc(s){ if(!s&&s!==0)return''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function fmtN(n,d){ d=d===undefined?0:d; return parseFloat(n||0).toLocaleString('en-IN',{minimumFractionDigits:d,maximumFractionDigits:d}); }
  function fmtR(n){ return '&#8377;&nbsp;'+fmtN(n,2); }

  var css=`@page{size:A4 landscape;margin:3mm;}*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}html,body{width:100%;background:#fff;-webkit-print-color-adjust:exact;print-color-adjust:exact;}body{font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#111;}.co-header{display:flex;align-items:center;justify-content:space-between;background:linear-gradient(120deg,#06102a 0%,#0a2050 60%,#0d2d6a 100%);border:3px solid #003399;border-bottom:none;padding:16px 22px 14px;}.co-name{font-size:42px;font-weight:900;color:#fff;letter-spacing:2px;line-height:1.1;}.co-name span{color:#00d4aa;}.co-sub{font-size:15px;color:#99bbdd;margin-top:5px;font-style:italic;}.co-ph{font-size:15px;color:#aaccee;margin-top:8px;font-weight:700;}.doc-right{text-align:right;}.doc-heading{font-size:30px;font-weight:900;text-transform:uppercase;letter-spacing:4px;color:#00aaff;border-bottom:3px solid #00aaff;padding-bottom:6px;margin-bottom:10px;}.doc-meta{font-size:15px;color:#aaccee;line-height:2.3;}.doc-meta b{color:#fff;font-size:16px;}.kpi-bar{display:grid;grid-template-columns:repeat(6,1fr);border:3px solid #003399;border-bottom:none;background:#eef2ff;}.kc{padding:12px 14px;border-right:2px solid #99aacc;text-align:center;}.kc:last-child{border-right:none;}.kc-lbl{font-size:12px;font-weight:800;text-transform:uppercase;color:#334;letter-spacing:0.6px;margin-bottom:5px;}.kc-val{font-size:24px;font-weight:900;color:#003399;font-family:"Courier New",monospace;line-height:1.2;}.kc-val.green{color:#006600;}.kc-val.red{color:#cc0000;}.kc-val.blue{color:#0044cc;}.kc-val.sm{font-size:16px;}.tbl-wrap{width:100%;border:3px solid #003399;}table.reg{width:100%;border-collapse:collapse;table-layout:fixed;}table.reg col:nth-child(1){width:2.5%;}table.reg col:nth-child(2){width:6.5%;}table.reg col:nth-child(3){width:10%;}table.reg col:nth-child(4){width:5.5%;}table.reg col:nth-child(5){width:5%;}table.reg col:nth-child(6){width:4.5%;}table.reg col:nth-child(7){width:5%;}table.reg col:nth-child(8){width:7.5%;}table.reg col:nth-child(9){width:5%;}table.reg col:nth-child(10){width:5%;}table.reg col:nth-child(11){width:5%;}table.reg col:nth-child(12){width:2.8%;}table.reg col:nth-child(13){width:5%;}table.reg col:nth-child(14){width:5.5%;}table.reg col:nth-child(15){width:4.7%;}table.reg col:nth-child(16){width:5.5%;}table.reg col:nth-child(17){width:4%;}table.reg col:nth-child(18){width:4.5%;}table.reg col:nth-child(19){width:4%;}table.reg col:nth-child(20){width:5%;}table.reg thead tr{background:#003399;}table.reg thead th{padding:11px 6px;border-right:1px solid #2255aa;text-align:center;font-size:13px;font-weight:900;text-transform:uppercase;color:#fff;white-space:nowrap;letter-spacing:0.4px;}table.reg thead th:last-child{border-right:none;}table.reg thead th.tl{text-align:left;padding-left:10px;}table.reg tbody tr.sep td{background:#d0e4ff!important;color:#001166;font-size:14px;font-weight:900;padding:9px 16px;text-align:left;border-top:3px solid #4466cc;border-bottom:2px solid #8899cc;font-family:Arial,sans-serif;letter-spacing:0.5px;}table.reg tbody tr.even td{background:#f0f5ff;}table.reg tbody tr.odd td{background:#ffffff;}table.reg tbody td{padding:9px 6px;border-right:1px solid #ccd;border-bottom:1px solid #dde;text-align:center;font-size:13px;font-family:"Courier New",monospace;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}table.reg tbody td:last-child{border-right:none;}table.reg tbody td.tl{text-align:left;font-family:Arial,sans-serif;font-size:13px;}table.reg tbody td.csm{font-size:11.5px;white-space:normal;word-break:break-word;}table.reg tbody td.disc{color:#bb0000;font-weight:800;}table.reg tbody td.net{color:#003399;font-weight:900;font-size:14px;}table.reg tbody td.dim{color:#445;}table.reg tbody td.inv{color:#006633;font-weight:700;}table.reg tfoot tr{background:#003399!important;}table.reg tfoot td{padding:11px 6px;font-weight:900;font-family:"Courier New",monospace;font-size:14px;color:#fff;border-right:1px solid #2255aa;text-align:center;}table.reg tfoot td:last-child{border-right:none;}table.reg tfoot td.tl{text-align:left;padding-left:14px;color:#aaddff;font-family:Arial,sans-serif;font-size:14px;}.bot-strip{display:grid;grid-template-columns:1fr 1fr 1fr;border:3px solid #003399;border-top:none;}.bot-col{border-right:2px solid #99aacc;}.bot-col:last-child{border-right:none;}.bot-col-hd{background:#003399;color:#fff;padding:10px 16px;font-size:14px;font-weight:900;text-transform:uppercase;border-bottom:1px solid #2255aa;letter-spacing:0.5px;}table.mini{width:100%;border-collapse:collapse;}table.mini thead th{background:#e4eaff;padding:8px 10px;text-align:left;font-size:12px;font-weight:800;text-transform:uppercase;border-bottom:1px solid #bbccee;color:#223;}table.mini thead th:not(:first-child){text-align:right;}table.mini tbody td{padding:7px 10px;border-bottom:1px solid #eef;font-family:"Courier New",monospace;font-size:13px;color:#111;}table.mini tbody td:first-child{font-family:Arial,sans-serif;font-size:13px;}table.mini tbody td:not(:first-child){text-align:right;}table.mini tbody tr:nth-child(even) td{background:#f5f7ff;}table.mini tfoot td{padding:9px 10px;font-weight:900;background:#003399;color:#fff;border-top:2px solid #2255aa;font-family:"Courier New",monospace;font-size:13px;}table.mini tfoot td:first-child{font-family:Arial,sans-serif;color:#bbddff;}table.mini tfoot td:not(:first-child){text-align:right;}.doc-footer{border:3px solid #003399;border-top:2px solid #99aacc;padding:10px 20px;display:flex;justify-content:space-between;align-items:center;font-size:14px;color:#223;background:#eef2ff;}.doc-footer b{color:#003399;font-size:15px;}`;

  var headerHtml='<div class="co-header"><div><div class="co-name">Sarthi <span>Sports</span> Wear</div><div class="co-sub">Sports Equipment &amp; Apparel Manufacturer</div><div class="co-ph">&#128222; 0712-3247750 &nbsp;|&nbsp; &#128241; 9422107750</div></div><div class="doc-right"><div class="doc-heading">&#128196; Sales Register</div><div class="doc-meta"><b>Period:</b> &nbsp; '+esc(kpi.range)+'<br><b>Type:</b> &nbsp;&nbsp;&nbsp; '+tabLabel+'<br><b>Printed:</b> &nbsp;'+pt+' | ADMIN</div></div></div>';
  var kpiHtml='<div class="kpi-bar"><div class="kc"><div class="kc-lbl">Period</div><div class="kc-val sm">'+esc(kpi.range)+'</div></div><div class="kc"><div class="kc-lbl">Total Orders</div><div class="kc-val blue">'+kpi.orders+'</div></div><div class="kc"><div class="kc-lbl">Total Qty</div><div class="kc-val blue">'+fmtN(kpi.qty)+'</div></div><div class="kc"><div class="kc-lbl">Gross Amount</div><div class="kc-val">'+fmtR(kpi.gross)+'</div></div><div class="kc"><div class="kc-lbl">Discount</div><div class="kc-val red">'+fmtR(kpi.disc)+'</div></div><div class="kc"><div class="kc-lbl">Net Amount</div><div class="kc-val green">'+fmtR(kpi.net)+'</div></div></div>';

  var tableHtml='<div class="tbl-wrap"><table class="reg"><colgroup><col><col><col><col><col><col><col><col><col><col><col><col><col><col><col><col><col><col><col><col></colgroup><thead><tr><th class="tl">#</th><th>Invoice</th><th class="tl">Customer</th><th>Ord. Date</th><th>Delivery</th><th>Advance</th><th>Product</th><th>Sizes</th><th>Pattern</th><th>Fabric</th><th>Color</th><th>Qty</th><th>Rate</th><th>Gross</th><th>Disc</th><th>Net Amt</th><th>Print&#8377;</th><th>Payment</th><th>Status</th><th>Remarks</th></tr></thead><tbody>';
  var prevDate='',sr=1,tQty=0,tGross=0,tDisc=0,tNet=0;
  rows.forEach(function(r){
    if(r.order_date!==prevDate){prevDate=r.order_date;tableHtml+='<tr class="sep"><td colspan="20">&#9658;&nbsp;'+esc(r.date_label)+'</td></tr>';}
    var cls=(sr%2===0)?'even':'odd'; tQty+=r.qty;tGross+=r.gross;tDisc+=r.disc;tNet+=r.net;
    tableHtml+='<tr class="'+cls+'"><td class="tl dim">'+sr+'</td><td class="inv">'+esc(r.invoice_no)+'</td><td class="tl">'+esc(r.customer_name)+'</td><td>'+esc(r.order_date_fmt)+'</td><td>'+(r.delivery_date?esc(r.delivery_date):'&mdash;')+'</td><td>'+(r.advance_date?esc(r.advance_date):'&mdash;')+'</td><td>'+esc(r.product)+'</td><td>'+esc(r.sizes)+'</td><td>'+esc(r.pattern)+'</td><td>'+esc(r.fabric)+'</td><td>'+esc(r.color)+'</td><td>'+fmtN(r.qty)+'</td><td>'+fmtR(r.price)+'</td><td>'+fmtR(r.gross)+'</td><td class="disc">&minus;'+fmtR(r.disc)+'</td><td class="net">'+fmtR(r.net)+'</td><td class="dim">'+(r.print_charges>0?fmtR(r.print_charges):'&mdash;')+'</td><td class="dim">'+esc(r.payment_mode)+'</td><td class="dim">'+esc(r.bill_status)+'</td><td class="dim csm">'+esc(r.remarks)+'</td></tr>';
    sr++;
  });
  tableHtml+='</tbody><tfoot><tr><td class="tl" colspan="11">&#10004; GRAND TOTAL &mdash; '+rows.length+' Orders</td><td>'+fmtN(tQty)+'</td><td>&mdash;</td><td>'+fmtR(tGross)+'</td><td style="color:#ffccaa;">&minus;'+fmtR(tDisc)+'</td><td style="color:#aaffcc;font-size:12px;">'+fmtR(tNet)+'</td><td colspan="4">&mdash;</td></tr></tfoot></table></div>';

  function miniTbl(title,hdrs,rowsArr,totals){var t='<div class="bot-col"><div class="bot-col-hd">'+title+'</div><table class="mini"><thead><tr>';hdrs.forEach(function(h){t+='<th>'+h+'</th>';});t+='</tr></thead><tbody>';rowsArr.forEach(function(row){t+='<tr>';row.forEach(function(c){t+='<td>'+c+'</td>';});t+='</tr>';});t+='</tbody><tfoot><tr>';totals.forEach(function(c){t+='<td>'+c+'</td>';});return t+'</tr></tfoot></table></div>';}
  var tot=['Total',kpi.orders,fmtN(kpi.qty),fmtR(kpi.net)];
  var sumHtml='<div class="bot-strip">'+miniTbl('&#128230; Product Wise',['Product','Orders','Qty','Net&#8377;'],data.prod.map(function(p){return[esc(p.name),p.c,fmtN(p.q),fmtR(p.n)];}),tot)+miniTbl('&#128197; Date Wise',['Date','Orders','Qty','Net&#8377;'],data.day.map(function(d){return[esc(d.date),d.c,fmtN(d.q),fmtR(d.n)];}),tot)+miniTbl('&#128100; Customer Wise',['Customer','Orders','Qty','Net&#8377;'],data.cust.map(function(c){return[esc(c.name),c.c,fmtN(c.q),fmtR(c.n)];}),tot)+'</div>';
  var footerHtml='<div class="doc-footer"><span><b>SARTHI SPORTS WEAR</b> &nbsp;&mdash;&nbsp; Sales Register &nbsp;&mdash;&nbsp; '+tabLabel+'</span><span>Printed: <b>'+pt+'</b> &nbsp;|&nbsp; User: <b>ADMIN</b></span></div>';

  var html='<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sarthi Sales Register</title><style>'+css+'</style></head><body style="margin:0;padding:0;width:100%;"><div style="padding:0;width:100%;">'+headerHtml+kpiHtml+tableHtml+sumHtml+footerHtml+'</div></body></html>';

  var overlay=document.getElementById('print-overlay'); overlay.classList.add('show');
  var iframe=document.getElementById('__printFrame');
  iframe.onload=function(){ overlay.classList.remove('show'); setTimeout(function(){ try{iframe.contentWindow.focus();iframe.contentWindow.print();}catch(e){} },300); };
  try{ iframe.contentWindow.document.open(); iframe.contentWindow.document.write(html); iframe.contentWindow.document.close(); }
  catch(e){
    overlay.classList.remove('show');
    var blob=new Blob([html],{type:'text/html'}); var url=URL.createObjectURL(blob);
    iframe.onload=function(){ overlay.classList.remove('show'); setTimeout(function(){ try{iframe.contentWindow.focus();iframe.contentWindow.print();}catch(ex){} setTimeout(function(){URL.revokeObjectURL(url);},60000); },300); };
    iframe.src=url;
  }
}

// ── FLASH MESSAGE (after delete redirect) ──
(function(){
  var msg = <?=json_encode($dmsg_flash)?>;
  if(!msg) return;
  var parts = msg.split(':');
  var type = parts[0]; // 'success' or 'error'
  var detail = parts.slice(1).join(':');
  var text = type === 'success'
    ? '✅ ' + detail + ' record' + (parseInt(detail)!==1?'s':'') + ' deleted successfully'
    : '❌ Delete failed: ' + detail;
  showToast(text, type);
})();


function showToast(msg, type){
  var t=document.getElementById('toast');
  t.textContent=msg; t.className='show '+(type||'')+' ';
  clearTimeout(t._tid);
  t._tid=setTimeout(function(){ t.className=''; },3800);
}
</script>
</body>
</html>