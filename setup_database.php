<?php
require 'config.php';

$statements = [
    "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 1",
    "ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(120) NULL",
    "ALTER TABLE users ADD COLUMN business_name VARCHAR(150) NULL",
    "ALTER TABLE users ADD COLUMN phone VARCHAR(40) NULL",
    "ALTER TABLE users ADD COLUMN id_number VARCHAR(80) NULL",
    "ALTER TABLE users ADD COLUMN verification_details TEXT NULL",
    "ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00",
    "ALTER TABLE categories ADD COLUMN image_url TEXT NULL",
    "ALTER TABLE products ADD COLUMN image_url TEXT NULL",
    "ALTER TABLE cart ADD COLUMN special_instructions TEXT NULL",
    "ALTER TABLE order_items ADD COLUMN special_instructions TEXT NULL",
    "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(30) NOT NULL DEFAULT 'card'",
    "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'paid'",
    "ALTER TABLE orders ADD COLUMN delivery_notes TEXT NULL",
    "CREATE TABLE IF NOT EXISTS seller_payouts (
        payout_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        seller_id INT NOT NULL,
        product_id INT NOT NULL,
        gross_amount DECIMAL(10,2) NOT NULL,
        platform_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        seller_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(order_id),
        INDEX(seller_id)
    )",
    "CREATE TABLE IF NOT EXISTS messages (
        message_id INT AUTO_INCREMENT PRIMARY KEY,
        buyer_id INT NOT NULL,
        seller_id INT NOT NULL,
        product_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(buyer_id),
        INDEX(seller_id),
        INDEX(product_id)
    )",
    "CREATE TABLE IF NOT EXISTS reviews (
        review_id INT AUTO_INCREMENT PRIMARY KEY,
        buyer_id INT NOT NULL,
        seller_id INT NOT NULL,
        product_id INT NOT NULL,
        order_id INT NOT NULL,
        rating TINYINT NOT NULL,
        comment TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_buyer_product_order (buyer_id, product_id, order_id),
        INDEX(seller_id),
        INDEX(product_id)
    )",
    "CREATE TABLE IF NOT EXISTS disputes (
        dispute_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        buyer_id INT NOT NULL,
        seller_id INT NOT NULL,
        product_id INT NULL,
        reason VARCHAR(180) NOT NULL,
        description TEXT NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(order_id),
        INDEX(buyer_id),
        INDEX(seller_id)
    )",
    "CREATE TABLE IF NOT EXISTS password_resets (
        reset_id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(150) NOT NULL,
        token VARCHAR(120) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(email),
        INDEX(token)
    )"
];

$results = [];
foreach ($statements as $sql) {
    try {
        $pdo->exec($sql);
        $results[] = ['ok' => true, 'sql' => $sql];
    } catch (PDOException $e) {
        $alreadyExists = stripos($e->getMessage(), 'Duplicate column') !== false
            || stripos($e->getMessage(), 'Duplicate key') !== false;
        $results[] = ['ok' => $alreadyExists, 'sql' => $sql, 'msg' => $alreadyExists ? 'Already exists' : $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>KasiBite Database Setup</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;}
    .box{background:white;max-width:900px;margin:auto;padding:24px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08);}
    .ok{color:#167a3a}.bad{color:#b00020}li{margin-bottom:8px}
  </style>
</head>
<body>
<div class="box">
  <h1>Database setup complete</h1>
  <p>Green items were created or already existed. If anything is red, copy that message and fix it in phpMyAdmin.</p>
  <ul>
  <?php foreach($results as $r): ?>
    <li class="<?=$r['ok']?'ok':'bad'?>"><?=$r['ok']?'OK':'ERROR'?> - <?=htmlspecialchars(substr($r['sql'],0,80))?><?=isset($r['msg'])?' - '.htmlspecialchars($r['msg']):''?></li>
  <?php endforeach; ?>
  </ul>
  <p><a href="index.php">Go to site</a></p>
</div>
</body>
</html>
