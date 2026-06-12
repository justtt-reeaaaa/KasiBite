<?php
session_start();
require 'config.php';

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a token and store it (simulation — no actual email sent)
        $token = bin2hex(random_bytes(20));
        // Clear old tokens for this email
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
        $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?,?)")->execute([$email, $token]);
        $sent = true;
        // In a real system you'd email the link. We just show the reset link for demo.
        $resetLink = "reset_password.php?token=$token";
    } else {
        $error = "No account found with that email.";
    }
}

$pageTitle = "Forgot Password";
include 'header.php';
?>

<style>
.auth-wrap { display:flex; justify-content:center; align-items:center; padding:60px 20px; }
.auth-box {
    background:white; padding:35px; border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1); width:100%; max-width:400px;
}
.auth-box h2 { font-size:22px; margin-bottom:8px; }
.auth-box .sub { font-size:13px; color:var(--muted); margin-bottom:25px; }
.auth-box .sub a { color:var(--primary); font-weight:600; }
.demo-note {
    background:#fff3cd; border:1px solid #ffc107; border-radius:6px;
    padding:10px 14px; font-size:12px; color:#856404; margin-top:15px;
}
.success-box {
    background:#d4edda; border:1px solid #c3e6cb; border-radius:8px;
    padding:20px; text-align:center; color:#155724;
}
.success-box .link-demo {
    display:inline-block; margin-top:12px; padding:10px 20px;
    background:var(--primary); color:white; border-radius:5px; font-size:13px;
}
</style>

<div class="auth-wrap">
    <div class="auth-box">

        <?php if ($sent): ?>
            <div class="success-box">
                <div style="font-size:32px;margin-bottom:10px;">📧</div>
                <strong>Reset Link Generated!</strong><br>
                <small style="color:#666;">In a live system, an email would be sent. For this demo, use the link below:</small>
                <a href="<?= $resetLink ?>" class="link-demo">Reset My Password →</a>
            </div>
            <p style="text-align:center;margin-top:15px;font-size:13px;"><a href="login.php" style="color:var(--primary);">← Back to Login</a></p>

        <?php else: ?>
            <h2>Forgot Password?</h2>
            <p class="sub">Enter your email and we'll send a reset link. <a href="login.php">Back to login</a></p>

            <?php if ($error): ?>
                <div class="flash error" style="margin:0 0 15px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <button type="submit" class="btn btn-full">Send Reset Link</button>
            </form>

            <div class="demo-note">
             <strong>Demo note:</strong> No actual email is sent. The reset link will appear on screen after submitting.
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>
