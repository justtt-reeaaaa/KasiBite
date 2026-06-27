<?php
$host = "sql305.infinityfree.com";
$dbname = "if0_42244333_kasibite";
$username = "if0_42244333";
$password = "sDej6OhsFr";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>