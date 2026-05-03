<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Firm Software</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
        height: 100vh;
        margin: 0;
        font-family: 'Inter', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #18181b; /* Graphite dark */
        color: #f4f4f5;     /* Soft white */
    }


/* password css */
#authScreen {
    width: 100%;
    height: 100vh;
    background: #000;
    display: flex;
    justify-content: center;
    align-items: center;
}

.auth-box {
    background: #111;
    padding: 40px;
    border-radius: 10px;
    width: 320px;
    text-align: center;
    border: 1px solid #333;
}
    /* Firm Name */
    #firmName {
        font-size: 3rem;
        font-weight: 700;
        letter-spacing: 2px;
        opacity: 0;
        animation: fadeIn 1.5s forwards;
        color: #f4f4f5;
    }

    @keyframes fadeIn {
        to { opacity: 1; }
    }

    /* Loader */
    #loader {
        display: none;
    }

    .dot-loader {
        width: 80px;
        height: 80px;
        position: relative;
        animation: rotate 1.2s linear infinite;
    }

    .dot {
        width: 14px;
        height: 14px;
        background: #a1a1aa; /* Neutral professional gray */
        border-radius: 50%;
        position: absolute;
    }

    .dot:nth-child(1) { top: 0; left: 50%; transform: translateX(-50%); }
    .dot:nth-child(2) { right: 0; top: 50%; transform: translateY(-50%); }
    .dot:nth-child(3) { bottom: 0; left: 50%; transform: translateX(-50%); }
    .dot:nth-child(4) { left: 0; top: 50%; transform: translateY(-50%); }

    @keyframes rotate {
        100% { transform: rotate(360deg); }
    }

    /* Book Order Box */
    #bookBox {
        display: none;
        background: #0f0f10; /* Deep neutral gray */
        border: 1px solid #3f3f46; /* Corporate border */
        padding: 70px 100px;
        border-radius: 16px;
        font-size: 2.8rem;
        font-weight: 600;
        letter-spacing: 2px;
        color: #e4e4e7;
        box-shadow: 0 20px 50px rgba(0,0,0,0.7);
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Hide main screen initially */
#mainScreen {
    display: none;
    width: 100%;
    height: 100vh;
    background: #000;
}

/* Small navbar boxes */
.small-box {
    border: 1px solid #3f3f46;
    padding: 8px 18px;
    font-size: 0.9rem;
    letter-spacing: 1px;
    border-radius: 6px;
}



#authScreen {
    width: 100%;
    height: 100vh;
    background: #000;
    display: flex;
    justify-content: center;
    align-items: center;
}

.auth-box {
    background: #111;
    padding: 40px;
    border-radius: 10px;
    width: 320px;
    text-align: center;
    border: 1px solid #333;
}

.small-box {
    border: 1px solid #3f3f46;
    padding: 8px 18px;
    font-size: 0.9rem;
    border-radius: 6px;
}



</style>

</head>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Firm Software</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body {
    height: 100vh;
    margin: 0;
    font-family: 'Inter', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #18181b;
    color: #f4f4f5;
}

/* AUTH SCREEN */
#authScreen {
    width: 100%;
    height: 100vh;
    background: #000;
    display: flex;
    justify-content: center;
    align-items: center;
}

.auth-box {
    background: #111;
    padding: 40px;
    border-radius: 10px;
    width: 320px;
    text-align: center;
    border: 1px solid #333;
}

/* Firm Name */
#firmName {
    font-size: 3rem;
    font-weight: 700;
    letter-spacing: 2px;
    opacity: 0;
    animation: fadeIn 1.5s forwards;
    display: none;
}

@keyframes fadeIn {
    to { opacity: 1; }
}

/* Loader */
#loader {
    display: none;
}

.dot-loader {
    width: 80px;
    height: 80px;
    position: relative;
    animation: rotate 1.2s linear infinite;
}

.dot {
    width: 14px;
    height: 14px;
    background: #a1a1aa;
    border-radius: 50%;
    position: absolute;
}

.dot:nth-child(1) { top: 0; left: 50%; transform: translateX(-50%); }
.dot:nth-child(2) { right: 0; top: 50%; transform: translateY(-50%); }
.dot:nth-child(3) { bottom: 0; left: 50%; transform: translateX(-50%); }
.dot:nth-child(4) { left: 0; top: 50%; transform: translateY(-50%); }

@keyframes rotate {
    100% { transform: rotate(360deg); }
}
</style>
</head>

<body>

<!-- AUTH SCREEN -->
<div id="authScreen">
    <div class="auth-box">
        <h3 id="authTitle">Enter Password</h3>
        <div style="position:relative;" class="mb-3">
    <input type="password" id="passwordInput" class="form-control pe-5" placeholder="Password">

    <i id="eyeIcon" class="bi bi-eye"
       onclick="togglePassword()"
       style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#71717a; font-size:18px;">
    </i>
</div>
        <button class="btn btn-dark w-100" onclick="handleAuth()">Continue</button>
        <p id="errorText" class="text-danger mt-3" style="display:none;">Wrong Password</p>
    </div>
</div>

<!-- Firm Name -->
<div id="firmName">SARTHI SPORTS WEAR.</div>

<!-- Loader -->
<div id="loader">
    <div class="dot-loader">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
</div>

<script>
let savedPassword = localStorage.getItem("softwarePassword");

window.onload = function () {
    if (!savedPassword) {
        document.getElementById("authTitle").innerText = "Create Password";
    } else {
        document.getElementById("authTitle").innerText = "Enter Password";
    }
};

function handleAuth() {
    let input = document.getElementById("passwordInput").value;

    if (!savedPassword) {
        localStorage.setItem("softwarePassword", input);
        alert("Password Created Successfully!");
        location.reload();
    } else {
        if (input === savedPassword) {
            document.getElementById("authScreen").style.display = "none";
            startSoftware();
        } else {
            document.getElementById("errorText").style.display = "block";
        }
    }
}

function startSoftware() {
    document.getElementById("firmName").style.display = "block";

    setTimeout(() => {
        document.getElementById("firmName").style.display = "none";
        document.getElementById("loader").style.display = "block";
    }, 2000);

    setTimeout(() => {
        // 🔥 Redirect to home page after loader
        window.location.href = "home.php";
    }, 3500);
}
function togglePassword() {
    const input = document.getElementById("passwordInput");
    const icon = document.getElementById("eyeIcon");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}
document.getElementById("passwordInput").addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault(); // prevents form reload if any
        handleAuth(); // runs same function as button
    }
});
</script>

</body>


</html>