<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "sarkhi sports1");

/* ══════════════════════════════════════════════
   HANDLE AJAX — mark MC as billed in database
   ══════════════════════════════════════════════ */
if(isset($_POST['action']) && $_POST['action'] === 'mark_billed' && isset($_POST['mc_no'])){
    $mc = mysqli_real_escape_string($conn, trim($_POST['mc_no']));
    if($mc !== ''){
        mysqli_query($conn,
            "INSERT INTO mc_bill_status (mc_no, bill_made, made_at)
             VALUES ('$mc', 1, NOW())
             ON DUPLICATE KEY UPDATE bill_made=1, made_at=NOW()"
        );
    }
    echo 'ok';
    exit;
}

/* ══════════════════════════════════════════════
   OWNER NAME
   ══════════════════════════════════════════════ */
$data = [];
if(file_exists('account_data.json')){
    $data = json_decode(file_get_contents('account_data.json'), true);
    if(!is_array($data)) $data = [];
}
if(!empty($_GET['name'])) $_SESSION['owner_name'] = trim($_GET['name']);
$ownerName = '';
if(!empty($_SESSION['owner_name']))      $ownerName = htmlspecialchars($_SESSION['owner_name']);
elseif(!empty($data['owner_name']))      $ownerName = htmlspecialchars($data['owner_name']);

/* ══════════════════════════════════════════════
   GET TODAY'S UNBILLED MCs
   ══════════════════════════════════════════════ */
