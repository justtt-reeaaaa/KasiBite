<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='buyer'){header("Location:login.php");exit;}
$user_id=$_SESSION['user_id'];

$stmt=$pdo->prepare("
    SELECT c.quantity,c.special_instructions,p.product_id,p.price,p.stock
    FROM cart c JOIN products p ON c.product_id=p.product_id
    WHERE c.user_id=?
");
$stmt->execute([$user_id]);
$items=$stmt->fetchAll(PDO::FETCH_ASSOC);
if(empty($items)){header("Location:cart.php");exit;}

$total=(float)$_POST['total'];
$ref='KB-'.strtoupper(substr(md5(time().$user_id),0,8));

// Create order
$pdo->prepare("INSERT INTO orders (buyer_id,total_amount,status,payment_ref) VALUES (?,?,'confirmed',?)")
    ->execute([$user_id,$total,$ref]);
$order_id=$pdo->lastInsertId();

// Insert order items + decrement stock
foreach($items as $i){
    $pdo->prepare("INSERT INTO order_items (order_id,product_id,quantity,price,special_instructions) VALUES (?,?,?,?,?)")
        ->execute([$order_id,$i['product_id'],$i['quantity'],$i['price'],$i['special_instructions']]);
    // Decrement stock; deactivate if reaches 0
    $newStock=max(0,$i['stock']-$i['quantity']);
    if($newStock===0){
        $pdo->prepare("UPDATE products SET stock=0,status='inactive' WHERE product_id=?")->execute([$i['product_id']]);
    } else {
        $pdo->prepare("UPDATE products SET stock=? WHERE product_id=?")->execute([$newStock,$i['product_id']]);
    }
}

// Clear cart
$pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$user_id]);

$_SESSION['last_order_id']=$order_id;
$_SESSION['last_order_ref']=$ref;
header("Location:order_confirm.php"); exit;
