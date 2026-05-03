<?php
$conn = mysqli_connect("localhost", "root", "", "sarkhi sports1");
if($conn && isset($_POST['mc_no'])){
    $mc = mysqli_real_escape_string($conn, trim($_POST['mc_no']));
    mysqli_query($conn,
        "INSERT INTO mc_bill_status (mc_no, bill_made, made_at)
         VALUES ('$mc', 1, NOW())
         ON DUPLICATE KEY UPDATE bill_made=1, made_at=NOW()");
}
echo 'ok';
?>