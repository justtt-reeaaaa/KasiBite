<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['remove'])) {
    $cid = (int)$_GET['remove'];
    $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?")->execute([$cid, $user_id]);
    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    foreach ($_POST['qty'] as $cid => $qty) {
        $qty = max(1, (int)$qty);
        $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?")
            ->execute([$qty, (int)$cid, $user_id]);
    }
    header("Location: cart.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, cat.category_name
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    JOIN categories cat ON p.category_id = cat.category_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($items as $item) $total += $item['price'] * $item['quantity'];

$catImages = [
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
$defaultImg = 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d';

$pageTitle = "My Cart";
include 'header.php';
?>
<style>
.cart-wrap{max-width:1000px;margin:40px auto;padding:0 20px;}
.cart-wrap h2{font-size:24px;margin-bottom:25px;}
.cart-wrap h2 span{color:var(--primary);}
.cart-table{width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
.cart-table th{background:var(--dark);color:white;padding:12px 16px;text-align:left;font-size:13px;}
.cart-table td{padding:12px 16px;border-bottom:1px solid #f0f0f0;font-size:14px;vertical-align:middle;}
.cart-table tr:last-child td{border-bottom:none;}
.cart-table img{width:60px;height:60px;object-fit:cover;border-radius:6px;}
.qty-input{width:60px;padding:6px;border:1px solid var(--border);border-radius:5px;text-align:center;}
.remove-link{color:var(--danger);font-size:13px;}
.cart-summary{background:white;border-radius:10px;padding:25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);max-width:350px;margin-left:auto;}
.cart-summary h3{font-size:18px;margin-bottom:15px;}
.summary-row{display:flex;justify-content:space-between;font-size:14px;margin-bottom:10px;}
.summary-row.total{font-weight:700;font-size:16px;border-top:1px solid #eee;padding-top:12px;margin-top:12px;}
.summary-row.total span:last-child{color:var(--primary);}
.empty-cart{text-align:center;padding:60px 20px;color:var(--muted);}
.empty-cart .icon{font-size:60px;margin-bottom:15px;}
</style>
<div class="cart-wrap">
    <h2>Your <span>Cart</span> 🛒</h2>
    <?php if(empty($items)): ?>
    <div class="empty-cart">
        <div class="icon">🛒</div>
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
            $img = $catImages[$item['category_name']] ?? $defaultImg;
            $sub = $item['price'] * $item['quantity'];
        ?>
        <tr>
            <td><img src="<?=$img?>" alt=""></td>
            <td><strong><?=htmlspecialchars($item['name'])?></strong><br><small style="color:var(--muted);"><?=htmlspecialchars($item['category_name'])?></small></td>
            <td>R<?=number_format($item['price'],2)?></td>
            <td><input type="number" name="qty[<?=$item['cart_id']?>]" value="<?=$item['quantity']?>" min="1" max="20" class="qty-input"></td>
            <td><strong>R<?=number_format($sub,2)?></strong></td>
            <td><a href="cart.php?remove=<?=$item['cart_id']?>" class="remove-link">✕ Remove</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;margin-top:20px;">
        <button type="submit" name="update_qty" class="btn btn-dark">Update Cart</button>
        <div class="cart-summary">
            <h3>Order Summary</h3>
            <div class="summary-row"><span>Items (<?=count($items)?>)</span><span>R<?=number_format($total,2)?></span></div>
            <div class="summary-row"><span>Delivery</span><span style="color:var(--success);">Free (Collection)</span></div>
            <div class="summary-row total"><span>Total</span><span>R<?=number_format($total,2)?></span></div>
            <a href="checkout.php" class="btn btn-full" style="text-align:center;display:block;margin-top:15px;">Proceed to Checkout →</a>
        </div>
    </div>
    </form>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
