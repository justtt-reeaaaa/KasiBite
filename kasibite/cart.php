<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='buyer'){header("Location:login.php");exit;}
$user_id=$_SESSION['user_id'];

if(isset($_GET['remove'])){
    $pdo->prepare("DELETE FROM cart WHERE cart_id=? AND user_id=?")->execute([(int)$_GET['remove'],$user_id]);
    header("Location:cart.php"); exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['update'])){
    foreach($_POST['qty'] as $cid=>$qty){
        $pdo->prepare("UPDATE cart SET quantity=? WHERE cart_id=? AND user_id=?")
            ->execute([max(1,(int)$qty),(int)$cid,$user_id]);
    }
    foreach($_POST['instructions'] as $cid=>$inst){
        $pdo->prepare("UPDATE cart SET special_instructions=? WHERE cart_id=? AND user_id=?")
            ->execute([trim($inst),(int)$cid,$user_id]);
    }
    $_SESSION['flash']=['type'=>'success','msg'=>'Cart updated!'];
    header("Location:cart.php"); exit;
}

$stmt=$pdo->prepare("
    SELECT c.cart_id,c.quantity,c.special_instructions,p.product_id,p.name,p.price,p.image_path,cat.category_name
    FROM cart c
    JOIN products p ON c.product_id=p.product_id
    JOIN categories cat ON p.category_id=cat.category_id
    WHERE c.user_id=?
");
$stmt->execute([$user_id]);
$items=$stmt->fetchAll(PDO::FETCH_ASSOC);
$total=0; foreach($items as $i) $total+=$i['price']*$i['quantity'];

$catImages=[
    'Amagwinya'=>'https://iol-prod.appspot.com/image/0a866824961ebe8a09ad8875ebac339f70fdbe4e=w700',
    'Pap & Stew'=>'https://th.bing.com/th/id/R.79bd82699014d8e920c92c40ef5436ff?rik=raN2D5pdTbIujQ&pid=ImgRaw&r=0',
    'Grilled Meat'=>'https://www.suburbansimplicity.com/wp-content/uploads/2021/06/How-to-keep-meat-moist-on-the-grill.jpg',
    'Breakfast'=>'https://tse3.mm.bing.net/th/id/OIP.cV8IfMXFn2uqn3YOR4ne0gHaHa?r=0&pid=ImgDetMain',
    'Beverages'=>'https://th.bing.com/th/id/R.4327e9e3d10634e6af86b81314bacd0d?rik=9VdoAm28zgLKsQ&pid=ImgRaw&r=0',
    'Vetkoek'=>'https://as2.ftcdn.net/v2/jpg/02/23/81/47/1000_F_223814741_k90kjLiXIFbLXpUtlnlOWyioTUoMt1vU.jpg',
    'Umngqusho'=>'https://www.thesouthafrican.com/wp-content/uploads/2020/07/087f68fa-umgquasho-samp-and-beans-with-lamb-and-chakalaka.jpg',
    'Snacks & Sides'=>'https://healy-group.com/wp-content/uploads/AdobeStock_953274304-min-1920x1076.jpeg',
    'Smiley & Walkie Talkies'=>'https://www.houseofyork.co.za/images/cmsimages/big/news-288-2588-walkie-talkie.jpeg',
    'Bunny Chow'=>'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d',
];
$default='https://images.unsplash.com/photo-1604908176997-125f25cc6f3d';
$pageTitle="My Cart"; include 'header.php';
?>
<style>
.cart-wrap{max-width:1020px;margin:35px auto;padding:0 20px;}
.cart-wrap h2{font-size:22px;margin-bottom:20px;}
.cart-wrap h2 span{color:var(--primary);}
.cart-table{width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
.cart-table th{background:var(--dark);color:white;padding:11px 14px;text-align:left;font-size:12px;}
.cart-table td{padding:12px 14px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:top;}
.cart-table tr:last-child td{border-bottom:none;}
.cart-table img{width:60px;height:60px;object-fit:cover;border-radius:6px;}
.qty-input{width:56px;padding:5px;border:1px solid var(--border);border-radius:5px;text-align:center;}
.inst-box{width:100%;padding:7px 10px;border:1px solid #ffc107;border-radius:5px;font-size:12px;font-family:Poppins,sans-serif;background:#fffdf0;resize:vertical;margin-top:6px;}
.inst-label{font-size:11px;font-weight:600;color:#856404;margin-top:8px;display:block;}
.remove-link{color:var(--danger);font-size:12px;}
.cart-summary{background:white;border-radius:10px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,0.08);max-width:340px;margin-left:auto;}
.cart-summary h3{font-size:17px;margin-bottom:15px;}
.sum-row{display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px;}
.sum-row.total{font-weight:700;font-size:16px;border-top:1px solid #eee;padding-top:10px;margin-top:10px;}
.sum-row.total span:last-child{color:var(--primary);}
.empty-cart{text-align:center;padding:60px 20px;color:var(--muted);}
</style>
<div class="cart-wrap">
  <h2>Your <span>Cart</span> 🛒</h2>
  <?php if(empty($items)): ?>
  <div class="empty-cart">
    <div style="font-size:60px;margin-bottom:15px;">🛒</div>
    <h3>Your cart is empty</h3>
    <p style="margin-bottom:20px;">Browse some kasi food and add items!</p>
    <a href="browse.php" class="btn">Browse Food</a>
  </div>
  <?php else: ?>
  <form method="POST">
  <table class="cart-table">
    <thead><tr><th>Item</th><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th>Remove</th></tr></thead>
    <tbody>
    <?php foreach($items as $item):
      $img=($item['image_path']&&file_exists($item['image_path']))?$item['image_path']:($catImages[$item['category_name']]??$default);
      $sub=$item['price']*$item['quantity'];
    ?>
    <tr>
      <td><img src="<?=$img?>" alt=""></td>
      <td>
        <strong><?=htmlspecialchars($item['name'])?></strong><br>
        <small style="color:var(--muted);"><?=htmlspecialchars($item['category_name'])?></small>
        <span class="inst-label">📝 Special Instructions (optional)</span>
        <textarea name="instructions[<?=$item['cart_id']?>]" class="inst-box" rows="2"
          placeholder="e.g. No onions, extra spicy, less salt..."><?=htmlspecialchars($item['special_instructions']??'')?></textarea>
      </td>
      <td>R<?=number_format($item['price'],2)?></td>
      <td><input type="number" name="qty[<?=$item['cart_id']?>]" value="<?=$item['quantity']?>" min="1" max="50" class="qty-input"></td>
      <td><strong>R<?=number_format($sub,2)?></strong></td>
      <td><a href="cart.php?remove=<?=$item['cart_id']?>" class="remove-link">✕</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;margin-top:20px;">
    <button type="submit" name="update" class="btn btn-dark">Update Cart & Save Instructions</button>
    <div class="cart-summary">
      <h3>Order Summary</h3>
      <div class="sum-row"><span>Items (<?=count($items)?>)</span><span>R<?=number_format($total,2)?></span></div>
      <div class="sum-row"><span>Delivery</span><span style="color:var(--success);">Free (Collection)</span></div>
      <div class="sum-row total"><span>Total</span><span>R<?=number_format($total,2)?></span></div>
      <a href="checkout.php" class="btn btn-full" style="text-align:center;display:block;margin-top:15px;">Proceed to Checkout →</a>
    </div>
  </div>
  </form>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
