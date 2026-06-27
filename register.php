<?php
$pageTitle = "Register As";
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
    max-width: 400px;
    text-align: center;
}
.auth-box h2 {
    font-size: 22px;
    margin-bottom: 6px;
    color: var(--dark);
}
.auth-box .sub {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 25px;
}
.auth-box .sub a { color: var(--primary); font-weight: 600; }
.role-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.role-options a {
    display: block;
    background: var(--primary);
    color: #fff;
    text-decoration: none;
    padding: 12px;
    border-radius: 6px;
    font-weight: bold;
    transition: background 0.3s ease, transform 0.2s ease;
}
.role-options a:hover {
    background: #46657a;
    transform: translateY(-2px);
}
</style>

<div class="auth-wrap">
    <div class="auth-box">
        <h2>Register As</h2>
        <p class="sub">Choose your account type below</p>
        <div class="role-options">
            <a href="register_form.php?role=buyer">Buyer</a>
            <a href="register_form.php?role=seller">Seller</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
