<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||!isset($_SESSION['last_order_id'])){header("Location:index.php");exit;}
$order_id=$_SESSION['last_order_id'];
$ref=$_SESSION['last_order_ref'];
unset($_SESSION['last_order_id'],$_SESSION['last_order_ref']);

$stmt=$pdo->prepare("SELECT oi.quantity,oi.price,oi.notes,p.name FROM order_items oi JOIN products p ON oi.product_id=p.product_id WHERE oi.order_id=?");
$stmt->execute([$order_id]);
$items=$stmt->fetchAll(PDO::FETCH_ASSOC);
$total=0; foreach($items as $i) $total+=$i['price']*$i['quantity'];

$pageTitle="Order Confirmed"; include 'header.php';
?>
<style>
.confirm-wrap{max-width:600px;margin:60px auto;padding:0 20px;text-align:center;}
.confirm-icon{font-size:64px;margin-bottom:15px;}
.confirm-box{background:white;border-radius:12px;padding:35px;box-shadow:0 4px 15px rgba(0,0,0,0.1);}
.confirm-box h2{font-size:24px;color:var(--success);margin-bottom:8px;}
.ref-code{background:var(--beige);border-radius:8px;padding:14px;font-size:18px;font-weight:700;letter-spacing:2px;color:var(--primary);margin:20px 0;}
.item-row{text-align:left;font-size:14px;padding:8px 0;border-bottom:1px solid #f0f0f0;}
.item-row .row-top{display:flex;justify-content:space-between;}
.item-note{font-size:12px;color:#856404;background:#fff8e1;padding:4px 8px;border-radius:4px;margin-top:4px;display:inline-block;}
.total-row{display:flex;justify-content:space-between;font-weight:700;font-size:16px;padding-top:12px;margin-top:4px;}
.total-row span:last-child{color:var(--primary);}
</style>
<div class="confirm-wrap">
  <div class="confirm-box">
    <div class="confirm-icon">✅</div>
    <h2>Order Confirmed!</h2>
    <p style="color:var(--muted);font-size:14px;">Your order has been placed successfully. Show this reference to your vendor.</p>
    <div class="ref-code"><?=$ref?></div>
    <h4 style="text-align:left;margin-bottom:10px;font-size:14px;">Order Items:</h4>
    <?php foreach($items as $i): ?>
    <div class="item-row">
      <div class="row-top"><span><?=htmlspecialchars($i['name'])?> x<?=$i['quantity']?></span><span>R<?=number_format($i['price']*$i['quantity'],2)?></span></div>
      <?php if(!empty($i['notes'])): ?><div class="item-note">📝 <?=htmlspecialchars($i['notes'])?></div><?php endif; ?>
    </div>
    <?php endforeach; ?>
    <div class="total-row"><span>Total Paid</span><span>R<?=number_format($total,2)?></span></div>
    <div style="margin-top:25px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="buyer_dashboard.php" class="btn">View My Orders</a>
      <a href="browse.php" class="btn btn-dark">Order More Food</a>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
