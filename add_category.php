<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    die("Access denied");
}

$name = trim($_POST['category_name'] ?? '');

if ($name !== '') {
    // avoid exact duplicates
    $check = $pdo->prepare("SELECT category_id FROM categories WHERE category_name = ?");
    $check->execute([$name]);
    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$name]);
    }
}

header("Location: seller.php");
exit;