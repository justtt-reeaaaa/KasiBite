<?php
session_start();
require_once 'helpers.php';
$role = $_GET['role'] ?? null;

if (!$role || !in_array($role, ['buyer','seller'])) {
    header("Location: register.php");
    exit;
}
$_SESSION['reg_role'] = $role;
$pageTitle = "Register as " . ucfirst($role);
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
    max-width: 520px;
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
.role-badge {
    display: inline-block;
    background: var(--primary);
    color: white;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    margin-bottom: 20px;
}
<?php if ($role === 'seller'): ?>
.seller-note {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    padding: 10px 14px;
    font-size: 12px;
    color: #856404;
    margin-bottom: 18px;
}
<?php endif; ?>
</style>

<div class="auth-wrap">
    <div class="auth-box">
        <h2>Create Account</h2>
        <p class="sub">Already have one? <a href="login.php">Log in here</a></p>
        <div class="role-badge"><?= ucfirst($role) ?> Account</div>

        <?php if ($role === 'seller'): ?>
        <div class="seller-note">
            ⏳ <strong>Seller accounts require admin approval.</strong> You'll be notified once your account is reviewed.
        </div>
        <?php endif; ?>

        <form action="register_user.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. Thandi Mokoena" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min 8 chars, uppercase, lowercase, number" required minlength="8">
            </div>
            <?php if ($role === 'seller'): ?>
            <div class="form-group">
                <label>Business / Stall Name</label>
                <input type="text" name="business_name" placeholder="e.g. Thandi's Kota Kitchen" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="e.g. 071 234 5678" required>
            </div>
            <div class="form-group">
                <label>Trading Location</label>
                <input type="text" name="location" placeholder="e.g. Soweto Taxi Rank, Stand 12" required>
            </div>
            <div class="form-group">
                <label>ID / Business Reference</label>
                <input type="text" name="id_number" placeholder="ID number, permit number, or business reference" required>
            </div>
            <div class="form-group">
                <label>What do you sell?</label>
                <textarea name="verification_details" rows="4" required></textarea>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-full">Register as <?= ucfirst($role) ?></button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
