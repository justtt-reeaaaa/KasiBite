<?php
$host = "localhost";
$dbname = "kasibite";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Ensure uploads folder exists for product/category images
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
