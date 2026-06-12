<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    die("Access denied");
}

$seller_id  = $_SESSION['user_id'];
$product_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT available FROM products WHERE product_id = ? AND seller_id = ?");
$stmt->execute([$product_id, $seller_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $new = $row['available'] ? 0 : 1;
    $upd = $pdo->prepare("UPDATE products SET available = ? WHERE product_id = ? AND seller_id = ?");
    $upd->execute([$new, $product_id, $seller_id]);
}

header("Location: seller.php");
exit;