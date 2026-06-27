<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='buyer'){header("Location:login.php");exit;}
$user_id=$_SESSION['user_id'];

$order_id=(int)($_GET['order_id']??0);

// Verify this order belongs to this buyer
$orderCheck=$pdo->prepare("SELECT * FROM orders WHERE order_id=? AND buyer_id=?");
$orderCheck->execute([$order_id,$user_id]);
$order=$orderCheck->fetch(PDO::FETCH_ASSOC);
if(!$order){ header("Location:buyer_dashboard.php"); exit; }

// Handle review submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    foreach($_POST['rating'] as $product_id => $rating){
        $product_id=(int)$product_id;
        $rating=max(1,min(5,(int)$rating));
        $comment=trim($_POST['comment'][$product_id]??'');

        // Prevent duplicate review for same product+order
        $exists=$pdo->prepare("SELECT review_id FROM reviews WHERE product_id=? AND buyer_id=? AND order_id=?");
        $exists->execute([$product_id,$user_id,$order_id]);
        if(!$exists->fetch() && $rating > 0){
            $pdo->prepare("INSERT INTO reviews (product_id,buyer_id,order_id,rating,comment) VALUES (?,?,?,?,?)")
                ->execute([$product_id,$user_id,$order_id,$rating,$comment]);
        }
    }
    $_SESSION['flash']=['type'=>'success','msg'=>'Thanks! Your review has been posted.'];
    header("Location:buyer_dashboard.php");
    exit;
}

// Get items from this order that haven't been reviewed yet
$items=$pdo->prepare("
    SELECT oi.product_id, oi.quantity, p.name,
           (SELECT COUNT(*) FROM reviews r WHERE r.product_id=oi.product_id AND r.buyer_id=? AND r.order_id=oi.order_id) as already_reviewed
    FROM order_items oi JOIN products p ON oi.product_id=p.product_id
    WHERE oi.order_id=?
");
$items->execute([$user_id,$order_id]);
$items=$items->fetchAll(PDO::FETCH_ASSOC);

$pageTitle="Leave a Review"; include 'header.php';
?>
<style>
.review-wrap{max-width:600px;margin:40px auto;padding:0 20px;}
.review-card{background:white;border-radius:10px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:18px;}
.review-card h4{margin-bottom:10px;}
.star-rating{display:flex;gap:6px;font-size:28px;margin-bottom:10px;}
.star-rating label{cursor:pointer;color:#ddd;}
.star-rating input{display:none;}
.star-rating input:checked ~ label,.star-rating label:hover,.star-rating label:hover ~ label{color:#f39c12;}
.star-rating{flex-direction:row-reverse;justify-content:flex-end;}
.already-done{color:var(--success);font-size:13px;font-weight:600;}
</style>
<div class="review-wrap">
  <h2 style="margin-bottom:6px;">Review Your Order</h2>
  <p style="color:var(--muted);font-size:13px;margin-bottom:20px;">Ref: <?=htmlspecialchars($order['payment_ref'])?></p>
  <form method="POST">
  <?php foreach($items as $item): ?>
    <div class="review-card">
      <h4><?=htmlspecialchars($item['name'])?></h4>
      <?php if($item['already_reviewed']): ?>
        <p class="already-done">✓ You already reviewed this item</p>
      <?php else: ?>
        <div class="star-rating">
          <?php for($i=5;$i>=1;$i--): ?>
            <input type="radio" name="rating[<?=$item['product_id']?>]" value="<?=$i?>" id="star<?=$item['product_id']?>_<?=$i?>">
            <label for="star<?=$item['product_id']?>_<?=$i?>">★</label>
          <?php endfor; ?>
        </div>
        <textarea name="comment[<?=$item['product_id']?>]" rows="2" placeholder="How was it? (optional)" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;font-family:Poppins;font-size:13px;"></textarea>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-full">Submit Review(s)</button>
  </form>
</div>
<?php include 'footer.php'; ?>
