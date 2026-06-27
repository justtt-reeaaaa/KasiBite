<?php
session_start();
$pageTitle = "Login";
include 'header.php';
?>

<style>
.auth-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 20px;
}
.auth-box {
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 380px;
}
.auth-box h2 { font-size: 22px; margin-bottom: 6px; }
.auth-box .sub { font-size: 13px; color: var(--muted); margin-bottom: 25px; }
.auth-box .sub a { color: var(--primary); font-weight: 600; }
.forgot-link { text-align: right; font-size: 12px; margin-top: -8px; margin-bottom: 16px; }
.forgot-link a { color: var(--primary); }
.demo-box {
    background: var(--beige);
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 12px;
    margin-top: 20px;
    color: #666;
    border: 1px dashed #ccc;
}
.demo-box strong { color: var(--dark); }
</style>

<div class="auth-wrap">
    <div class="auth-box">
        <h2>Welcome Back 👋</h2>
        <p class="sub">Don't have an account? <a href="register.php">Register here</a></p>

        <form action="login_user.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Your password" required>
            </div>
            <div class="forgot-link"><a href="forgot_password.php">Forgot password?</a></div>
            <button type="submit" class="btn btn-full">Login</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
