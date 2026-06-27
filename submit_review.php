<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='buyer'){
    header("Location:login.php"); exit;
}
$user_id=$_SESSION['user_id'];
$product_id=(int)$_POST['product_id'];
$order_id=(int)$_POST['order_id'];
$rating=(int)$_POST['rating'];
$comment=trim($_POST['comment']);

if($rating<1||$rating>5||!$comment){
    $_SESSION['flash']=['type'=>'error','msg'=>'Please provide a rating and comment.'];
    header("Location:buyer_dashboard.php"); exit;
}

// Verify buyer actually ordered this product
$check=$pdo->prepare("
    SELECT oi.item_id FROM order_items oi
    JOIN orders o ON oi.order_id=o.order_id
    WHERE o.buyer_id=? AND oi.product_id=? AND oi.order_id=?
");
$check->execute([$user_id,$product_id,$order_id]);
if(!$check->fetch()){
    $_SESSION['flash']=['type'=>'error','msg'=>'You can only review products you have ordered.'];
    header("Location:buyer_dashboard.php"); exit;
}

// Check not already reviewed
$exists=$pdo->prepare("SELECT review_id FROM reviews WHERE product_id=? AND buyer_id=? AND order_id=?");
$exists->execute([$product_id,$user_id,$order_id]);
if($exists->fetch()){
    $_SESSION['flash']=['type'=>'info','msg'=>'You have already reviewed this item.'];
    header("Location:buyer_dashboard.php"); exit;
}

$pdo->prepare("INSERT INTO reviews (product_id,buyer_id,order_id,rating,comment) VALUES (?,?,?,?,?)")
    ->execute([$product_id,$user_id,$order_id,$rating,$comment]);

$_SESSION['flash']=['type'=>'success','msg'=>'Review submitted! Thank you.'];
header("Location:buyer_dashboard.php"); exit;