$unbilled = [];
if($conn){
    $ub = mysqli_query($conn,
        "SELECT mc_no, order_date, party_no
         FROM master_copy_log
         WHERE DATE(created_at) = CURDATE()
         ORDER BY id DESC"
    );
    if($ub && mysqli_num_rows($ub) > 0){
        $billed = [];
        $br = mysqli_query($conn, "SELECT mc_no FROM mc_bill_status WHERE bill_made = 1");
        if($br){
            while($row = mysqli_fetch_assoc($br)){
                $billed[] = $row['mc_no'];
            }
        }
        while($r = mysqli_fetch_assoc($ub)){
            if(!in_array($r['mc_no'], $billed)){
                $unbilled[] = $r;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home - Firm Software</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{height:100vh;background:#000;font-family:'Inter',sans-serif;color:#f4f4f5;display:flex;flex-direction:column;}
.navbar{border-bottom:1px solid #222;flex-shrink:0;}
.small-box{border:1px solid #3f3f46;padding:8px 18px;font-size:.9rem;letter-spacing:1px;border-radius:6px;background:#111;color:#e4e4e7;transition:.3s;}
.small-box:hover{background:#1f1f23;border-color:#52525b;}
.dropdown-menu{background:#111;border:1px solid #333;}
.dropdown-item{color:#e4e4e7;}
.dropdown-item:hover{background:#1f1f23;color:#fff;}
.greeting-wrap{flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;gap:12px;}
.greeting-hello{font-size:1rem;letter-spacing:4px;text-transform:uppercase;color:#52525b;animation:fadeUp .6s ease-out .1s both;}
.greeting-name{font-size:3.2rem;font-weight:700;letter-spacing:3px;color:#f4f4f5;animation:fadeUp .7s ease-out .3s both;}
.greeting-line{width:40px;height:2px;background:#3f3f46;border-radius:2px;animation:fadeUp .7s ease-out .5s both;}
.no-name{font-size:.85rem;color:#3f3f46;letter-spacing:1px;}
.no-name a{color:#71717a;text-decoration:underline;}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.circle-btn{width:42px;height:42px;border-radius:50%;border:1px solid #3f3f46;background:#111;color:#e4e4e7;display:flex;align-items:center;justify-content:center;font-size:18px;text-decoration:none;transition:.3s;}
.circle-btn:hover{background:#1f1f23;border-color:#52525b;}
#mc-alert-box{position:fixed;bottom:18px;left:18px;z-index:99999;width:300px;background:#0a0a0a;border:2px solid #ff4400;border-radius:12px;box-shadow:0 0 30px rgba(255,68,0,.5);font-family:'Inter',sans-serif;overflow:hidden;animation:mcSlideUp .4s ease-out;}
@keyframes mcSlideUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.mc-head{background:#1a0500;border-bottom:2px solid #ff4400;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;}
.mc-head-title{display:flex;align-items:center;gap:8px;}
.mc-head-title span{color:#ff4400;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;}
.mc-blink{width:10px;height:10px;border-radius:50%;background:#ff4400;animation:blink .7s infinite;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.mc-head button{background:transparent;border:none;color:#555;font-size:18px;cursor:pointer;}
.mc-head button:hover{color:#ff4400;}
#mc-body{padding:8px 12px;overflow-y:auto;max-height:220px;}
.mc-item{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #1a1a1a;}
.mc-item:last-child{border-bottom:none;}
.mc-tag{background:#1a0500;border:1px solid #cc3300;color:#ff6633;font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;font-family:monospace;letter-spacing:1px;}
.mc-dt{font-size:10px;color:#555;margin-top:3px;}
.mc-nobill{font-size:10px;color:#ff4400;font-weight:700;}
#mc-foot{display:flex;}
#mc-mute,#mc-dismiss{flex:1;background:#0d0200;border:none;border-top:1px solid #1a1a1a;color:#555;font-size:11px;padding:9px;cursor:pointer;font-family:'Inter',sans-serif;transition:color .2s;}
#mc-mute{border-right:1px solid #1a1a1a;}
#mc-mute:hover,#mc-dismiss:hover{color:#ff4400;}
#mc-sound-bar{background:#100300;border-top:1px solid #2a0a00;padding:7px 12px;display:flex;align-items:center;justify-content:space-between;}
#mc-sound-bar span{font-size:11px;color:#663300;}
#mc-sound-toggle{background:#ff4400;border:none;border-radius:20px;color:#fff;font-size:11px;font-weight:700;padding:4px 14px;cursor:pointer;transition:background .2s;font-family:'Inter',sans-serif;}
#mc-sound-toggle.muted{background:#333;color:#666;}
#mc-sound-toggle:hover:not(.muted){background:#ff6600;}
#mc-dot{position:fixed;bottom:18px;left:18px;z-index:99998;width:18px;height:18px;border-radius:50%;background:#ff4400;border:2px solid #000;cursor:pointer;display:none;animation:dotPulse 1s infinite;}
@keyframes dotPulse{0%,100%{box-shadow:0 0 6px #ff4400;transform:scale(1)}50%{box-shadow:0 0 20px #ff4400;transform:scale(1.15)}}
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-black px-4">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div class="d-flex gap-3">
            <div class="dropdown">
                <button class="btn small-box dropdown-toggle" data-bs-toggle="dropdown">Master</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="account.php">Account</a></li>
                    <li><a class="dropdown-item" href="master.php">Master copy</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn small-box dropdown-toggle" data-bs-toggle="dropdown">Utility</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="sales.php">Sales</a></li>
                    <li><a class="dropdown-item" href="salesregister.php">Sales Register</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn small-box dropdown-toggle" data-bs-toggle="dropdown">Copies</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="garagecopy.php">Garage/gate copy</a></li>
                    <li><a class="dropdown-item" href="ghistory.php">garage/gate history</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn small-box dropdown-toggle" data-bs-toggle="dropdown">Receipt</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="receipt.php">Receipt</a></li>
                    <li><a class="dropdown-item" href="rhistory.php">Receipt history</a></li>
                </ul>
            </div>
        </div>
        <a href="account.php" class="circle-btn">
            <i class="bi bi-person-lines-fill"></i>
        </a>
    </div>
</nav>

<div class="greeting-wrap">
    <?php if(!empty($ownerName)): ?>
        <div class="greeting-hello">Hello,</div>
        <div class="greeting-name"><?php echo $ownerName; ?></div>
        <div class="greeting-line"></div>
    <?php else: ?>
        <div class="no-name">No name set — go to <a href="account.php">Master &rarr; Account</a></div>
    <?php endif; ?>
</div>

<?php if(!empty($unbilled)): ?>
<div id="mc-alert-box">
    <div class="mc-head">
        <div class="mc-head-title">
            <div class="mc-blink"></div>
            <span>&#9888; <?php echo count($unbilled); ?> MC &mdash; No Bill Today</span>
        </div>
        <button onclick="mcMinimize()" title="Minimize">&#8722;</button>
    </div>
    <div id="mc-body">
        <?php foreach($unbilled as $u):
            $mc_safe   = htmlspecialchars($u['mc_no']);
            $mc_enc    = urlencode($u['mc_no']);
            $party_enc = urlencode(isset($u['party_no']) ? $u['party_no'] : '');
            $dt        = !empty($u['order_date']) ? date('d M Y', strtotime($u['order_date'])) : 'Today';
        ?>
        <div class="mc-item" id="mcitem-<?php echo $mc_safe; ?>">
            <div>
                <div class="mc-tag"><?php echo $mc_safe; ?></div>
                <div class="mc-dt"><?php echo $dt; ?></div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;">
                <span class="mc-nobill" id="mcst-<?php echo $mc_safe; ?>">No Bill!</span>
                <a href="sales.php?from_mc=<?php echo $mc_enc; ?>&party=<?php echo $party_enc; ?>"
                   id="mcbtn-<?php echo $mc_safe; ?>"
                   style="background:#ff4400;border-radius:3px;color:#fff;font-size:10px;font-weight:700;padding:3px 9px;text-decoration:none;white-space:nowrap;">
                   &#128203; Make Bill
                </a>
                <button id="mcrm-<?php echo $mc_safe; ?>"
                   onclick="mcRemove('<?php echo $mc_safe; ?>')"
                   style="display:none;background:#1a3322;border:1px solid #00cc77;border-radius:3px;color:#00cc77;font-size:10px;font-weight:700;padding:3px 9px;cursor:pointer;white-space:nowrap;">
                   &#10005; Bill Made &mdash; Remove
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div id="mc-sound-bar">
        <span id="mc-sound-status">&#128266; Alarm active &mdash; waiting for interaction</span>
        <button id="mc-sound-toggle" onclick="mcToggleMute()">Mute</button>
    </div>
    <div id="mc-foot">
        <button id="mc-mute" onclick="mcToggleMute()">&#128263; Mute Sound</button>
        <button id="mc-dismiss" onclick="mcDismiss()">&#10005; Dismiss All</button>
    </div>
</div>
<div id="mc-dot" onclick="mcRestore()" title="Unbilled MCs"></div>

<script>
var ALARM_SRC='data:audio/wav;base64,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';
var _muted=false,_dismissed=false,_started=false,_audio=null;
function buildAudio(){_audio=new Audio(ALARM_SRC);_audio.loop=true;_audio.volume=1.0;}
function startAlarm(){if(_started||_muted||_dismissed)return;_started=true;buildAudio();_audio.play().then(function(){updateStatus('&#128266; Alarm sounding');}).catch(function(){_started=false;updateStatus('&#128561; Click anywhere to enable sound');});}
function stopAlarm(){if(_audio){_audio.pause();_audio.currentTime=0;}}
function updateStatus(m){var e=document.getElementById('mc-sound-status');if(e)e.innerHTML=m;}
function mcToggleMute(){
    _muted=!_muted;
    var b1=document.getElementById('mc-sound-toggle');
    var b2=document.getElementById('mc-mute');
    if(_muted){stopAlarm();if(b1){b1.textContent='Unmute';b1.classList.add('muted');}if(b2)b2.textContent='&#128266; Unmute';updateStatus('&#128263; Sound muted');}
    else{_started=false;startAlarm();if(b1){b1.textContent='Mute';b1.classList.remove('muted');}if(b2)b2.innerHTML='&#128263; Mute Sound';}
}
function mcMinimize(){document.getElementById('mc-alert-box').style.display='none';document.getElementById('mc-dot').style.display='block';}
function mcRestore(){document.getElementById('mc-alert-box').style.display='block';document.getElementById('mc-dot').style.display='none';}
function mcDismiss(){_dismissed=true;_muted=true;stopAlarm();var b=document.getElementById('mc-alert-box');var d=document.getElementById('mc-dot');if(b)b.style.display='none';if(d)d.style.display='none';}
window.addEventListener('beforeunload',function(){stopAlarm();});
window.addEventListener('pagehide',function(){stopAlarm();});
document.addEventListener('visibilitychange',function(){if(!_audio)return;if(document.hidden){_audio.pause();}else if(!_muted&&!_dismissed){_audio.play().catch(function(){});}});

/* ══════════════════════════════════════════
   MC BILL SYSTEM — PERMANENT DB STORAGE
   ══════════════════════════════════════════ */

/* Send POST to home.php to save bill_made=1 in DB */
function mcSaveDB(mcNo){
    var fd=new FormData();
    fd.append('action','mark_billed');
    fd.append('mc_no',mcNo);
    fetch('home.php',{method:'POST',body:fd})
    .catch(function(err){
        /* fallback if fetch fails */
        var x=new XMLHttpRequest();
        x.open('POST','home.php',false);
        x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        x.send('action=mark_billed&mc_no='+encodeURIComponent(mcNo));
    });
}

/* Called when user clicks "Bill Made — Remove" button */
function mcRemove(mcNo){
    mcSaveDB(mcNo);   /* SAVE TO DATABASE — so it never shows after reload */
    try{localStorage.removeItem('mc_bill_made_'+mcNo);}catch(e){}
    var el=document.getElementById('mcitem-'+mcNo);
    if(el){
        el.style.transition='opacity .35s';
        el.style.opacity='0';
        setTimeout(function(){
            el.remove();
            var bd=document.getElementById('mc-body');
            if(bd&&bd.querySelectorAll('.mc-item').length===0) mcDismiss();
        },370);
    }
}

/* Show green tick state on an MC item */
function mcShowBillMade(mcNo){
    var el =document.getElementById('mcitem-'+mcNo);
    var st =document.getElementById('mcst-'+mcNo);
    var btn=document.getElementById('mcbtn-'+mcNo);
    var rm =document.getElementById('mcrm-'+mcNo);
    if(!el)return;
    if(st){st.textContent='\u2714 Bill Made';st.style.color='#00cc77';}
    if(btn)btn.style.display='none';
    if(rm) rm.style.display='block';
    el.style.background='rgba(0,204,119,0.08)';
    el.style.borderLeft='3px solid #00cc77';
}

/* Poll localStorage every 2s for same-session bill feedback from sales.php */
function mcCheckLS(){
    document.querySelectorAll('[id^="mcitem-"]').forEach(function(el){
        var mc=el.id.replace('mcitem-','');
        try{if(localStorage.getItem('mc_bill_made_'+mc)==='1'){mcShowBillMade(mc);}}catch(e){}
    });
}
mcCheckLS();
setInterval(mcCheckLS,2000);

/* Start alarm on first user interaction */
function _onInteract(){
    startAlarm();
    ['click','keydown','mousedown','touchstart','scroll','mousemove'].forEach(function(ev){
        document.removeEventListener(ev,_onInteract);
    });
}
['click','keydown','mousedown','touchstart','scroll','mousemove'].forEach(function(ev){
    document.addEventListener(ev,_onInteract,{once:true,passive:true});
});
setTimeout(function(){if(!_started)startAlarm();},400);
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>