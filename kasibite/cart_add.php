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
$notes      = trim($_POST['notes'] ?? '');
$user_id    = $_SESSION['user_id'];

if (!$product_id) {
    echo json_encode(["status" => "error", "msg" => "Invalid product"]);
    exit;
}

$check = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$check->execute([$user_id, $product_id]);
$existing = $check->fetch();

if ($existing) {
    if ($notes) {
        $pdo->prepare("UPDATE cart SET quantity = quantity + 1, notes = ? WHERE cart_id = ?")
            ->execute([$notes, $existing['cart_id']]);
    } else {
        $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?")
            ->execute([$existing['cart_id']]);
    }
} else {
    $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, notes) VALUES (?,?,1,?)")
        ->execute([$user_id, $product_id, $notes ?: null]);
}

echo json_encode(["status" => "success", "msg" => "Added to cart"]);
