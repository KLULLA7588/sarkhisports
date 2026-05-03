<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ownerName = trim($_POST['ownerName']);
    $firmName  = trim($_POST['firmName']);
    $phone     = trim($_POST['phone']);
    $email     = trim($_POST['email']);
    $address   = trim($_POST['address']);
    $gst       = trim($_POST['gst']);

    $data = array(
        'owner_name' => $ownerName,
        'firm_name'  => $firmName,
        'phone'      => $phone,
        'email'      => $email,
        'address'    => $address,
        'gst'        => $gst
    );

    file_put_contents('account_data.json', json_encode($data));

    $_SESSION['owner_name'] = $ownerName;
    $_SESSION['firm_name']  = $firmName;
    $_SESSION['phone']      = $phone;
    $_SESSION['email']      = $email;
    $_SESSION['address']    = $address;
    $_SESSION['gst']        = $gst;

    header('Location: home.php?name=' . urlencode($ownerName));
    exit;
}

$data = array();
if (file_exists('account_data.json')) {
    $json = file_get_contents('account_data.json');
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

$ownerName = '';
$firmName  = '';
$phone     = '';
$email     = '';
$address   = '';
$gst       = '';

if (!empty($_SESSION['owner_name'])) {
    $ownerName = htmlspecialchars($_SESSION['owner_name']);
    $firmName  = htmlspecialchars($_SESSION['firm_name']);
    $phone     = htmlspecialchars($_SESSION['phone']);
    $email     = htmlspecialchars($_SESSION['email']);
    $address   = htmlspecialchars($_SESSION['address']);
    $gst       = htmlspecialchars($_SESSION['gst']);
} elseif (!empty($data)) {
    $ownerName = htmlspecialchars($data['owner_name']);
    $firmName  = htmlspecialchars($data['firm_name']);
    $phone     = htmlspecialchars($data['phone']);
    $email     = htmlspecialchars($data['email']);
    $address   = htmlspecialchars($data['address']);
    $gst       = htmlspecialchars($data['gst']);
}

$hasData = !empty($ownerName) || !empty($firmName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account - Sarthi Sports Wear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #000;
            color: #e4e4e7;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .navbar {
            background: #000;
            border-bottom: 1px solid #27272a;
            padding: 16px 24px;
        }

        .small-box {
            border: 1px solid #3f3f46;
            padding: 11px 24px;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 1px;
            border-radius: 6px;
            color: #a1a1aa;
            background: #111;
        }

        .small-box:hover {
            background: #1f1f23;
            border-color: #52525b;
        }

        .dropdown-menu {
            background: #111;
            border: 1px solid #333;
        }

        .dropdown-item {
            color: #e4e4e7;
            font-size: 1.15rem;
            font-weight: 700;
            padding: 10px 20px;
        }
        .dropdown-item:hover { background: #1f1f23; color: #fff; }

        .center-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 58px);
            padding: 24px;
        }

        .account-card {
            background: #0f0f10;
            border: 1px solid #27272a;
            border-radius: 14px;
            padding: 50px 56px;
            width: 100%;
            max-width: 620px;
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #f4f4f5;
        }

        .card-sub {
            font-size: 1rem;
            color: #52525b;
            letter-spacing: 1px;
            margin-bottom: 28px;
            margin-top: 4px;
        }

        .btn-edit-top {
            background: transparent;
            border: 1px solid #3f3f46;
            color: #a1a1aa;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 9px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
        }

        .btn-edit-top:hover { border-color: #71717a; color: #e4e4e7; }

        .divider {
            border: none;
            border-top: 1px solid #1c1c1e;
            margin-bottom: 28px;
        }

        .info-row {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 24px;
        }

        .info-label {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #52525b;
        }

        .info-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #e4e4e7;
        }

        .info-empty {
            color: #3f3f46;
            font-style: italic;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .field-wrap { margin-bottom: 22px; }

        label {
            display: block;
            font-size: 0.98rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #71717a;
            margin-bottom: 9px;
        }

        .form-control {
            background: #18181b;
            border: 1px solid #3f3f46;
            border-radius: 8px;
            color: #e4e4e7;
            font-size: 1.2rem;
            font-weight: 700;
            padding: 15px 18px;
            width: 100%;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
        }

        .form-control:focus {
            background: #1c1c1e;
            border-color: #71717a;
            box-shadow: 0 0 0 3px rgba(113,113,122,0.1);
            color: #f4f4f5;
            outline: none;
        }

        .form-control::placeholder { color: #3f3f46; }

        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .btn-save {
            flex: 1;
            background: #f4f4f5;
            color: #09090b;
            border: none;
            padding: 16px;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
        }

        .btn-save:hover { background: #e4e4e7; }

        .btn-cancel {
            background: transparent;
            color: #52525b;
            border: 1px solid #27272a;
            padding: 16px 26px;
            font-size: 1.15rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: 0.2s;
        }

        .btn-cancel:hover { color: #a1a1aa; border-color: #52525b; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-black px-4">
    <div class="d-flex gap-3">

        <div class="dropdown">
            <button class="btn small-box dropdown-toggle" data-bs-toggle="dropdown">Manager</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Account</a></li>
                <li><a class="dropdown-item" href="#">By date</a></li>
                <li><a class="dropdown-item" href="#">By month</a></li>
            </ul>
        </div>

        <div class="dropdown">
            <button class="btn small-box dropdown-toggle" data-bs-toggle="dropdown">Master</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="account.php">Account</a></li>
                <li><a class="dropdown-item" href="#">By date</a></li>
                <li><a class="dropdown-item" href="#">By month</a></li>
            </ul>
        </div>

        <div class="dropdown">
            <button class="btn small-box dropdown-toggle" data-bs-toggle="dropdown">Utility</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Sales</a></li>
                <li><a class="dropdown-item" href="#">Monthly progress</a></li>
                <li><a class="dropdown-item" href="#">Sales Register</a></li>
                <li><a class="dropdown-item" href="#">Payment modes</a></li>
            </ul>
        </div>

        <div class="dropdown">
            <button class="btn small-box dropdown-toggle" data-bs-toggle="dropdown">Contact</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Email</a></li>
            </ul>
        </div>

    </div>
</nav>

<div class="center-wrap">
    <div class="account-card">

        <div class="card-header-row">
            <div class="card-title">MASTER ACCOUNT</div>
            <?php if ($hasData) { ?>
                <button class="btn-edit-top" onclick="switchToEdit()">&#9998; Edit</button>
            <?php } ?>
        </div>
        <div class="card-sub">BACK INFORMATION</div>
        <hr class="divider">

        <?php if ($hasData) { ?>
        <!-- VIEW MODE -->
        <div id="viewMode">
            <div class="info-row">
                <span class="info-label">Firm Name</span>
                <span class="info-value <?php echo empty($firmName) ? 'info-empty' : ''; ?>"><?php echo !empty($firmName) ? $firmName : '&mdash;'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Owner Name</span>
                <span class="info-value <?php echo empty($ownerName) ? 'info-empty' : ''; ?>"><?php echo !empty($ownerName) ? $ownerName : '&mdash;'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Contact Number</span>
                <span class="info-value <?php echo empty($phone) ? 'info-empty' : ''; ?>"><?php echo !empty($phone) ? $phone : '&mdash;'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value <?php echo empty($email) ? 'info-empty' : ''; ?>"><?php echo !empty($email) ? $email : '&mdash;'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Address</span>
                <span class="info-value <?php echo empty($address) ? 'info-empty' : ''; ?>"><?php echo !empty($address) ? $address : '&mdash;'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">GST Number</span>
                <span class="info-value <?php echo empty($gst) ? 'info-empty' : ''; ?>"><?php echo !empty($gst) ? $gst : '&mdash;'; ?></span>
            </div>
            <div class="btn-row">
                <a href="home.php" class="btn-cancel">&larr; Back</a>
            </div>
        </div>
        <?php } ?>

        <!-- EDIT / FILL MODE -->
        <div id="editMode" <?php echo $hasData ? 'style="display:none;"' : ''; ?>>
            <form method="POST" action="account.php">
                <div class="field-wrap">
                    <label>Firm Name</label>
                    <input type="text" name="firmName" class="form-control" value="<?php echo $firmName; ?>" placeholder="e.g. Sarthi Sports Wear">
                </div>
                <div class="field-wrap">
                    <label>Owner Name</label>
                    <input type="text" name="ownerName" class="form-control" value="<?php echo $ownerName; ?>" placeholder="Full name">
                </div>
                <div class="field-wrap">
                    <label>Contact Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?php echo $phone; ?>" placeholder="+91 XXXXX XXXXX">
                </div>
                <div class="field-wrap">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" placeholder="you@example.com">
                </div>
                <div class="field-wrap">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?php echo $address; ?>" placeholder="Shop, Street, City">
                </div>
                <div class="field-wrap">
                    <label>GST Number</label>
                    <input type="text" name="gst" class="form-control" value="<?php echo $gst; ?>" placeholder="22AAAAA0000A1Z5">
                </div>
                <div class="btn-row">
                    <?php if ($hasData) { ?>
                        <button type="button" class="btn-cancel" onclick="switchToView()">Cancel</button>
                    <?php } else { ?>
                        <a href="home.php" class="btn-cancel">Cancel</a>
                    <?php } ?>
                    <button type="submit" class="btn-save">Save &amp; Return</button>
                </div>
            </form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function switchToEdit() {
    document.getElementById('viewMode').style.display = 'none';
    document.getElementById('editMode').style.display = 'block';
}
function switchToView() {
    document.getElementById('editMode').style.display = 'none';
    document.getElementById('viewMode').style.display = 'block';
}
</script>
</body>
</html>