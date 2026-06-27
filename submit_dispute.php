<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='buyer'){header("Location:login.php");exit;}
$user_id=$_SESSION['user_id'];
$order_id=(int)$_POST['order_id'];
$reason=trim($_POST['reason']);

if(!$order_id||!$reason){
    $_SESSION['flash']=['type'=>'error','msg'=>'Please select an order and describe the issue.'];
    header("Location:buyer_dashboard.php"); exit;
}

// Verify order belongs to buyer
$order=$pdo->prepare("SELECT o.*,oi.product_id FROM orders o JOIN order_items oi ON o.order_id=oi.order_id WHERE o.order_id=? AND o.buyer_id=? LIMIT 1");
$order->execute([$order_id,$user_id]);
$order=$order->fetch(PDO::FETCH_ASSOC);
if(!$order){
    $_SESSION['flash']=['type'=>'error','msg'=>'Order not found.'];
    header("Location:buyer_dashboard.php"); exit;
}

// Get seller_id from first product in order
$seller=$pdo->prepare("SELECT p.seller_id FROM order_items oi JOIN products p ON oi.product_id=p.product_id WHERE oi.order_id=? LIMIT 1");
$seller->execute([$order_id]);
$seller=$seller->fetch(PDO::FETCH_ASSOC);
$seller_id=$seller['seller_id']??0;

// Check no duplicate dispute for same order
$dup=$pdo->prepare("SELECT dispute_id FROM disputes WHERE order_id=? AND buyer_id=?");
$dup->execute([$order_id,$user_id]);
if($dup->fetch()){
    $_SESSION['flash']=['type'=>'info','msg'=>'You have already filed a dispute for this order.'];
    header("Location:buyer_dashboard.php"); exit;
}

$pdo->prepare("INSERT INTO disputes (order_id,buyer_id,seller_id,reason) VALUES (?,?,?,?)")
    ->execute([$order_id,$user_id,$seller_id,$reason]);

$_SESSION['flash']=['type'=>'success','msg'=>'Dispute submitted. The admin will review and follow up.'];
header("Location:buyer_dashboard.php"); exit;
