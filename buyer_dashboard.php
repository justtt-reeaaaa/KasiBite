<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='buyer'){header("Location:login.php");exit;}
$user_id=$_SESSION['user_id'];

$user=$pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$user_id]);
$user=$user->fetch(PDO::FETCH_ASSOC);

$orders=$pdo->prepare("SELECT * FROM orders WHERE buyer_id=? ORDER BY created_at DESC");
$orders->execute([$user_id]);
$orders=$orders->fetchAll(PDO::FETCH_ASSOC);

$cart_count=$pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id=?");
$cart_count->execute([$user_id]);
$cart_count=$cart_count->fetchColumn()||0;

$pageTitle="My Dashboard"; include 'header.php';
?>
<style>
.dash-wrap{max-width:1000px;margin:40px auto;padding:0 20px;}
.dash-header{display:flex;align-items:center;gap:20px;background:white;border-radius:12px;padding:25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:25px;}
.avatar{width:60px;height:60px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;color:white;font-weight:700;}
.dash-header h2{font-size:20px;margin-bottom:4px;}
.dash-header p{font-size:13px;color:var(--muted);}
.stats-row{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:25px;}
.stat-card{background:white;border-radius:10px;padding:20px;flex:1;min-width:140px;box-shadow:0 2px 8px rgba(0,0,0,0.08);text-align:center;}
.stat-card .num{font-size:28px;font-weight:700;color:var(--primary);}
.stat-card .label{font-size:12px;color:var(--muted);margin-top:4px;}
.orders-table{width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
.orders-table th{background:var(--dark);color:white;padding:12px 16px;text-align:left;font-size:13px;}
.orders-table td{padding:12px 16px;border-bottom:1px solid #f0f0f0;font-size:14px;}
.orders-table tr:last-child td{border-bottom:none;}
.status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.status-confirmed{background:#d4edda;color:#155724;}
.status-pending{background:#fff3cd;color:#856404;}
.status-completed{background:#cce5ff;color:#004085;}
.status-cancelled{background:#f8d7da;color:#721c24;}
.quick-links{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:25px;}
</style>
<div class="dash-wrap">
  <div class="dash-header">
    <div class="avatar"><?=strtoupper(substr($user['full_name'],0,1))?></div>
    <div>
      <h2>Welcome, <?=htmlspecialchars(explode(' ',$user['full_name'])[0])?>! 👋</h2>
      <p><?=htmlspecialchars($user['email'])?> · Buyer Account</p>
    </div>
    <a href="browse.php" class="btn" style="margin-left:auto;">Browse Food 🍽️</a>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="num"><?=count($orders)?></div><div class="label">Total Orders</div></div>
    <div class="stat-card"><div class="num"><?=$cart_count?></div><div class="label">Items in Cart</div></div>
    <div class="stat-card"><div class="num">R<?=number_format(array_sum(array_column($orders,'total_amount')),2)?></div><div class="label">Total Spent</div></div>
  </div>

  <div class="quick-links">
    <a href="cart.php" class="btn">🛒 View Cart (<?=$cart_count?>)</a>
    <a href="browse.php" class="btn btn-dark">Browse Food</a>
  </div>

  <h3 style="margin-bottom:15px;">My Orders</h3>
  <?php if(empty($orders)): ?>
  <div style="text-align:center;padding:40px;background:white;border-radius:10px;">
    <div style="font-size:40px;margin-bottom:10px;">🍽️</div>
    <p style="color:var(--muted);">No orders yet. Go explore some kasi food!</p>
    <a href="browse.php" class="btn" style="margin-top:15px;">Browse Food</a>
  </div>
  <?php else: ?>
  <table class="orders-table">
    <thead><tr><th>Order Ref</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($orders as $o): ?>
    <tr>
      <td><strong><?=htmlspecialchars($o['payment_ref'])?></strong></td>
      <td><?=date('d M Y, H:i',strtotime($o['created_at']))?></td>
      <td><strong>R<?=number_format($o['total_amount'],2)?></strong></td>
      <td><span class="status-badge status-<?=$o['status']?>"><?=ucfirst($o['status'])?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
