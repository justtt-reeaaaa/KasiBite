<?php
session_start();
require 'config.php';
require_once 'helpers.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer'){
    header("Location:login.php");
    exit;
}

$buyer_id = (int)$_SESSION['user_id'];
$order_id = (int)($_GET['order_id'] ?? $_POST['order_id'] ?? 0);

$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE order_id=? AND buyer_id=?");
$orderStmt->execute([$order_id,$buyer_id]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);
if(!$order){
    $_SESSION['flash']=['type'=>'error','msg'=>'Order not found.'];
    header("Location:buyer_dashboard.php");
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_review'])){
    $product_id=(int)$_POST['product_id'];
    $rating=max(1,min(5,(int)$_POST['rating']));
    $comment=trim($_POST['comment'] ?? '');
    $seller_id=(int)$_POST['seller_id'];
    $pdo->prepare("
        INSERT INTO reviews (buyer_id,seller_id,product_id,order_id,rating,comment)
        VALUES (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)
    ")->execute([$buyer_id,$seller_id,$product_id,$order_id,$rating,$comment]);
    $_SESSION['flash']=['type'=>'success','msg'=>'Review saved.'];
    header("Location:order_details.php?order_id=$order_id");
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_dispute'])){
    $seller_id=(int)$_POST['seller_id'];
    $product_id=(int)$_POST['product_id'];
    $reason=trim($_POST['reason'] ?? '');
    $description=trim($_POST['description'] ?? '');
    if($reason && $description){
        $pdo->prepare("INSERT INTO disputes (order_id,buyer_id,seller_id,product_id,reason,description,status) VALUES (?,?,?,?,?,?,'open')")
            ->execute([$order_id,$buyer_id,$seller_id,$product_id,$reason,$description]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Dispute submitted to admin.'];
    }
    header("Location:order_details.php?order_id=$order_id");
    exit;
}

$itemsStmt=$pdo->prepare("
    SELECT oi.*, p.name, p.seller_id, u.full_name AS seller_name
    FROM order_items oi
    JOIN products p ON oi.product_id=p.product_id
    JOIN users u ON p.seller_id=u.user_id
    WHERE oi.order_id=?
");
$itemsStmt->execute([$order_id]);
$items=$itemsStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle="Order Details";
include 'header.php';
?>
<style>
.wrap{max-width:950px;margin:40px auto;padding:0 20px;}
.box{background:white;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px;}
.item{border-top:1px solid #eee;padding-top:18px;margin-top:18px;}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
@media(max-width:760px){.grid{grid-template-columns:1fr;}}
</style>
<div class="wrap">
  <div class="box">
    <h2>Order <?=e($order['payment_ref'])?></h2>
    <p style="color:var(--muted);font-size:13px;">Status: <?=e(ucfirst($order['status']))?> | Total: R<?=number_format($order['total_amount'],2)?></p>
    <?php foreach($items as $item): ?>
    <div class="item">
      <h3><?=e($item['name'])?></h3>
      <p style="font-size:13px;color:var(--muted);">Seller: <?=e($item['seller_name'])?> | Qty: <?=$item['quantity']?> | Paid: R<?=number_format($item['price']*$item['quantity'],2)?></p>
      <?php if($item['special_instructions']): ?><p style="font-size:13px;">Your note: <?=e($item['special_instructions'])?></p><?php endif; ?>
      <div class="grid">
        <form method="POST">
          <h4>Write / Update Review</h4>
          <input type="hidden" name="order_id" value="<?=$order_id?>">
          <input type="hidden" name="product_id" value="<?=$item['product_id']?>">
          <input type="hidden" name="seller_id" value="<?=$item['seller_id']?>">
          <div class="form-group"><label>Rating</label><select name="rating"><option value="5">5 - Excellent</option><option value="4">4 - Good</option><option value="3">3 - Okay</option><option value="2">2 - Poor</option><option value="1">1 - Bad</option></select></div>
          <div class="form-group"><label>Review</label><textarea name="comment" rows="3" placeholder="Tell future buyers about this item"></textarea></div>
          <button class="btn" name="add_review" type="submit">Save Review</button>
        </form>
        <form method="POST">
          <h4>Report Dispute</h4>
          <input type="hidden" name="order_id" value="<?=$order_id?>">
          <input type="hidden" name="product_id" value="<?=$item['product_id']?>">
          <input type="hidden" name="seller_id" value="<?=$item['seller_id']?>">
          <div class="form-group"><label>Reason</label><input name="reason" placeholder="e.g. Wrong item, not delivered, quality issue" required></div>
          <div class="form-group"><label>Description</label><textarea name="description" rows="3" placeholder="Explain exactly what happened" required></textarea></div>
          <button class="btn btn-danger" name="add_dispute" type="submit">Submit Dispute</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include 'footer.php'; ?>
