<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "msg" => "Login required"]);
    exit;
}

if ($_SESSION['role'] !== 'buyer') {
    echo json_encode(["status" => "error", "msg" => "Only buyers can add to cart"]);
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$user_id    = $_SESSION['user_id'];

if (!$product_id) {
    echo json_encode(["status" => "error", "msg" => "Invalid product"]);
    exit;
}

$prod = $pdo->prepare("SELECT seller_id, stock, status FROM products WHERE product_id=?");
$prod->execute([$product_id]);
$product = $prod->fetch(PDO::FETCH_ASSOC);
if (!$product || $product['status'] !== 'active' || (int)$product['stock'] <= 0) {
    echo json_encode(["status" => "error", "msg" => "This listing is no longer available"]);
    exit;
}
if ((int)$product['seller_id'] === $user_id) {
    echo json_encode(["status" => "error", "msg" => "Sellers cannot buy their own listings"]);
    exit;
}

$check = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$check->execute([$user_id, $product_id]);
$existing = $check->fetch();

if ($existing) {
    $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?")
        ->execute([$existing['cart_id']]);
} else {
    $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,1)")
        ->execute([$user_id, $product_id]);
}

echo json_encode(["status" => "success", "msg" => "Added to cart"]);
