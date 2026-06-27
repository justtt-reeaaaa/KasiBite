<?php
session_start();
require 'config.php';
require_once 'helpers.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer'){
    header("Location:login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$pay_method = htmlspecialchars($_POST['payment_method'] ?? 'card');
$delivery_notes = trim($_POST['delivery_notes'] ?? '');

$stmt = $pdo->prepare("
    SELECT c.cart_id, c.quantity, c.special_instructions,
           p.product_id, p.seller_id, p.price, p.stock, p.status
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(empty($items)){
    header("Location:cart.php");
    exit;
}

$total = 0;
foreach($items as $i){
    if($i['status'] !== 'active' || (int)$i['stock'] < (int)$i['quantity']){
        $_SESSION['flash'] = ['type'=>'error','msg'=>'One of your cart items is no longer available. Please update your cart.'];
        header("Location:cart.php");
        exit;
    }
    if((int)$i['seller_id'] === $user_id){
        $_SESSION['flash'] = ['type'=>'error','msg'=>'You cannot buy your own listing.'];
        header("Location:cart.php");
        exit;
    }
    $total += (float)$i['price'] * (int)$i['quantity'];
}

$ref = 'KB-' . strtoupper(substr(md5(time().$user_id),0,8));

try {
    $pdo->beginTransaction();

    $pdo->prepare("
        INSERT INTO orders (buyer_id,total_amount,status,payment_method,payment_status,payment_ref,delivery_notes)
        VALUES (?,?,'confirmed',?,'paid',?,?)
    ")->execute([$user_id,$total,$pay_method,$ref,$delivery_notes]);
    $order_id = $pdo->lastInsertId();

    foreach($items as $i){
        $line_total = (float)$i['price'] * (int)$i['quantity'];
        $pdo->prepare("
            INSERT INTO order_items (order_id,product_id,quantity,price,special_instructions)
            VALUES (?,?,?,?,?)
        ")->execute([$order_id,$i['product_id'],$i['quantity'],$i['price'],$i['special_instructions']]);

        $seller_amount = seller_payout_amount($line_total);
        $platform_fee = platform_fee_amount($line_total);
        $pdo->prepare("
            INSERT INTO seller_payouts (order_id,seller_id,product_id,gross_amount,platform_fee,seller_amount,status)
            VALUES (?,?,?,?,?,?,'pending')
        ")->execute([$order_id,$i['seller_id'],$i['product_id'],$line_total,$platform_fee,$seller_amount]);

        $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE user_id = ?")
            ->execute([$seller_amount,$i['seller_id']]);

        $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?")
            ->execute([$i['quantity'],$i['product_id']]);
        $pdo->prepare("UPDATE products SET status='inactive' WHERE product_id = ? AND stock <= 0")
            ->execute([$i['product_id']]);
    }

    $pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$user_id]);
    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Order could not be completed. Please try again.'];
    header("Location:checkout.php");
    exit;
}

$_SESSION['last_order_id'] = $order_id;
$_SESSION['last_order_ref'] = $ref;
header("Location:order_confirm.php");
exit;
