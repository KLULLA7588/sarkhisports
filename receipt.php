<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarthi Sports Wear — Payment Receipt</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --bg: #0f1720; --panel: #1e2a3a; --panel2: #243040;
        --border: #2e4060; --border-light: #3a5070;
        --accent: #00aaff; --accent2: #00d4aa;
        --text: #ffffff; --text-dim: #ffffff; --text-label: #ffffff;
        --input-bg: #111c28; --header-bg: #162030;
        --btn-green: #00c47a; --btn-gray: #445566; --btn-red: #e04040; --btn-blue: #0077cc;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Barlow', sans-serif; background: var(--bg); min-height: 100vh; color: #ffffff; padding: 16px; display: flex; flex-direction: column; align-items: center; font-size: 18px; }
    .container { max-width: 1600px; width: 100%; }

    .topbar { background: var(--header-bg); border: 1px solid var(--border); border-bottom: 2px solid var(--accent); border-radius: 6px 6px 0 0; padding: 12px 36px; display: flex; align-items: center; justify-content: space-between; font-size: 20px; color: #ffffff; font-family: 'Share Tech Mono', monospace; }
    .topbar .brand { color: var(--accent); font-size: 22px; font-weight: bold; letter-spacing: 1px; }
    .topbar-right { display: flex; align-items: center; gap: 16px; }
    .info-pills { display: flex; gap: 16px; }
    .pill { background: var(--panel2); padding: 5px 16px; border-radius: 3px; border: 1px solid var(--border); font-size: 20px; color: #ffffff; }
    .pill span { color: var(--accent2); }
    .btn-history { background: var(--panel2); border: 1px solid var(--accent); border-radius: 4px; color: var(--accent); font-size: 19px; font-family: 'Share Tech Mono', monospace; padding: 6px 16px; cursor: pointer; text-decoration: none; letter-spacing: 0.5px; transition: background 0.2s; }
    .btn-history:hover { background: rgba(0,170,255,0.15); }

    .shop-header { background: var(--panel); border: 1px solid var(--border); border-top: none; padding: 28px 48px; display: flex; align-items: center; justify-content: space-between; }
    .shop-header h1 { font-size: 42px; font-weight: 700; color: var(--accent); letter-spacing: 0.5px; line-height: 1.1; }
    .shop-header p  { font-size: 20px; color: #ffffff; margin-top: 5px; }
    .receipt-badge { background: #004a88; border: 1px solid var(--accent); border-radius: 4px; padding: 14px 40px; font-family: 'Share Tech Mono', monospace; font-size: 21px; color: #ffffff; text-align: center; line-height: 1.5; }
    .receipt-badge span { font-size: 32px; font-weight: bold; display: block; color: var(--accent2); letter-spacing: 1px; }

    .section-label { background: var(--header-bg); border: 1px solid var(--border); border-top: none; padding: 6px 32px; font-size: 25px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; font-family: 'Share Tech Mono', monospace; }

    .form-panel { background: var(--panel); border: 1px solid var(--border); border-top: none; padding: 30px 48px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; }
    .hfield { display: flex; flex-direction: column; gap: 7px; }
    .hfield label { font-size: 22px; text-transform: uppercase; letter-spacing: 0.8px; color: #ffffff; font-weight: 600; font-family: 'Share Tech Mono', monospace; }
    .hfield input, .hfield select { background: var(--input-bg); border: 1px solid var(--border); border-radius: 4px; color: #ffffff; font-size: 28px; font-family: 'Share Tech Mono', monospace; padding: 16px 18px; transition: border-color 0.2s; outline: none; }
    .hfield input:focus, .hfield select:focus { border-color: var(--accent); background: #0d1520; }
    .hfield input::placeholder { color: #6a8aaa; }

    .pay-panel { background: var(--panel); border: 1px solid var(--border); border-top: none; padding: 26px 48px; display: flex; flex-direction: column; gap: 13px; }
    .paymode-entry { display: grid; grid-template-columns: 1fr 260px 42px; gap: 14px; align-items: center; }
    .paymode-select { background: var(--input-bg); border: 1px solid var(--border); border-radius: 4px; color: #ffffff; font-size: 28px; font-family: 'Share Tech Mono', monospace; padding: 15px 18px; outline: none; width: 100%; }
    .paymode-select:focus { border-color: var(--accent); }
    .paymode-amount { background: var(--input-bg); border: 1px solid var(--border); border-radius: 4px; color: var(--accent2); font-size: 30px; font-family: 'Share Tech Mono', monospace; text-align: right; padding: 15px 18px; outline: none; width: 100%; }
    .paymode-amount:focus { border-color: var(--accent); }
    .paymode-amount::placeholder { color: #6a8aaa; }
    .paymode-remove { background: transparent; border: 1px solid var(--border); border-radius: 4px; color: var(--btn-red); font-size: 22px; width: 42px; height: 42px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .paymode-remove:hover { background: rgba(224,64,64,0.15); border-color: var(--btn-red); }
    .paymode-add-btn { background: transparent; border: 1px dashed var(--border-light); border-radius: 4px; color: var(--accent); font-size: 20px; font-weight: 600; padding: 10px 16px; cursor: pointer; font-family: 'Barlow', sans-serif; letter-spacing: 0.3px; }
    .paymode-add-btn:hover { background: rgba(0,170,255,0.08); border-color: var(--accent); }

    .totals-panel { background: var(--panel); border: 1px solid var(--border); border-top: none; padding: 24px 48px; }
    .total-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .total-row:last-child { border-bottom: none; }
    .total-row label { font-size: 20px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Share Tech Mono', monospace; }
    .total-row .val { font-family: 'Share Tech Mono', monospace; font-size: 26px; color: #ffffff; }
    .total-row.balance-row { background: rgba(0,212,170,0.07); border: 1px solid rgba(0,212,170,0.28); border-radius: 5px; padding: 14px 20px; margin-top: 8px; }
    .total-row.balance-row label { color: var(--accent2); font-size: 21px; }
    .total-row.balance-row .val { font-size: 36px; color: var(--accent2); font-weight: bold; }

    .btn-bar { background: var(--header-bg); border: 1px solid var(--border); border-top: 2px solid var(--border-light); border-radius: 0 0 6px 6px; padding: 18px 48px; display: flex; gap: 14px; }
    .btn { padding: 13px 40px; border: none; border-radius: 4px; font-size: 21px; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: filter 0.15s, transform 0.1s; font-family: 'Barlow', sans-serif; }
    .btn:hover { filter: brightness(1.15); }
    .btn:active { transform: scale(0.97); }
    .btn-print  { background: var(--btn-green); color: #000; }
    .btn-done   { background: var(--btn-blue);  color: #fff; }
    .btn-clear  { background: var(--btn-gray);  color: #ffffff; }

    /* ═══ CUSTOM MESSAGE BOX ═══ */
    .msgbox-overlay { display: none; position: fixed; inset: 0; background: rgba(5,10,18,0.82); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; animation: fadeIn 0.18s ease; }
    .msgbox-overlay.show { display: flex; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(28px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
    .msgbox-card { background: var(--panel); border-radius: 10px; min-width: 360px; max-width: 480px; overflow: hidden; box-shadow: 0 0 0 1px var(--border), 0 24px 64px rgba(0,0,0,0.7); animation: slideUp 0.22s cubic-bezier(0.34,1.56,0.64,1); }
    .msgbox-header { padding: 0 0 0 22px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); font-family: 'Share Tech Mono', monospace; font-size: 17px; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 700; color: #ffffff; height: 46px; }
    .msgbox-header-bar { display: flex; align-items: center; gap: 10px; height: 100%; padding: 0 14px; background: var(--header-bg); }
    .msgbox-dot { width: 9px; height: 9px; border-radius: 50%; }
    .msgbox-body { padding: 28px 30px 24px; display: flex; gap: 18px; align-items: flex-start; }
    .msgbox-icon { font-size: 42px; flex-shrink: 0; line-height: 1; margin-top: 2px; }
    .msgbox-title { font-size: 21px; font-weight: 700; color: #ffffff; margin-bottom: 8px; line-height: 1.3; }
    .msgbox-msg { font-size: 19px; color: #ffffff; line-height: 1.6; font-family: 'Share Tech Mono', monospace; white-space: pre-line; }
    .msgbox-footer { padding: 0 30px 24px; display: flex; justify-content: flex-end; gap: 10px; }
    .msgbox-btn { padding: 10px 28px; border: none; border-radius: 5px; font-size: 19px; font-weight: 700; cursor: pointer; font-family: 'Barlow', sans-serif; letter-spacing: 0.4px; transition: filter 0.15s, transform 0.1s; }
    .msgbox-btn:hover { filter: brightness(1.18); }
    .msgbox-btn:active { transform: scale(0.96); }
    .msgbox-btn-ok { background: var(--btn-blue); color: #fff; }
    .msgbox-btn-confirm { background: var(--btn-green); color: #000; }
    .msgbox-btn-cancel { background: var(--btn-gray); color: #fff; }
    .msgbox-card.type-warn .msgbox-header { border-top: 3px solid #ffaa00; }
    .msgbox-card.type-warn .msgbox-dot { background: #ffaa00; }
    .msgbox-card.type-error .msgbox-header { border-top: 3px solid var(--btn-red); }
    .msgbox-card.type-error .msgbox-dot { background: var(--btn-red); }
    .msgbox-card.type-success .msgbox-header { border-top: 3px solid var(--btn-green); }
    .msgbox-card.type-success .msgbox-dot { background: var(--btn-green); }
    .msgbox-card.type-confirm .msgbox-header { border-top: 3px solid var(--accent); }
    .msgbox-card.type-confirm .msgbox-dot { background: var(--accent); }

    /* Done overlay */
    .done-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999; align-items: center; justify-content: center; }
    .done-overlay.show { display: flex; }
    .done-card { background: var(--panel); border: 2px solid var(--accent2); border-radius: 10px; padding: 44px 60px; text-align: center; }
    .done-card .icon { font-size: 68px; display: block; margin-bottom: 14px; }
    .done-card h2 { font-size: 34px; color: var(--accent2); margin-bottom: 10px; }
    .done-card p { color: #ffffff; font-size: 21px; margin-bottom: 8px; }
    .done-card .saved-info { background: rgba(0,212,170,0.1); border: 1px solid rgba(0,212,170,0.3); border-radius: 6px; padding: 12px 20px; margin: 16px 0 22px; font-family: 'Share Tech Mono', monospace; font-size: 20px; color: var(--accent2); white-space: pre-line; }
    .done-card button { background: var(--btn-green); color: #000; border: none; border-radius: 5px; padding: 13px 38px; font-size: 22px; font-weight: 700; cursor: pointer; }

    #bill_seq::-webkit-outer-spin-button,
    #bill_seq::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    #bill_seq { -moz-appearance: textfield; }

    #printArea { display: none; }

    @media print {
        @page { size: A4 portrait; margin: 0; }
        html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .container, .done-overlay, .msgbox-overlay { display: none !important; }
        #printArea { display: block !important; }
    }
</style>
</head>
<body>

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

  /* ── SIGNATURE SECTION (right-aligned, after totals) ── */
  .bill-sig-section { display: flex; justify-content: flex-end; margin-top: 10mm; padding-top: 0; }
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
      <span class="bill-balance-lbl" id="p1-bal-lbl">Balance Due</span>
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

<!-- MESSAGE BOX -->
<div class="msgbox-overlay" id="msgboxOverlay">
  <div class="msgbox-card" id="msgboxCard">
    <div class="msgbox-header">
      <span id="msgbox-header-text">NOTICE</span>
      <div class="msgbox-header-bar"><div class="msgbox-dot"></div></div>
    </div>
    <div class="msgbox-body">
      <div class="msgbox-icon" id="msgbox-icon">ℹ️</div>
      <div class="msgbox-text">
        <div class="msgbox-title" id="msgbox-title">Notice</div>
        <div class="msgbox-msg" id="msgbox-msg"></div>
      </div>
    </div>
    <div class="msgbox-footer" id="msgbox-footer"></div>
  </div>
</div>

<!-- DONE OVERLAY -->
<div class="done-overlay" id="doneOverlay">
  <div class="done-card">
    <span class="icon">✅</span>
    <h2>Receipt Saved!</h2>
    <p>Data has been saved to history.</p>
    <div class="saved-info" id="done-summary">—</div>
    <button onclick="dismissDone()">OK — Start New Receipt</button>
  </div>
</div>

<!-- MAIN UI -->
<div class="container">
  <div class="topbar">
    <span class="brand">⚡ SARTHI SPORTS WEAR — PAYMENT RECEIPT</span>
    <div class="topbar-right">
      <a class="btn-history" href="rhistory.php">📋 HISTORY</a>
      <div class="info-pills">
        <span class="pill">Date: <span id="currentDate">--/--/----</span></span>
        <span class="pill">Time: <span id="currentTime">--:--:--</span></span>
      </div>
    </div>
  </div>

  <div class="shop-header">
    <div>
      <h1>Sarthi Sports Wear</h1>
      <p>Ph: 0762-0425141 | Mob: 9422107750</p>
    </div>
    <div class="receipt-badge">
      RECEIPT NO.
      <span id="billDisplay">—</span>
    </div>
  </div>

  <div class="section-label">▶ Bill Details</div>
  <div class="form-panel">
    <div class="hfield">
      <label>Bill No.</label>
      <div style="display:flex;align-items:center;background:var(--input-bg);border:1px solid var(--border);border-radius:4px;overflow:hidden;">
        <span id="bill_prefix" style="padding:16px 12px 16px 16px;font-family:'Share Tech Mono',monospace;font-size:28px;color:#ffffff;white-space:nowrap;user-select:none;border-right:1px solid var(--border);">INV-2026-</span>
        <input type="number" id="bill_seq" min="1" step="1" placeholder="001"
               style="background:transparent;border:none;color:#ffffff;font-family:'Share Tech Mono',monospace;font-size:28px;padding:16px 16px 16px 12px;outline:none;width:100%;-moz-appearance:textfield;"
               oninput="updateBillNo()">
      </div>
      <input type="hidden" id="bill_no">
    </div>
    <div class="hfield">
      <label>Date</label>
      <input type="date" id="date" onchange="updateBillYear()">
    </div>
    <div class="hfield">
      <label>Customer Name</label>
      <input type="text" id="customer_name" placeholder="Enter customer name">
    </div>
  </div>

  <div class="section-label">▶ Payment</div>
  <div class="pay-panel" id="payPanel">
    <button class="paymode-add-btn" onclick="addPaymodeRow()">+ Add Payment Mode</button>
  </div>

  <div class="section-label">▶ Amount Summary</div>
  <div class="totals-panel">
    <div class="total-row">
      <label><b>Total Amount (₹)</b></label>
      <input type="number" id="net_amount" placeholder="0.00" min="0" step="0.01"
             style="background:var(--input-bg);border:1px solid var(--border);border-radius:4px;color:var(--accent2);font-family:'Share Tech Mono',monospace;font-size:30px;text-align:right;padding:15px 18px;width:260px;outline:none;"
             oninput="updateBalance()">
    </div>
    <div class="total-row">
      <label><b>Amount Paid</b></label>
      <span class="val" id="paid_amt" style="color:var(--accent2);">₹ 0.00</span>
    </div>
    <div class="total-row balance-row">
      <label>Balance Due</label>
      <span class="val" id="balance_amt">₹ 0.00</span>
    </div>
  </div>

  <div class="btn-bar">
    <button class="btn btn-print" onclick="generateReceipt()">🖨 PRINT</button>
    <button class="btn btn-done"  onclick="markDone()">✔ DONE</button>
    <button class="btn btn-clear" onclick="clearForm()">↺ CLEAR</button>
  </div>
</div>

<script>
var pmCounter = 0;
var PAY_MODES = ['Cash','UPI','Card','Cheque','Online','NEFT/RTGS','Other'];
var currentReceiptNo = 0;

function pad(n){ return String(n).length<2?'0'+String(n):String(n); }
function pad3(n){ var s=String(n); while(s.length<3) s='0'+s; return s; }

function getNextReceiptNo(){
    var n=parseInt(localStorage.getItem('ssw_receipt_no')||'0',10)+1;
    localStorage.setItem('ssw_receipt_no',n); return n;
}
function getNextBillNo(){
    var n=parseInt(localStorage.getItem('ssw_bill_no')||'0',10)+1;
    localStorage.setItem('ssw_bill_no',n);
    return n;
}
function saveToHistory(record){
    var hist=JSON.parse(localStorage.getItem('ssw_receipt_history')||'[]');
    hist.unshift(record);
    localStorage.setItem('ssw_receipt_history',JSON.stringify(hist));
}

function tick(){
    var d=new Date();
    document.getElementById('currentDate').textContent=pad(d.getDate())+'/'+pad(d.getMonth()+1)+'/'+d.getFullYear();
    document.getElementById('currentTime').textContent=pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds());
}
setInterval(tick,1000); tick();

function getBillYear(){
    var dateVal=document.getElementById('date').value;
    return dateVal ? dateVal.split('-')[0] : new Date().getFullYear().toString();
}

function updateBillNo(){
    var year=getBillYear();
    var seq=document.getElementById('bill_seq').value.trim();
    var seqNum=parseInt(seq,10);
    var seqStr=isNaN(seqNum)||seqNum<1 ? '' : pad3(seqNum);
    document.getElementById('bill_prefix').textContent='INV-'+year+'-';
    var full=seqStr ? 'INV-'+year+'-'+seqStr : '';
    document.getElementById('bill_no').value=full;
    document.getElementById('billDisplay').textContent=full||'—';
}

function updateBillYear(){
    var year=getBillYear();
    document.getElementById('bill_prefix').textContent='INV-'+year+'-';
    var seq=document.getElementById('bill_seq').value.trim();
    var seqNum=parseInt(seq,10);
    var seqStr=isNaN(seqNum)||seqNum<1 ? '' : pad3(seqNum);
    var full=seqStr ? 'INV-'+year+'-'+seqStr : '';
    document.getElementById('bill_no').value=full;
    document.getElementById('billDisplay').textContent=full||'—';
}

var msgboxTypes = {
    warn:    { icon:'⚠️',  header:'WARNING',  title:'Incomplete Information', btnClass:'msgbox-btn-ok',    btnText:'Got It' },
    error:   { icon:'❌',  header:'ERROR',    title:'Error',                  btnClass:'msgbox-btn-ok',    btnText:'Close' },
    success: { icon:'✅',  header:'SUCCESS',  title:'Success',                btnClass:'msgbox-btn-ok',    btnText:'OK' },
    confirm: { icon:'❓',  header:'CONFIRM',  title:'Confirm Action',         btnClass:'msgbox-btn-confirm', btnText:'Yes, Proceed' }
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
function hideMsgbox(){
    document.getElementById('msgboxOverlay').classList.remove('show');
}
document.getElementById('msgboxOverlay').addEventListener('click', function(e){
    if(e.target === this) hideMsgbox();
});
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') hideMsgbox();
});

function addPaymodeRow(defaultMode){
    defaultMode=defaultMode||'';
    var id=++pmCounter;
    var opts='<option value="">— Select Mode —</option>';
    for(var i=0;i<PAY_MODES.length;i++){
        var m=PAY_MODES[i];
        opts+='<option value="'+m+'"'+(m===defaultMode?' selected':'')+'>'+m+'</option>';
    }
    var div=document.createElement('div');
    div.className='paymode-entry'; div.setAttribute('data-pmid',id);
    div.innerHTML='<select class="paymode-select" onchange="updateBalance()">'+opts+'</select>'+
        '<input type="number" class="paymode-amount" placeholder="0.00" min="0" step="0.01" oninput="updateBalance()">'+
        '<button class="paymode-remove" onclick="removePaymode('+id+')">✕</button>';
    document.getElementById('payPanel').insertBefore(div,document.querySelector('#payPanel .paymode-add-btn'));
    updateBalance();
}
function removePaymode(id){
    var el=document.querySelector('[data-pmid="'+id+'"]');
    if(el) el.remove(); updateBalance();
}
function updateBalance(){
    var net=parseFloat(document.getElementById('net_amount').value)||0, paid=0;
    document.querySelectorAll('#payPanel .paymode-entry').forEach(function(e){
        var mode=e.querySelector('.paymode-select').value;
        var amt=parseFloat(e.querySelector('.paymode-amount').value)||0;
        if(mode) paid+=Math.max(0,amt);
    });
    var bal=Math.max(0,net-paid);
    document.getElementById('paid_amt').textContent='₹ '+paid.toFixed(2);
    document.getElementById('balance_amt').textContent='₹ '+bal.toFixed(2);
}

function collectFormData(callback){
    var bill=document.getElementById('bill_no').value.trim();
    var date=document.getElementById('date').value;
    var name=document.getElementById('customer_name').value.trim();
    var net=parseFloat(document.getElementById('net_amount').value);
    var missing=[];
    if(!bill) missing.push('Bill No.');
    if(!date) missing.push('Date');
    if(!name) missing.push('Customer Name');
    if(isNaN(net)||net<=0) missing.push('Net Amount (must be > 0)');
    if(missing.length){
        showMsg('warn','Please fill in the following required fields:\n• '+missing.join('\n• '),
            {title:'Incomplete Form',header:'VALIDATION ERROR',icon:'📋',okText:'OK, Got It'});
        return;
    }
    var pmParts=[];
    document.querySelectorAll('#payPanel .paymode-entry').forEach(function(e){
        var mode=e.querySelector('.paymode-select').value;
        var amt=parseFloat(e.querySelector('.paymode-amount').value)||0;
        if(mode&&amt>0) pmParts.push({mode:mode,amt:amt});
    });
    if(!pmParts.length){
        showMsg('warn','Please add at least one Payment Mode and enter an amount greater than zero.',
            {title:'No Payment Added',header:'PAYMENT REQUIRED',icon:'💳',okText:'Add Payment'});
        return;
    }
    var months=['January','February','March','April','May','June','July','August','September','October','November','December'];
    var parts=date.split('-');
    var formatted=parts[2]+' '+months[parseInt(parts[1],10)-1]+' '+parts[0];
    var paid=0; pmParts.forEach(function(p){paid+=p.amt;});
    var balance=Math.max(0,net-paid);
    var now=new Date();
    var printTime=pad(now.getDate())+'/'+pad(now.getMonth()+1)+'/'+now.getFullYear()+' '+pad(now.getHours())+':'+pad(now.getMinutes())+':'+pad(now.getSeconds());
    callback({bill:bill,date:date,formatted:formatted,name:name,net:net,pmParts:pmParts,paid:paid,balance:balance,printTime:printTime,now:now});
}

function fillBillCopy(prefix, d){
    var dp=d.date.split('-');
    var dateStr=dp.length===3?dp[2]+'-'+dp[1]+'-'+dp[0]:d.date;
    document.getElementById(prefix+'-date').textContent     = dateStr;
    document.getElementById(prefix+'-customer').textContent = d.name;
    var figEl=document.getElementById(prefix+'-figures'); if(figEl) figEl.textContent='₹ '+d.net.toFixed(2);
    var tbody=document.getElementById(prefix+'-pay-rows');
    tbody.innerHTML='';
    d.pmParts.forEach(function(p,i){
        var tr=document.createElement('tr');
        tr.innerHTML='<td>'+(i+1)+'</td><td>'+p.mode+'</td><td>₹ '+p.amt.toFixed(2)+'</td>';
        tbody.appendChild(tr);
    });
    document.getElementById(prefix+'-total-paid').textContent='₹ '+d.net.toFixed(2);
    var balBar=document.getElementById(prefix+'-bal-bar');
    balBar.style.display='none';
}

function generateReceipt(){
    collectFormData(function(d){
        fillBillCopy('p1',d);
        saveToHistory({
            receiptNo:pad3(currentReceiptNo),billNo:d.bill,
            date:d.date,dateFormatted:d.formatted,customer:d.name,
            payments:d.pmParts,amountPaid:d.paid,netAmount:d.net,balance:d.balance,
            printedAt:d.printTime,savedAt:d.now.toISOString(),savedVia:'print'
        });
        setTimeout(function(){ window.print(); },120);
    });
}

function markDone(){
    collectFormData(function(d){
        saveToHistory({
            receiptNo:pad3(currentReceiptNo),billNo:d.bill,
            date:d.date,dateFormatted:d.formatted,customer:d.name,
            payments:d.pmParts,amountPaid:d.paid,netAmount:d.net,balance:d.balance,
            printedAt:d.printTime,savedAt:d.now.toISOString(),savedVia:'done'
        });
        var modeList=d.pmParts.map(function(p){return p.mode+': ₹'+p.amt.toFixed(2);}).join(' | ');
        document.getElementById('done-summary').textContent=
            d.bill+' | '+d.name+'\n'+modeList+'\nTotal: ₹'+d.net.toFixed(2)+(d.balance>0?' | Due: ₹'+d.balance.toFixed(2):'');
        document.getElementById('doneOverlay').classList.add('show');
    });
}

function dismissDone(){
    document.getElementById('doneOverlay').classList.remove('show');
    clearForm(true);
}

function clearForm(skipConfirm){
    if(!skipConfirm){
        showMsg('confirm','This will erase all current fields. Are you sure you want to start fresh?',
            {title:'Clear All Fields?',header:'CONFIRM CLEAR',icon:'🗑️',okText:'Yes, Clear',cancelText:'Cancel'},
            function(confirmed){ if(confirmed) doClear(); });
        return;
    }
    doClear();
}

function doClear(){
    document.getElementById('customer_name').value='';
    document.getElementById('net_amount').value='';
    currentReceiptNo=getNextReceiptNo();
    var seqN=getNextBillNo();
    var today=new Date();
    document.getElementById('date').value=today.getFullYear()+'-'+pad(today.getMonth()+1)+'-'+pad(today.getDate());
    document.getElementById('bill_seq').value=seqN;
    updateBillNo();
    document.querySelectorAll('#payPanel .paymode-entry').forEach(function(e){e.remove();});
    pmCounter=0; addPaymodeRow('Cash'); updateBalance();
}

(function(){
    currentReceiptNo=getNextReceiptNo();
    var seqN=getNextBillNo();
    var today=new Date();
    document.getElementById('date').value=today.getFullYear()+'-'+pad(today.getMonth()+1)+'-'+pad(today.getDate());
    document.getElementById('bill_seq').value=seqN;
    updateBillNo();
    addPaymodeRow('Cash');
})();
</script>
</body>
</html>