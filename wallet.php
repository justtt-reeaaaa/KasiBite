<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];

// Add money
if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];

    $sql = "UPDATE users SET wallet_balance = wallet_balance + ? WHERE user_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$amount, $user_id]);
}

// Get balance
$stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE user_id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<h2>My Wallet</h2>

<p>Balance: R<?= $user['wallet_balance'] ?></p>

<form method="POST">
    <input type="number" name="amount" placeholder="Enter amount" required>
    <button type="submit">Add Money</button>
</form>