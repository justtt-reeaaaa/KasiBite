<?php
session_start();
$email = $_SESSION['verify_email'] ?? '';
$token = $_SESSION['verify_token'] ?? '';
if (!$email || !$token) {
    header("Location: login.php");
    exit;
}
$pageTitle = "Verify Email";
include 'header.php';
?>
<style>
.auth-wrap{display:flex;justify-content:center;align-items:center;padding:60px 20px;}
.auth-box{background:white;padding:35px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.1);width:100%;max-width:460px;text-align:center;}
.auth-box h2{font-size:22px;margin-bottom:8px;}
.auth-box p{font-size:13px;color:var(--muted);margin-bottom:18px;}
.demo-note{background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:12px 14px;font-size:12px;color:#856404;margin:16px 0;text-align:left;}
</style>
<div class="auth-wrap">
  <div class="auth-box">
    <h2>Email Verification</h2>
     <p>Verify email <?=htmlspecialchars($email)?>. 
    <a class="btn btn-full" href="verify_email.php?token=<?=htmlspecialchars($token)?>">Verify My Email</a>
  </div>
</div>
<?php include 'footer.php'; ?>
