<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='buyer'){header("Location:login.php");exit;}
$user_id=$_SESSION['user_id'];

$user=$pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$user_id]); $user=$user->fetch(PDO::FETCH_ASSOC);

$orders=$pdo->prepare("SELECT * FROM orders WHERE buyer_id=? ORDER BY created_at DESC");
$orders->execute([$user_id]); $orders=$orders->fetchAll(PDO::FETCH_ASSOC);

$cart_count=$pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?");
$cart_count->execute([$user_id]); $cart_count=$cart_count->fetchColumn();

// Get order items for review
$order_items_q=$pdo->prepare("
    SELECT oi.item_id,oi.order_id,oi.product_id,p.name as product_name,o.created_at,
           (SELECT review_id FROM reviews r WHERE r.product_id=oi.product_id AND r.buyer_id=? AND r.order_id=oi.order_id) as reviewed
    FROM order_items oi
    JOIN orders o ON oi.order_id=o.order_id
    JOIN products p ON oi.product_id=p.product_id
    WHERE o.buyer_id=?
    ORDER BY o.created_at DESC
");
$order_items_q->execute([$user_id,$user_id]);
$order_items=$order_items_q->fetchAll(PDO::FETCH_ASSOC);

// Get disputes filed by this buyer
$disputes=$pdo->prepare("
    SELECT d.*,o.payment_ref,u.full_name as seller_name
    FROM disputes d
    JOIN orders o ON d.order_id=o.order_id
    JOIN users u ON d.seller_id=u.user_id
    WHERE d.buyer_id=?
    ORDER BY d.created_at DESC
");
$disputes->execute([$user_id]); $disputes=$disputes->fetchAll(PDO::FETCH_ASSOC);

$pageTitle="My Dashboard"; include 'header.php';
?>
<style>
.dash-wrap{max-width:1000px;margin:35px auto;padding:0 20px;}
.dash-header{display:flex;align-items:center;gap:20px;background:white;border-radius:12px;padding:22px 25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px;flex-wrap:wrap;}
.avatar{width:56px;height:56px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;color:white;font-weight:700;}
.stats-row{display:flex;gap:15px;flex-wrap:wrap;margin-bottom:20px;}
.stat-card{background:white;border-radius:10px;padding:18px;flex:1;min-width:130px;box-shadow:0 2px 8px rgba(0,0,0,0.08);text-align:center;}
.stat-card .num{font-size:26px;font-weight:700;color:var(--primary);}
.stat-card .lbl{font-size:12px;color:var(--muted);}
.box{background:white;border-radius:10px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px;}
.box h3{font-size:15px;margin-bottom:16px;border-bottom:2px solid var(--primary);padding-bottom:8px;}
table{width:100%;border-collapse:collapse;}
th{background:var(--dark);color:white;padding:10px 14px;text-align:left;font-size:12px;}
td{padding:10px 14px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-confirmed{background:#d4edda;color:#155724;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-completed{background:#cce5ff;color:#004085;}
.badge-cancelled{background:#f8d7da;color:#721c24;}
.badge-open{background:#f8d7da;color:#721c24;}
.badge-reviewing{background:#fff3cd;color:#856404;}
.badge-resolved{background:#d4edda;color:#155724;}
/* Stars */
.stars{color:#ccc;font-size:20px;cursor:pointer;}
.stars span{transition:color 0.1s;}
.stars span.lit,.stars span:hover,.stars span.hover{color:#f1c40f;}
/* Review form */
.review-form{background:#fffdf0;border:1px solid #ffc107;border-radius:8px;padding:15px;margin-top:8px;}
.review-form textarea{width:100%;padding:8px;border:1px solid #ddd;border-radius:5px;font-family:Poppins,sans-serif;font-size:13px;resize:vertical;}
/* Dispute form */
.dispute-form{background:#fff5f5;border:1px solid #e74c3c;border-radius:8px;padding:15px;margin-top:8px;}
.dispute-form textarea{width:100%;padding:8px;border:1px solid #ddd;border-radius:5px;font-family:Poppins,sans-serif;font-size:13px;resize:vertical;}
</style>
<div class="dash-wrap">
  <div class="dash-header">
    <div class="avatar"><?=strtoupper(substr($user['full_name'],0,1))?></div>
    <div>
      <div style="font-size:18px;font-weight:700;">Welcome, <?=htmlspecialchars(explode(' ',$user['full_name'])[0])?>! 👋</div>
      <div style="font-size:13px;color:var(--muted);"><?=htmlspecialchars($user['email'])?> · Buyer Account</div>
    </div>
    <a href="browse.php" class="btn" style="margin-left:auto;">Browse Food 🍽️</a>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="num"><?=count($orders)?></div><div class="lbl">Orders</div></div>
    <div class="stat-card"><div class="num"><?=$cart_count?></div><div class="lbl">In Cart</div></div>
    <div class="stat-card"><div class="num">R<?=number_format(array_sum(array_column($orders,'total_amount')),2)?></div><div class="lbl">Spent</div></div>
  </div>

  <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="cart.php" class="btn">🛒 My Cart (<?=$cart_count?>)</a>
    <a href="browse.php" class="btn btn-dark">Browse Food</a>
  </div>

  <!-- ORDER HISTORY -->
  <div class="box">
    <h3>📋 My Orders</h3>
    <?php if(empty($orders)): ?>
    <div style="text-align:center;padding:30px;color:var(--muted);">
      <p>No orders yet. <a href="browse.php" style="color:var(--primary);">Browse food</a> to get started.</p>
    </div>
    <?php else: ?>
    <table>
      <thead><tr><th>Reference</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach($orders as $o): ?>
      <tr>
        <td><strong><?=htmlspecialchars($o['payment_ref'])?></strong></td>
        <td><?=date('d M Y, H:i',strtotime($o['created_at']))?></td>
        <td><strong>R<?=number_format($o['total_amount'],2)?></strong></td>
        <td><span class="badge badge-<?=$o['status']?>"><?=ucfirst($o['status'])?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- REVIEWS -->
  <div class="box">
    <h3>⭐ Leave a Review</h3>
    <?php
    $unreviewed=array_filter($order_items,fn($i)=>!$i['reviewed']);
    if(empty($unreviewed)): ?>
    <p style="color:var(--muted);font-size:14px;">You have reviewed all your orders, or have no orders yet.</p>
    <?php else: ?>
    <p style="font-size:13px;color:var(--muted);margin-bottom:15px;">You can review each item you've ordered. Your review will appear on the product page for future buyers.</p>
    <?php foreach($unreviewed as $item): ?>
    <div style="border:1px solid #eee;border-radius:8px;padding:14px;margin-bottom:12px;">
      <strong><?=htmlspecialchars($item['product_name'])?></strong>
      <span style="font-size:12px;color:var(--muted);margin-left:10px;">Order <?=htmlspecialchars($item['order_id'])?> · <?=date('d M Y',strtotime($item['created_at']))?></span>
      <div class="review-form">
        <form action="submit_review.php" method="POST">
          <input type="hidden" name="product_id" value="<?=$item['product_id']?>">
          <input type="hidden" name="order_id" value="<?=$item['order_id']?>">
          <div style="margin-bottom:10px;">
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px;">Rating:</label>
            <div class="stars" id="stars-<?=$item['item_id']?>">
              <?php for($s=1;$s<=5;$s++): ?>
              <span data-val="<?=$s?>" onclick="setRating(<?=$item['item_id']?>,<?=$s?>)">★</span>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="rating-<?=$item['item_id']?>" value="0">
          </div>
          <div style="margin-bottom:10px;">
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px;">Your Review:</label>
            <textarea name="comment" rows="3" placeholder="Share your experience with this food item..." required></textarea>
          </div>
          <button type="submit" class="btn btn-sm">Submit Review</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- DISPUTES -->
  <div class="box">
    <h3>⚠️ Report a Dispute</h3>
    <p style="font-size:13px;color:var(--muted);margin-bottom:15px;">If you have an issue with an order, report it here. The admin will review and follow up.</p>
    <?php if(!empty($orders)): ?>
    <div class="dispute-form">
      <form action="submit_dispute.php" method="POST">
        <div class="form-group">
          <label style="font-size:13px;font-weight:600;">Select Order</label>
          <select name="order_id" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:5px;font-family:Poppins,sans-serif;font-size:13px;">
            <option value="">-- Select an Order --</option>
            <?php foreach($orders as $o): ?>
            <option value="<?=$o['order_id']?>"><?=htmlspecialchars($o['payment_ref'])?> — R<?=number_format($o['total_amount'],2)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-top:10px;">
          <label style="font-size:13px;font-weight:600;">Describe the Issue</label>
          <textarea name="reason" rows="3" placeholder="Explain what went wrong with your order..." required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:5px;font-family:Poppins,sans-serif;font-size:13px;resize:vertical;"></textarea>
        </div>
        <button type="submit" class="btn btn-danger btn-sm" style="margin-top:8px;">Submit Dispute</button>
      </form>
    </div>
    <?php else: ?>
    <p style="color:var(--muted);font-size:13px;">You have no orders to dispute yet.</p>
    <?php endif; ?>

    <?php if(!empty($disputes)): ?>
    <h4 style="margin-top:20px;margin-bottom:10px;font-size:14px;">Your Disputes</h4>
    <table>
      <thead><tr><th>Order</th><th>Seller</th><th>Reason</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach($disputes as $d): ?>
      <tr>
        <td><?=htmlspecialchars($d['payment_ref'])?></td>
        <td><?=htmlspecialchars($d['seller_name'])?></td>
        <td style="max-width:200px;"><?=htmlspecialchars(substr($d['reason'],0,60)).(strlen($d['reason'])>60?'...':'')?></td>
        <td><span class="badge badge-<?=$d['status']?>"><?=ucfirst($d['status'])?></span></td>
        <td><?=date('d M Y',strtotime($d['created_at']))?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
function setRating(itemId, val){
  document.getElementById('rating-'+itemId).value=val;
  const stars=document.querySelectorAll('#stars-'+itemId+' span');
  stars.forEach((s,i)=>{s.classList.toggle('lit',i<val);});
}
// Hover effect
document.querySelectorAll('.stars').forEach(container=>{
  const stars=container.querySelectorAll('span');
  stars.forEach((s,i)=>{
    s.addEventListener('mouseenter',()=>stars.forEach((x,j)=>x.classList.toggle('hover',j<=i)));
    s.addEventListener('mouseleave',()=>stars.forEach(x=>x.classList.remove('hover')));
  });
});
</script>
<?php include 'footer.php'; ?>
