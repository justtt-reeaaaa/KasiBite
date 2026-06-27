<?php
session_start();
require 'config.php';
require_once 'helpers.php';

$token = $_GET['token'] ?? '';
$error = '';
$done  = false;

// Validate token (expires after 1 hour)
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = "This reset link is invalid or has expired.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $newPass = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($newPass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hashed, $reset['email']]);
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$reset['email']]);
        $done = true;
    }
}

$pageTitle = "Reset Password";
include 'header.php';
?>

<style>
.auth-wrap { display:flex; justify-content:center; align-items:center; padding:60px 20px; }
.auth-box {
    background:white; padding:35px; border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1); width:100%; max-width:400px; text-align:center;
}
.auth-box h2 { font-size:22px; margin-bottom:20px; }
</style>

<div class="auth-wrap">
    <div class="auth-box">

        <?php if ($done): ?>
            <div style="font-size:40px;margin-bottom:10px;">✅</div>
            <h2>Password Updated!</h2>
            <p style="color:var(--muted);font-size:13px;margin-bottom:20px;">Your password has been changed successfully.</p>
            <a href="login.php" class="btn">Go to Login</a>

        <?php elseif ($error && !$reset): ?>
            <div style="font-size:40px;margin-bottom:10px;">❌</div>
            <h2>Invalid Link</h2>
            <p style="color:var(--muted);font-size:13px;margin-bottom:20px;"><?= htmlspecialchars($error) ?></p>
            <a href="forgot_password.php" class="btn">Try Again</a>

        <?php else: ?>
            <h2>Set New Password</h2>
            <?php if ($error): ?>
                <div class="flash error" style="margin:0 0 15px;text-align:left;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" style="text-align:left;">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="Min 8 chars, uppercase, lowercase, number" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm" placeholder="Repeat password" required>
                </div>
                <button type="submit" class="btn btn-full">Update Password</button>
            </form>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>
