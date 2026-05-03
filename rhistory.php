<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarthi Sports Wear — Receipt History</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --bg: #0f1720; --panel: #1e2a3a; --panel2: #243040;
        --border: #2e4060; --border-light: #3a5070;
        --accent: #00aaff; --accent2: #00d4aa;
        --text: #ffffff; --text-dim: #ffffff; --text-label: #ffffff;
        --input-bg: #111c28; --header-bg: #162030;
        --btn-green: #00c47a; --btn-gray: #445566; --btn-red: #e04040;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Barlow', sans-serif; background: var(--bg); min-height: 100vh; color: #ffffff; padding: 14px 28px; display: flex; flex-direction: column; align-items: center; font-size: 18px; }
    .container { max-width: 1600px; width: 100%; }
    .topbar { background: var(--header-bg); border: 1px solid var(--border); border-bottom: 2px solid var(--accent); border-radius: 6px 6px 0 0; padding: 13px 32px; display: flex; align-items: center; justify-content: space-between; }
    .topbar-left { display: flex; align-items: center; gap: 12px; }
    .topbar-icon { background: var(--panel2); border: 1px solid var(--border); border-radius: 4px; padding: 6px 14px; font-size: 20px; font-family: 'Share Tech Mono', monospace; color: #ffffff; }
    .topbar-title { font-size: 24px; font-weight: 700; letter-spacing: 1.5px; color: #ffffff; font-family: 'Share Tech Mono', monospace; text-transform: uppercase; }
    .btn-back { background: var(--panel2); border: 1px solid var(--border); border-radius: 4px; color: var(--accent); font-size: 20px; font-family: 'Share Tech Mono', monospace; padding: 7px 20px; cursor: pointer; text-decoration: none; letter-spacing: 0.5px; transition: background 0.2s; }
    .btn-back:hover { background: rgba(0,170,255,0.15); border-color: var(--accent); }
    .page-header { background: var(--panel); border: 1px solid var(--border); border-top: none; padding: 18px 32px; display: flex; align-items: center; gap: 12px; font-family: 'Share Tech Mono', monospace; }
    .page-header .lightning { color: #f0a000; font-size: 26px; }
    .page-header h2 { font-size: 24px; letter-spacing: 2px; color: #ffffff; font-weight: 700; text-transform: uppercase; }
    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; background: var(--panel); border: 1px solid var(--border); border-top: none; }
    .stat-card { padding: 28px 32px; text-align: center; border-right: 1px solid var(--border); transition: background 0.2s; }
    .stat-card:last-child { border-right: none; }
    .stat-card .num { font-size: 58px; font-weight: 700; font-family: 'Share Tech Mono', monospace; line-height: 1; }
    .stat-card .lbl { font-size: 18px; text-transform: uppercase; letter-spacing: 2px; color: #ffffff; margin-top: 8px; font-family: 'Share Tech Mono', monospace; }
    .stat-card:nth-child(1) .num { color: var(--accent); }
    .stat-card:nth-child(2) .num { color: var(--accent2); }
    .stat-card:nth-child(3) .num { color: #7a9aff; }
    .tabs-bar { background: var(--panel); border: 1px solid var(--border); border-top: none; padding: 0 32px; display: flex; align-items: center; gap: 0; }
    .tab { padding: 16px 24px; font-size: 20px; font-family: 'Share Tech Mono', monospace; letter-spacing: 1px; text-transform: uppercase; color: #ffffff; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 7px; user-select: none; background: none; border-top: none; border-left: none; border-right: none; }
    .tab:hover { color: var(--accent); }
    .tab.active { color: var(--accent); border-bottom-color: var(--accent); }
    .tab-icon { font-size: 20px; }
    .filter-panel { background: var(--panel); border: 1px solid var(--border); border-top: none; padding: 16px 32px; display: none; gap: 18px; align-items: flex-end; flex-wrap: wrap; }
    .filter-panel.active { display: flex; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-size: 17px; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; font-family: 'Share Tech Mono', monospace; font-weight: 700; }
    .filter-group input, .filter-group select { background: var(--input-bg); border: 1px solid var(--border); border-radius: 4px; color: #ffffff; font-size: 20px; font-family: 'Share Tech Mono', monospace; padding: 11px 16px; outline: none; min-width: 160px; }
    .filter-group input:focus, .filter-group select:focus { border-color: var(--accent); }
    .filter-btn { background: var(--accent); border: none; border-radius: 4px; color: #000; font-size: 19px; font-weight: 700; padding: 12px 26px; cursor: pointer; font-family: 'Share Tech Mono', monospace; letter-spacing: 0.5px; transition: filter 0.15s; }
    .filter-btn:hover { filter: brightness(1.15); }
    .filter-clear { background: transparent; border: 1px solid var(--border); border-radius: 4px; color: #ffffff; font-size: 18px; padding: 12px 20px; cursor: pointer; font-family: 'Share Tech Mono', monospace; transition: border-color 0.2s; }
    .filter-clear:hover { border-color: var(--accent); color: var(--accent); }
    .controls-bar { background: var(--panel); border: 1px solid var(--border); border-top: none; padding: 14px 32px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .type-btns { display: flex; gap: 0; flex-wrap: wrap; }
    .type-btn { background: var(--panel2); border: 1px solid var(--border); color: #ffffff; font-size: 18px; font-family: 'Share Tech Mono', monospace; padding: 11px 26px; cursor: pointer; letter-spacing: 0.5px; transition: all 0.15s; }
    .type-btn:first-child { border-radius: 4px 0 0 4px; }
    .type-btn:last-child { border-radius: 0 4px 4px 0; }
    .type-btn:not(:first-child) { border-left: none; }
    .type-btn.active { background: rgba(0,170,255,0.15); border-color: var(--accent); color: var(--accent); }
    .type-btn:hover:not(.active) { color: #ffffff; background: var(--border); }
    .search-wrap { flex: 1; position: relative; min-width: 200px; }
    .search-wrap input { width: 100%; background: var(--input-bg); border: 1px solid var(--border); border-radius: 4px; color: #ffffff; font-size: 20px; font-family: 'Share Tech Mono', monospace; padding: 11px 48px 11px 16px; outline: none; transition: padding-right 0.1s; }
    .search-wrap input::placeholder { color: #6a8aaa; }
    .search-wrap input:focus { border-color: var(--accent); }
    .search-wrap .search-icon { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #ffffff; font-size: 20px; pointer-events: none; transition: opacity 0.15s; }
    .search-wrap .search-clear { display: none; position: absolute; right: 42px; top: 50%; transform: translateY(-50%); background: var(--border-light); border: none; border-radius: 50%; color: #ffffff; font-size: 13px; font-weight: 900; width: 22px; height: 22px; line-height: 22px; text-align: center; padding: 0; cursor: pointer; transition: background 0.15s, color 0.15s, transform 0.1s; z-index: 2; }
    .search-wrap .search-clear:hover { background: var(--btn-red); color: #fff; transform: translateY(-50%) scale(1.15); }
    .search-wrap.has-value input { padding-right: 72px; }
    .search-wrap.has-value .search-clear { display: block; }
    .result-count { font-size: 18px; color: #ffffff; font-family: 'Share Tech Mono', monospace; white-space: nowrap; }
    .table-wrap { background: var(--panel); border: 1px solid var(--border); border-top: none; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-family: 'Share Tech Mono', monospace; }
    thead tr { background: var(--header-bg); }
    thead th { padding: 16px 22px; text-align: left; font-size: 17px; text-transform: uppercase; letter-spacing: 1.5px; color: #ffffff; font-weight: 700; border-bottom: 1px solid var(--border); white-space: nowrap; }
    tbody tr { border-bottom: 1px solid rgba(46,64,96,0.5); transition: background 0.15s; }
    tbody tr:hover { background: rgba(0,170,255,0.04); }
    tbody tr:last-child { border-bottom: none; }
    td { padding: 16px 22px; font-size: 19px; vertical-align: middle; }
    td.num-col { color: #ffffff; font-size: 18px; }
    td.bill-col { color: var(--accent); font-weight: 700; }
    td.date-col { color: #ffffff; }
    td.name-col { color: #ffffff; font-weight: 600; font-family: 'Barlow', sans-serif; font-size: 20px; }
    td.amount-col { color: var(--accent2); font-weight: 700; font-size: 20px; }
    td.time-col { color: #ffffff; font-size: 18px; }
    .badge-receipt { display: inline-block; padding: 5px 16px; font-size: 17px; font-weight: 700; border-radius: 3px; letter-spacing: 0.5px; border: 1px solid var(--accent); color: var(--accent); background: rgba(0,170,255,0.1); }
    .pay-badges { display: flex; flex-wrap: wrap; gap: 5px; }
    .pb { display: inline-block; padding: 4px 12px; border-radius: 3px; font-size: 16px; font-weight: 700; }
    .pbc { background: rgba(0,196,122,0.12); color: #00c47a; border: 1px solid rgba(0,196,122,0.35); }
    .pbu { background: rgba(0,170,255,0.12); color: #00aaff; border: 1px solid rgba(0,170,255,0.35); }
    .pbk { background: rgba(255,180,0,0.12); color: #ffa030; border: 1px solid rgba(255,180,0,0.35); }
    .pbq { background: rgba(200,150,255,0.12); color: #cc88ff; border: 1px solid rgba(200,150,255,0.35); }
    .pba { background: rgba(255,100,100,0.12); color: #ff8080; border: 1px solid rgba(255,100,100,0.35); }
    .pbo { background: rgba(120,130,150,0.15); color: #ffffff; border: 1px solid var(--border); }
    .del-btn { background: transparent; border: none; color: var(--btn-red); font-size: 18px; font-family: 'Share Tech Mono', monospace; cursor: pointer; padding: 6px 12px; border-radius: 3px; transition: background 0.15s; display: flex; align-items: center; gap: 4px; letter-spacing: 0.5px; }
    .del-btn:hover { background: rgba(224,64,64,0.15); }
    .empty-state { padding: 60px 24px; text-align: center; font-family: 'Share Tech Mono', monospace; color: #ffffff; }
    .empty-state .icon { font-size: 50px; margin-bottom: 14px; display: block; }
    .empty-state p { font-size: 20px; letter-spacing: 1px; }
    .table-footer { background: var(--header-bg); border: 1px solid var(--border); border-top: none; border-radius: 0 0 6px 6px; padding: 14px 32px; display: flex; justify-content: space-between; align-items: center; font-size: 18px; color: #ffffff; font-family: 'Share Tech Mono', monospace; }
    .clear-all-btn { background: transparent; border: 1px solid rgba(224,64,64,0.4); border-radius: 4px; color: rgba(224,64,64,0.85); font-size: 18px; font-family: 'Share Tech Mono', monospace; padding: 7px 18px; cursor: pointer; transition: all 0.2s; }
    .clear-all-btn:hover { border-color: var(--btn-red); color: var(--btn-red); background: rgba(224,64,64,0.08); }
    .msgbox-overlay { display: none; position: fixed; inset: 0; background: rgba(5,10,18,0.82); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; }
    .msgbox-overlay.show { display: flex; animation: mbFadeIn 0.18s ease; }
    @keyframes mbFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes mbSlideUp { from { opacity: 0; transform: translateY(28px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
    .msgbox-card { background: var(--panel); border-radius: 10px; min-width: 340px; max-width: 460px; overflow: hidden; box-shadow: 0 0 0 1px var(--border), 0 24px 64px rgba(0,0,0,0.7); animation: mbSlideUp 0.22s cubic-bezier(0.34,1.56,0.64,1); }
    .msgbox-header { padding: 0 0 0 22px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); font-family: 'Share Tech Mono', monospace; font-size: 16px; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 700; color: #ffffff; height: 46px; }
    .msgbox-header-bar { display: flex; align-items: center; gap: 10px; height: 100%; padding: 0 14px; background: var(--header-bg); }
    .msgbox-dot { width: 9px; height: 9px; border-radius: 50%; }
    .msgbox-body { padding: 28px 30px 24px; display: flex; gap: 18px; align-items: flex-start; }
    .msgbox-icon { font-size: 40px; flex-shrink: 0; line-height: 1; margin-top: 2px; }
    .msgbox-title { font-size: 20px; font-weight: 700; color: #ffffff; margin-bottom: 8px; line-height: 1.3; }
    .msgbox-msg { font-size: 18px; color: #ffffff; line-height: 1.6; font-family: 'Share Tech Mono', monospace; white-space: pre-line; }
    .msgbox-footer { padding: 0 30px 24px; display: flex; justify-content: flex-end; gap: 10px; }
    .msgbox-btn { padding: 11px 28px; border: none; border-radius: 5px; font-size: 18px; font-weight: 700; cursor: pointer; font-family: 'Barlow', sans-serif; letter-spacing: 0.4px; transition: filter 0.15s, transform 0.1s; }
    .msgbox-btn:hover { filter: brightness(1.18); }
    .msgbox-btn:active { transform: scale(0.96); }
    .msgbox-btn-ok      { background: #0077cc; color: #fff; }
    .msgbox-btn-confirm { background: var(--btn-red); color: #fff; }
    .msgbox-btn-cancel  { background: var(--btn-gray); color: #fff; }
    .msgbox-card.type-warn    .msgbox-header { border-top: 3px solid #ffaa00; }
    .msgbox-card.type-warn    .msgbox-dot   { background: #ffaa00; }
    .msgbox-card.type-error   .msgbox-header { border-top: 3px solid var(--btn-red); }
    .msgbox-card.type-error   .msgbox-dot   { background: var(--btn-red); }
    .msgbox-card.type-success .msgbox-header { border-top: 3px solid var(--btn-green); }
    .msgbox-card.type-success .msgbox-dot   { background: var(--btn-green); }
    .msgbox-card.type-confirm .msgbox-header { border-top: 3px solid var(--btn-red); }
    .msgbox-card.type-confirm .msgbox-dot   { background: var(--btn-red); }
    .print-row-btn { background: rgba(0,196,122,0.1); border: 1px solid rgba(0,196,122,0.4); border-radius: 4px; color: #00c47a; font-size: 17px; font-family: 'Share Tech Mono', monospace; padding: 8px 16px; cursor: pointer; letter-spacing: 0.4px; white-space: nowrap; transition: background 0.15s, border-color 0.15s; }
    .print-row-btn:hover { background: rgba(0,196,122,0.22); border-color: #00c47a; }
    .dots-btn { background: var(--panel2); border: 1px solid var(--border); border-radius: 4px; color: #ffffff; font-size: 24px; font-weight: 900; padding: 3px 13px 6px; cursor: pointer; letter-spacing: -1px; transition: all 0.2s; font-family: monospace; line-height: 1; }
    .dots-btn:hover { border-color: var(--accent); color: var(--accent); }
    .dots-btn.active { border-color: var(--btn-red); color: var(--btn-red); background: rgba(224,64,64,0.1); }
    .sel-bar { display: none; background: rgba(224,64,64,0.06); border: 1px solid rgba(224,64,64,0.35); border-top: none; padding: 12px 32px; align-items: center; gap: 14px; flex-wrap: wrap; }
    .sel-bar.show { display: flex; }
    .sel-bar-count { font-family: 'Share Tech Mono', monospace; font-size: 17px; color: #ffffff; }
    .sel-bar-count strong { color: var(--btn-red); font-size: 19px; }
    .sel-bar-btn { border-radius: 4px; font-size: 17px; font-family: 'Share Tech Mono', monospace; padding: 9px 20px; cursor: pointer; transition: background 0.15s, filter 0.15s; letter-spacing: 0.3px; }
    .sel-bar-btn-all  { background: transparent; border: 1px solid var(--accent); color: var(--accent); }
    .sel-bar-btn-all:hover  { background: rgba(0,170,255,0.1); }
    .sel-bar-btn-none { background: transparent; border: 1px solid var(--border-light); color: #ffffff; }
    .sel-bar-btn-none:hover { background: rgba(255,255,255,0.05); }
    .sel-bar-btn-del  { background: var(--btn-red); border: none; color: #fff; font-weight: 700; font-family: 'Barlow', sans-serif; }
    .sel-bar-btn-del:hover  { filter: brightness(1.18); }
    .sel-bar-btn-del:disabled { background: #553030; color: #886666; cursor: not-allowed; filter: none; }
    .sel-bar-btn-close { margin-left: auto; background: transparent; border: 1px solid var(--border-light); border-radius: 4px; color: #ffffff; font-size: 17px; font-family: 'Share Tech Mono', monospace; padding: 9px 16px; cursor: pointer; transition: background 0.15s, border-color 0.15s, color 0.15s; }
    .sel-bar-btn-close:hover { background: rgba(224,64,64,0.12); border-color: var(--btn-red); color: var(--btn-red); }
    .chk-th { width: 44px; text-align: center !important; display: none; }
    .chk-td { text-align: center; display: none; }
    body.sel-mode .chk-th, body.sel-mode .chk-td { display: table-cell; }
    tbody tr.row-sel { background: rgba(224,64,64,0.08) !important; }
    input.row-chk { width: 18px; height: 18px; accent-color: var(--btn-red); cursor: pointer; }
    #printArea { display: none; }
    @media print {
        @page { size: A4 portrait; margin: 0; }
        html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .container { display: none !important; }
        .msgbox-overlay { display: none !important; }
        #printArea { display: block !important; }
    }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════
     PRINT AREA — Same format as receipt.html
════════════════════════════════════════════ -->
<div id="printArea">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  #printPage { width: 210mm; min-height: 297mm; background: #fff; font-family: 'Times New Roman', Times, serif; padding: 0; display: flex; flex-direction: column; }
  .bill-copy { width: 100%; padding: 12mm 16mm 10mm; page-break-inside: avoid; flex: 1; display: flex; flex-direction: column; }
  .bill-title-bar { text-align: center; font-size: 23pt; font-weight: 900; letter-spacing: 6px; text-transform: uppercase; color: #111; padding-bottom: 3mm; margin-bottom: 4mm; border-bottom: 2.5px solid #111; font-family: Arial, sans-serif; }
  .bill-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px double #111; padding-bottom: 4mm; margin-bottom: 4mm; }
  .bill-shop-name { font-size: 30pt; font-weight: 900; color: #111; letter-spacing: 1.5px; text-transform: uppercase; line-height: 1; font-family: 'Times New Roman', serif; }
  .bill-shop-tagline { font-size: 14pt; color: #555; font-family: Arial, sans-serif; margin-top: 2mm; }
  .bill-shop-contact { font-size: 15pt; color: #333; font-family: Arial, sans-serif; margin-top: 1.5mm; font-weight: 600; }
  .bill-header-right { text-align: right; flex-shrink: 0; margin-left: 8mm; }
  .bill-header-right .bm-label { font-size: 13pt; text-transform: uppercase; letter-spacing: 1.5px; color: #666; font-weight: 700; font-family: Arial, sans-serif; display: block; margin-bottom: 1mm; }
  .bill-header-right .bm-val { font-size: 21pt; font-weight: 700; color: #111; font-family: 'Times New Roman', serif; display: block; }
  .bill-fields { border: 1.5px solid #111; margin-bottom: 0; }
  .bill-field-row { display: flex; align-items: stretch; border-bottom: 1px solid #ccc; }
  .bill-field-row:last-child { border-bottom: none; }
  .bfr-label { width: 50mm; flex-shrink: 0; background: #f5f5f5; border-right: 1.5px solid #ccc; padding: 3.5mm 4mm; font-size: 13pt; text-transform: uppercase; letter-spacing: 1px; color: #555; font-weight: 700; font-family: Arial, sans-serif; display: flex; align-items: center; }
  .bfr-val { flex: 1; padding: 3.5mm 5mm; font-size: 18pt; font-weight: 700; color: #111; font-family: 'Times New Roman', serif; display: flex; align-items: center; }
  .bfr-val.amount { font-size: 20pt; }
  .bill-pay-table { width: 100%; border-collapse: collapse; border: 1.5px solid #111; border-top: none; margin-bottom: 0; }
  .bill-pay-table thead tr { background: #222; color: #fff; }
  .bill-pay-table thead th { font-size: 13pt; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; font-family: Arial, sans-serif; padding: 3.5mm 5mm; text-align: left; }
  .bill-pay-table thead th:last-child { text-align: right; }
  .bill-pay-table tbody tr { border-bottom: 1px solid #ddd; }
  .bill-pay-table tbody tr:last-child { border-bottom: none; }
  .bill-pay-table tbody td { font-size: 17pt; font-family: 'Times New Roman', serif; padding: 3.5mm 5mm; color: #111; }
  .bill-pay-table tbody td:last-child { text-align: right; font-weight: 700; }
  .bill-pay-table tfoot tr { border-top: 2px solid #111; background: #f0f0f0; }
  .bill-pay-table tfoot td { font-size: 15pt; font-family: Arial, sans-serif; font-weight: 700; padding: 3.5mm 5mm; text-transform: uppercase; letter-spacing: 0.5px; color: #111; }
  .bill-pay-table tfoot td:last-child { text-align: right; font-size: 18pt; font-family: 'Times New Roman', serif; }
  .bill-balance { border: 2.5px solid #111; border-top: none; display: flex; justify-content: space-between; align-items: center; padding: 4.5mm 5mm; background: #111; }
  .bill-balance-lbl { font-size: 15pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #fff; font-family: Arial, sans-serif; }
  .bill-balance-val { font-size: 22pt; font-weight: 900; color: #fff; font-family: 'Times New Roman', serif; }
  /* ── SIGNATURE SECTION ── */
  .bill-sig-section { display: flex; justify-content: flex-end; margin-top: 10mm; }
  .bill-sig-block { text-align: center; min-width: 60mm; }
  .bill-sig-line { width: 60mm; border-bottom: 1.5px solid #111; margin: 0 auto 2.5mm; height: 14mm; }
  .bill-sig-label { font-size: 12pt; text-transform: uppercase; letter-spacing: 1.5px; color: #333; font-weight: 700; font-family: Arial, sans-serif; }
  .bill-sig-sublabel { font-size: 11pt; color: #666; font-family: Arial, sans-serif; margin-top: 1mm; }
  /* ── FOOTER ── */
  .bill-sep { border: none; border-top: 1.5px dashed #999; margin: 6mm 0 4mm; }
  .bill-footer { padding-top: 4mm; display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid #333; margin-top: auto; }
  .bill-footer-left { font-size: 15pt; font-weight: 700; color: #111; font-family: 'Times New Roman', serif; line-height: 1.4; }
  .bill-footer-thanks { font-size: 14pt; font-style: italic; color: #444; font-family: 'Times New Roman', serif; text-align: center; }
</style>
<div id="printPage">
  <div class="bill-copy">
    <div class="bill-title-bar">Payment Receipt</div>
    <div class="bill-header">
      <div class="bill-header-left">
        <div class="bill-shop-name">Sarthi Sports Wear</div>
        <div class="bill-shop-tagline">Sports Goods &amp; Equipment</div>
        <div class="bill-shop-contact">📞 0762-0425141 &nbsp;&nbsp;|&nbsp;&nbsp; 📱 9422107750</div>
      </div>
      <div class="bill-header-right">
        <span class="bm-label">Date</span>
        <span class="bm-val" id="p1-date">—</span>
      </div>
    </div>
    <div class="bill-fields" style="border-top:1.5px solid #111;">
      <div class="bill-field-row">
        <div class="bfr-label">Party Name</div>
        <div class="bfr-val" id="p1-customer">—</div>
      </div>

    </div>
    <table class="bill-pay-table">
      <thead><tr><th>#</th><th>Payment Mode</th><th>Amount</th></tr></thead>
      <tbody id="p1-pay-rows"></tbody>
      <tfoot><tr><td colspan="2">Total Amount In Figures</td><td id="p1-total-paid">—</td></tr></tfoot>
    </table>
    <div class="bill-balance" id="p1-bal-bar">
      <span class="bill-balance-lbl">Balance Due</span>
      <span class="bill-balance-val" id="p1-balance">₹ 0.00</span>
    </div>
    <!-- Signature block — right-aligned, directly after totals -->
    <div class="bill-sig-section">
      <div class="bill-sig-block">
        <div class="bill-sig-line"></div>
        <div class="bill-sig-label">Authorised Signature</div>
        <div class="bill-sig-sublabel">Sarthi Sports Wear</div>
      </div>
    </div>
    <hr class="bill-sep">
    <div class="bill-footer">
      <div class="bill-footer-left">Sarthi Sports Wear</div>
      <div class="bill-footer-thanks">★ Thank You, Visit Again! ★</div>
    </div>
  </div>
</div>
</div>

<!-- ═══ CUSTOM MESSAGE BOX ═══ -->
<div class="msgbox-overlay" id="msgboxOverlay">
  <div class="msgbox-card" id="msgboxCard">
    <div class="msgbox-header"><span id="msgbox-header-text">NOTICE</span><div class="msgbox-header-bar"><div class="msgbox-dot"></div></div></div>
    <div class="msgbox-body"><div class="msgbox-icon" id="msgbox-icon">⚠️</div><div><div class="msgbox-title" id="msgbox-title">Notice</div><div class="msgbox-msg" id="msgbox-msg"></div></div></div>
    <div class="msgbox-footer" id="msgbox-footer"></div>
  </div>
</div>

<div class="container">

  <div class="topbar">
    <div class="topbar-left">
      <span class="topbar-icon">📋</span>
      <span class="topbar-title">History</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <button class="dots-btn" id="dotsBtn" onclick="toggleSelMode()" title="Select to delete">⋮</button>
      <a class="btn-back" href="receipt.php">← BACK</a>
    </div>
  </div>

  <div class="page-header">
    <span class="lightning">⚡</span>
    <h2>Sarthi Sports Wear — Receipt History</h2>
  </div>

  <div class="sel-bar" id="selBar">
    <input type="checkbox" class="row-chk" id="chkAll" onchange="toggleChkAll(this)">
    <button class="sel-bar-btn sel-bar-btn-all" onclick="selAll()">☑ Select All</button>
    <button class="sel-bar-btn sel-bar-btn-none" onclick="selNone()">☐ Deselect All</button>
    <span class="sel-bar-count">Selected: <strong id="selCount">0</strong></span>
    <button class="sel-bar-btn sel-bar-btn-del" id="selDelBtn" disabled onclick="selDelete()">🗑 Delete Selected</button>
    <button class="sel-bar-btn-close" onclick="toggleSelMode()">✕</button>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="num" id="stat-total">0</div><div class="lbl">Total Records</div></div>
    <div class="stat-card"><div class="num" id="stat-amount">₹0</div><div class="lbl">Total Amount</div></div>
    <div class="stat-card"><div class="num" id="stat-filtered">0</div><div class="lbl">Showing</div></div>
  </div>

  <div class="tabs-bar">
    <button class="tab active" onclick="switchTab('all')" id="tab-all"><span class="tab-icon">≡</span> ALL</button>
    <button class="tab" onclick="switchTab('bydate')" id="tab-bydate"><span class="tab-icon">📅</span> BY DATE</button>
    <button class="tab" onclick="switchTab('bymonth')" id="tab-bymonth"><span class="tab-icon">📆</span> BY MONTH</button>
  </div>

  <div class="filter-panel" id="filter-bydate">
    <div class="filter-group"><label>From Date</label><input type="date" id="date-from"></div>
    <div class="filter-group"><label>To Date</label><input type="date" id="date-to"></div>
    <button class="filter-btn" onclick="applyDateFilter()">APPLY</button>
    <button class="filter-clear" onclick="clearDateFilter()">CLEAR</button>
  </div>

  <div class="filter-panel" id="filter-bymonth">
    <div class="filter-group">
      <label>Month</label>
      <select id="month-select">
        <option value="">— All Months —</option>
        <option value="01">January</option><option value="02">February</option>
        <option value="03">March</option><option value="04">April</option>
        <option value="05">May</option><option value="06">June</option>
        <option value="07">July</option><option value="08">August</option>
        <option value="09">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
      </select>
    </div>
    <div class="filter-group"><label>Year</label><input type="number" id="year-input" placeholder="e.g. 2026" min="2020" max="2099" style="width:130px;"></div>
    <button class="filter-btn" onclick="applyMonthFilter()">APPLY</button>
    <button class="filter-clear" onclick="clearMonthFilter()">CLEAR</button>
  </div>

  <div class="controls-bar">
    <div class="type-btns">
      <button class="type-btn active" onclick="setTypeFilter('all')" id="tbtn-all">ALL</button>
      <button class="type-btn" onclick="setTypeFilter('cash')" id="tbtn-cash">CASH</button>
      <button class="type-btn" onclick="setTypeFilter('upi')" id="tbtn-upi">UPI</button>
      <button class="type-btn" onclick="setTypeFilter('card')" id="tbtn-card">CARD</button>
      <button class="type-btn" onclick="setTypeFilter('cheque')" id="tbtn-cheque">CHEQUE</button>
      <button class="type-btn" onclick="setTypeFilter('advance')" id="tbtn-advance">ADVANCE</button>
      <button class="type-btn" onclick="setTypeFilter('online')" id="tbtn-online">ONLINE</button>
      <button class="type-btn" onclick="setTypeFilter('neft/rtgs')" id="tbtn-neft">NEFT/RTGS</button>
      <button class="type-btn" onclick="setTypeFilter('other')" id="tbtn-other">OTHER</button>
    </div>
    <div class="search-wrap" id="searchWrap">
      <input type="text" id="search-box" placeholder="Search bill no, customer name, amount..." oninput="onSearchInput()">
      <button class="search-clear" id="search-clear-btn" onclick="clearSearch()" title="Clear search">✕</button>
      <span class="search-icon">🔍</span>
    </div>
    <span class="result-count" id="result-count">0 records</span>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th class="chk-th"></th>
          <th>#</th>
          <th>Bill No.</th>
          <th>Date</th>
          <th>Customer Name</th>
          <th>Payment Mode</th>
          <th>Amount Paid</th>
          <th>Net Amount</th>
          <th>Balance</th>
          <th>Print</th>
          <th>Saved At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="historyTable">
        <tr><td colspan="12"><div class="empty-state"><span class="icon">📭</span><p>No records found</p></div></td></tr>
      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span id="footer-text">Showing all records</span>
    <button class="clear-all-btn" onclick="clearAllHistory()">✕ CLEAR ALL HISTORY</button>
  </div>

</div>

<script>
var allRecords = [];
var activeTab = 'all';
var activeType = 'all';
var dateFromFilter = '';
var dateToFilter = '';
var monthFilter = '';
var yearFilter = '';
var selModeOn = false;
var selectedSet = new Set();
var visibleIdx = [];

function pad(n){ return String(n).length<2?'0'+String(n):String(n); }

/* ═══ MESSAGE BOX ═══ */
var msgboxTypes = {
    warn:    { icon:'⚠️',  header:'WARNING',  title:'Warning',        btnClass:'msgbox-btn-ok',      btnText:'OK' },
    error:   { icon:'❌',  header:'ERROR',    title:'Error',          btnClass:'msgbox-btn-ok',      btnText:'Close' },
    success: { icon:'✅',  header:'SUCCESS',  title:'Success',        btnClass:'msgbox-btn-ok',      btnText:'OK' },
    confirm: { icon:'🗑️', header:'CONFIRM',  title:'Confirm Action', btnClass:'msgbox-btn-confirm', btnText:'Yes, Delete' }
};
function showMsg(type, message, opts, callback){
    opts = opts || {};
    var cfg = msgboxTypes[type] || msgboxTypes['warn'];
    var card = document.getElementById('msgboxCard');
    card.className = 'msgbox-card type-' + type;
    document.getElementById('msgbox-header-text').textContent = opts.header || cfg.header;
    document.getElementById('msgbox-icon').textContent = opts.icon || cfg.icon;
    document.getElementById('msgbox-title').textContent = opts.title || cfg.title;
    document.getElementById('msgbox-msg').textContent = message;
    var footer = document.getElementById('msgbox-footer');
    footer.innerHTML = '';
    if(type === 'confirm'){
        var cancelBtn = document.createElement('button');
        cancelBtn.className = 'msgbox-btn msgbox-btn-cancel';
        cancelBtn.textContent = opts.cancelText || 'Cancel';
        cancelBtn.onclick = function(){ hideMsgbox(); if(callback) callback(false); };
        footer.appendChild(cancelBtn);
    }
    var okBtn = document.createElement('button');
    okBtn.className = 'msgbox-btn ' + cfg.btnClass;
    okBtn.textContent = opts.okText || cfg.btnText;
    okBtn.onclick = function(){ hideMsgbox(); if(callback) callback(true); };
    footer.appendChild(okBtn);
    document.getElementById('msgboxOverlay').classList.add('show');
    setTimeout(function(){ okBtn.focus(); }, 100);
}
function hideMsgbox(){ document.getElementById('msgboxOverlay').classList.remove('show'); }
document.getElementById('msgboxOverlay').addEventListener('click', function(e){ if(e.target === this) hideMsgbox(); });
document.addEventListener('keydown', function(e){ if(e.key === 'Escape') hideMsgbox(); });

/* ═══ APP LOGIC ═══ */
function loadHistory(){
    allRecords = JSON.parse(localStorage.getItem('ssw_receipt_history') || '[]');
    updateStats();
    applyFilters();
}
function updateStats(){
    var total = allRecords.length;
    var totalAmt = 0;
    allRecords.forEach(function(r){ totalAmt += (r.amountPaid || 0); });
    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-amount').textContent = '₹' + Math.round(totalAmt).toLocaleString('en-IN');
}
function switchTab(tab){
    activeTab = tab;
    ['all','bydate','bymonth'].forEach(function(t){
        document.getElementById('tab-'+t).classList.toggle('active', t===tab);
        var fp = document.getElementById('filter-'+t);
        if(fp) fp.classList.toggle('active', t===tab);
    });
    if(tab==='all'){ dateFromFilter=''; dateToFilter=''; monthFilter=''; yearFilter=''; }
    applyFilters();
}
function applyDateFilter(){ dateFromFilter = document.getElementById('date-from').value; dateToFilter = document.getElementById('date-to').value; applyFilters(); }
function clearDateFilter(){ dateFromFilter=''; dateToFilter=''; document.getElementById('date-from').value=''; document.getElementById('date-to').value=''; applyFilters(); }
function applyMonthFilter(){ monthFilter = document.getElementById('month-select').value; yearFilter = document.getElementById('year-input').value; applyFilters(); }
function clearMonthFilter(){ monthFilter=''; yearFilter=''; document.getElementById('month-select').value=''; document.getElementById('year-input').value=''; applyFilters(); }
function setTypeFilter(type){
    activeType = type;
    ['all','cash','upi','card','cheque','online','neft','other','advance'].forEach(function(t){
        var el = document.getElementById('tbtn-'+t);
        if(el){ var val = t==='neft' ? 'neft/rtgs' : t; el.classList.toggle('active', val===type); }
    });
    applyFilters();
}
function onSearchInput(){
    var val = document.getElementById('search-box').value;
    var wrap = document.getElementById('searchWrap');
    if(val){ wrap.classList.add('has-value'); } else { wrap.classList.remove('has-value'); }
    applyFilters();
}
function clearSearch(){
    var box = document.getElementById('search-box');
    box.value = '';
    document.getElementById('searchWrap').classList.remove('has-value');
    box.focus();
    applyFilters();
}
function applyFilters(){
    var search = (document.getElementById('search-box').value || '').toLowerCase().trim();
    var filtered = [];
    visibleIdx = [];
    allRecords.forEach(function(r, realIdx){
        if(activeTab==='bydate'){ if(dateFromFilter && r.date < dateFromFilter) return; if(dateToFilter && r.date > dateToFilter) return; }
        if(activeTab==='bymonth'){ if(monthFilter && r.date){ var mm=r.date.split('-')[1]; if(mm!==monthFilter) return; } if(yearFilter && r.date){ var yy=r.date.split('-')[0]; if(yy!==String(yearFilter)) return; } }
        if(activeType !== 'all'){ var hasModes=(r.payments||[]).some(function(p){ return p.mode.toLowerCase()===activeType; }); if(!hasModes) return; }
        if(search){ var haystack=[r.billNo,r.customer,r.receiptNo,String(r.netAmount||''),String(r.amountPaid||''),r.dateFormatted].join(' ').toLowerCase(); if(haystack.indexOf(search)===-1) return; }
        filtered.push({ r: r, idx: realIdx });
        visibleIdx.push(realIdx);
    });
    renderTable(filtered);
    document.getElementById('stat-filtered').textContent = filtered.length;
    document.getElementById('result-count').textContent = filtered.length + ' record' + (filtered.length!==1?'s':'');
    var footerParts = [];
    if(activeTab==='bydate' && (dateFromFilter||dateToFilter)) footerParts.push('Date: '+(dateFromFilter||'...')+' → '+(dateToFilter||'...'));
    if(activeTab==='bymonth' && (monthFilter||yearFilter)) footerParts.push('Month: '+(monthFilter||'all')+' / '+(yearFilter||'all'));
    if(search) footerParts.push('Search: "'+search+'"');
    document.getElementById('footer-text').textContent = footerParts.length ? footerParts.join(' | ') : 'Showing all records';
    updateSelUI();
}
function getPayClass(mode){
    var m=(mode||'').toLowerCase();
    if(m==='cash') return 'pbc'; if(m==='upi') return 'pbu'; if(m==='card') return 'pbk';
    if(m==='cheque') return 'pbq'; if(m==='advance') return 'pba'; return 'pbo';
}
function renderTable(records){
    var tbody = document.getElementById('historyTable');
    if(!records.length){
        tbody.innerHTML = '<tr><td colspan="12"><div class="empty-state"><span class="icon">📭</span><p>No records found</p></div></td></tr>';
        return;
    }
    var html = '';
    records.forEach(function(item, i){
        var r = item.r;
        var globalIdx = item.idx;
        var isSel = selectedSet.has(globalIdx);
        var payBadges = '';
        (r.payments||[]).forEach(function(p){ payBadges += '<span class="pb '+getPayClass(p.mode)+'">'+p.mode+': ₹'+p.amt.toFixed(2)+'</span>'; });
        var balColor = (r.balance||0)>0 ? 'color:#e04040;' : 'color:var(--accent2);';
        var balText  = (r.balance||0)>0 ? '₹'+r.balance.toFixed(2) : '✓ Paid';
        html += '<tr class="'+(isSel?'row-sel':'')+'">' +
            '<td class="chk-td"><input type="checkbox" class="row-chk" '+(isSel?'checked':'')+' onchange="rowChk(this,'+globalIdx+')"></td>' +
            '<td class="num-col">'+(i+1)+'</td>' +
            '<td class="bill-col"><span class="badge-receipt">'+r.billNo+'</span></td>' +
            '<td class="date-col">'+(r.date ? r.date.split('-').reverse().join('-') : '—')+'</td>' +
            '<td class="name-col">'+escHtml(r.customer||'—')+'</td>' +
            '<td><div class="pay-badges">'+payBadges+'</div></td>' +
            '<td class="amount-col">₹'+(r.amountPaid||0).toFixed(2)+'</td>' +
            '<td style="font-weight:700;color:#ffffff;font-size:19px;">₹'+(r.netAmount||0).toFixed(2)+'</td>' +
            '<td style="font-weight:700;font-size:19px;'+balColor+'">'+balText+'</td>' +
            '<td><button class="print-row-btn" onclick="printRecord('+globalIdx+')">🖨 PRINT</button></td>' +
            '<td class="time-col">'+(r.printedAt ? r.printedAt.split(' ')[0] : '—')+'</td>' +
            '<td><button class="del-btn" onclick="deleteRecord('+globalIdx+')">✕ DEL</button></td>' +
            '</tr>';
    });
    tbody.innerHTML = html;
}
function escHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

/* ═══ SELECT MODE ═══ */
function toggleSelMode(){
    selModeOn = !selModeOn;
    document.body.classList.toggle('sel-mode', selModeOn);
    document.getElementById('dotsBtn').classList.toggle('active', selModeOn);
    document.getElementById('selBar').classList.toggle('show', selModeOn);
    if(!selModeOn){ selectedSet.clear(); applyFilters(); }
    updateSelUI();
}
function rowChk(chk, idx){
    if(chk.checked) selectedSet.add(idx); else selectedSet.delete(idx);
    var row = chk.closest('tr');
    if(row) row.className = chk.checked ? 'row-sel' : '';
    updateSelUI();
}
function toggleChkAll(chk){ if(chk.checked) selAll(); else selNone(); }
function selAll(){ visibleIdx.forEach(function(i){ selectedSet.add(i); }); applyFilters(); }
function selNone(){ selectedSet.clear(); applyFilters(); }
function updateSelUI(){
    var count = selectedSet.size;
    var el = document.getElementById('selCount');
    if(el) el.textContent = count;
    var btn = document.getElementById('selDelBtn');
    if(btn){ btn.disabled = count===0; btn.textContent = count>0 ? '🗑 Delete Selected ('+count+')' : '🗑 Delete Selected'; }
    var chkAll = document.getElementById('chkAll');
    if(chkAll){
        var selVis = visibleIdx.filter(function(i){ return selectedSet.has(i); }).length;
        if(visibleIdx.length>0 && selVis===visibleIdx.length){ chkAll.checked=true; chkAll.indeterminate=false; }
        else if(selVis>0){ chkAll.checked=false; chkAll.indeterminate=true; }
        else { chkAll.checked=false; chkAll.indeterminate=false; }
    }
}
function selDelete(){
    var count = selectedSet.size;
    if(!count) return;
    showMsg('confirm',
        'You are about to permanently delete '+count+' selected record(s).\n\nThis action cannot be undone.',
        { title: 'Delete '+count+' Record(s)?', header: 'CONFIRM BULK DELETE', icon: '🗑️', okText: 'Yes, Delete All', cancelText: 'Cancel' },
        function(confirmed){
            if(!confirmed) return;
            var toDelete = Array.from(selectedSet).sort(function(a,b){ return b-a; });
            toDelete.forEach(function(i){ allRecords.splice(i,1); });
            localStorage.setItem('ssw_receipt_history', JSON.stringify(allRecords));
            selectedSet.clear();
            selModeOn = false;
            document.body.classList.remove('sel-mode');
            document.getElementById('dotsBtn').classList.remove('active');
            document.getElementById('selBar').classList.remove('show');
            updateStats();
            applyFilters();
        }
    );
}

/* ═══ PRINT — uses same format as receipt.html ═══ */
function printRecord(idx){
    var r = allRecords[idx];
    if(!r) return;
    var dateParts = (r.date||'').split('-');
    var dateStr = dateParts.length===3 ? dateParts[2]+'-'+dateParts[1]+'-'+dateParts[0] : (r.date || '—');

    document.getElementById('p1-date').textContent     = dateStr;
    document.getElementById('p1-customer').textContent = r.customer || '—';
    var figEl=document.getElementById('p1-figures'); if(figEl) figEl.textContent='₹ '+(r.netAmount||0).toFixed(2);

    /* Payment rows */
    var tbody = document.getElementById('p1-pay-rows');
    tbody.innerHTML = '';
    (r.payments||[]).forEach(function(p, i){
        var tr = document.createElement('tr');
        tr.innerHTML = '<td>'+(i+1)+'</td><td>'+p.mode+'</td><td>₹ '+p.amt.toFixed(2)+'</td>';
        tbody.appendChild(tr);
    });
    document.getElementById('p1-total-paid').textContent = '₹ ' + (r.netAmount||0).toFixed(2);

    /* Balance bar */
    var balBar = document.getElementById('p1-bal-bar');
    balBar.style.display = 'none';

    setTimeout(function(){ window.print(); }, 100);
}

function deleteRecord(idx){
    var r = allRecords[idx];
    var label = r ? (r.billNo + ' — ' + (r.customer || 'Unknown')) : 'this record';
    showMsg('confirm',
        'You are about to permanently delete:\n' + label + '\n\nThis action cannot be undone.',
        { title: 'Delete Record?', header: 'CONFIRM DELETE', icon: '🗑️', okText: 'Yes, Delete', cancelText: 'Cancel' },
        function(confirmed){
            if(!confirmed) return;
            allRecords.splice(idx, 1);
            localStorage.setItem('ssw_receipt_history', JSON.stringify(allRecords));
            updateStats();
            applyFilters();
        }
    );
}
function clearAllHistory(){
    var count = allRecords.length;
    if(count === 0){ showMsg('warn', 'There are no records in history to clear.', { title: 'Nothing to Clear', header: 'HISTORY EMPTY', icon: '📭', okText: 'OK' }); return; }
    showMsg('confirm',
        'You are about to permanently delete all ' + count + ' receipt record(s).\n\nThis action cannot be undone.',
        { title: 'Clear All History?', header: 'DANGER — CONFIRM', icon: '⚠️', okText: 'Yes, Clear All', cancelText: 'Cancel' },
        function(confirmed){
            if(!confirmed) return;
            allRecords = [];
            localStorage.setItem('ssw_receipt_history', JSON.stringify(allRecords));
            updateStats();
            applyFilters();
        }
    );
}

document.getElementById('year-input').value = new Date().getFullYear();
loadHistory();
</script>
</body>
</html>