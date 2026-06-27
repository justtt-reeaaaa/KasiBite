<?php
session_start();
require 'config.php';
require_once 'helpers.php';

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
        $note = trim($_POST['special_instructions'][$cid] ?? '');
        $pdo->prepare("UPDATE cart SET quantity = ?, special_instructions = ? WHERE cart_id = ? AND user_id = ?")
            ->execute([$qty, $note, (int)$cid, $user_id]);
    }
    header("Location: cart.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.cart_id, c.quantity, c.special_instructions, p.product_id, p.name, p.price, p.stock, p.image_url, cat.category_name, cat.image_url AS category_image_url
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    JOIN categories cat ON p.category_id = cat.category_id
    WHERE c.user_id = ? AND p.status='active' AND p.stock > 0
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($items as $item) $total += $item['price'] * $item['quantity'];

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
.note-input{width:100%;min-width:190px;padding:8px;border:1px solid var(--border);border-radius:6px;font-family:Poppins;font-size:12px;}
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
        <thead><tr><th>Item</th><th>Product</th><th>Price</th><th>Qty</th><th>Order Notes</th><th>Subtotal</th><th>Remove</th></tr></thead>
        <tbody>
        <?php foreach($items as $item):
            $img = category_image($item);
            $sub = $item['price'] * $item['quantity'];
        ?>
        <tr>
            <td><img src="<?=$img?>" alt=""></td>
            <td><strong><?=htmlspecialchars($item['name'])?></strong><br><small style="color:var(--muted);"><?=htmlspecialchars($item['category_name'])?></small></td>
            <td>R<?=number_format($item['price'],2)?></td>
            <td><input type="number" name="qty[<?=$item['cart_id']?>]" value="<?=$item['quantity']?>" min="1" max="<?=$item['stock']?>" class="qty-input"></td>
            <td><textarea class="note-input" name="special_instructions[<?=$item['cart_id']?>]" rows="2"><?=htmlspecialchars($item['special_instructions'] ?? '')?></textarea></td>
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
