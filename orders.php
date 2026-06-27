<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT o.order_id, o.total_amount, o.status, o.payment_method, o.created_at,
           COUNT(oi.item_id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.buyer_id = ?
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Orders - KasiBite</title>
<style>
:root { --primary: #e67e22; --dark: #1e1e1e; --light: #f5f5f5; }

body { margin: 0; font-family: Poppins, sans-serif; background: var(--light); }

.container { display: flex; }

.sidebar {
    width: 250px;
    background: var(--dark);
    color: white;
    min-height: 100vh;
    padding: 20px;
    box-sizing: border-box;
}

.sidebar h2 { color: var(--primary); }
.sidebar a { display: block; color: white; text-decoration: none; margin: 15px 0; }
.sidebar a:hover { color: var(--primary); }

.main { flex: 1; padding: 30px; }

.main h2 {
    border-bottom: 3px solid var(--primary);
    padding-bottom: 8px;
    display: inline-block;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    margin-top: 20px;
}

th {
    background: var(--primary);
    color: white;
    padding: 12px;
    text-align: left;
    font-size: 14px;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.status-badge {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.confirmed { background: #d4edda; color: #155724; }
.pending   { background: #fff3cd; color: #856404; }
.cancelled { background: #f8d7da; color: #721c24; }
.completed { background: #cce5ff; color: #004085; }

.empty { color: #888; padding: 40px 0; }
</style>
</head>
<body>

<div class="container">
<div class="sidebar">
    <h2>KasiBite</h2>
    <a href="browse.php">🍽 Browse Food</a>
    <a href="cart.php">🛒 My Cart</a>
    <a href="orders.php"><strong>📦 Order History</strong></a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h2>📦 My Orders</h2>

    <?php if (empty($orders)): ?>
        <p class="empty">You haven't placed any orders yet. <a href="browse.php" style="color:var(--primary)">Browse food →</a></p>
    <?php else: ?>
    <table>
        <tr>
            <th>Order #</th>
            <th>Date</th>
            <th>Items</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
        </tr>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td>#<?= str_pad($o['order_id'], 5, '0', STR_PAD_LEFT) ?></td>
            <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
            <td><?= $o['item_count'] ?> item(s)</td>
            <td>R<?= number_format($o['total_amount'], 2) ?></td>
            <td><?= htmlspecialchars(ucfirst($o['payment_method'])) ?></td>
            <td>
                <span class="status-badge <?= $o['status'] ?>">
                    <?= ucfirst($o['status']) ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
</div>
</body>
</html>
