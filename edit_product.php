<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    die("Access denied");
}

$seller_id  = $_SESSION['user_id'];
$product_id = (int)($_GET['id'] ?? 0);

// load product and check it belongs to this seller
$stmt = $pdo->prepare("
    SELECT product_id, name, description, price 
    FROM products 
    WHERE product_id = ? AND seller_id = ?
");
$stmt->execute([$product_id, $seller_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found");
}

// handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'] ?? '';
    $price       = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';

    if ($name && $price > 0) {
        $u = $pdo->prepare("
            UPDATE products 
            SET name = ?, description = ?, price = ?
            WHERE product_id = ? AND seller_id = ?
        ");
        $u->execute([$name, $description, $price, $product_id, $seller_id]);
        header("Location: seller.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - KasiBite</title>
    <style>
        body{
            font-family:Poppins, Arial, sans-serif;
            background:#f5f5f5;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            margin:0;
        }
        .box{
            background:#fff;
            padding:25px;
            border-radius:8px;
            box-shadow:0 0 8px rgba(0,0,0,0.12);
            width:350px;
        }
        h2{margin-top:0;color:#333;}
        input,textarea{
            width:100%;
            padding:8px;
            margin:6px 0 10px;
            border:1px solid #ccc;
            border-radius:4px;
            font-size:14px;
        }
        textarea{min-height:70px;resize:vertical;}
        button{
            width:100%;
            padding:10px;
            background:#e67e22;
            color:#fff;
            border:none;
            border-radius:4px;
            cursor:pointer;
        }
        a{
            font-size:12px;
            text-decoration:none;
            color:#555;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>Edit Product</h2>
    <form method="POST">
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
        <button type="submit">Save changes</button>
    </form>
    <p><a href="seller.php">Back to dashboard</a></p>
</div>
</body>
</html>