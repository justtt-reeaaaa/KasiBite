<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = (int)$_SESSION['user_id'];

// Handle delete product
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE product_id = ? AND seller_id = ?")->execute([$product_id, $seller_id]);
    header("Location: seller.php");
    exit;
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch seller's products
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch seller info
$seller = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$seller->execute([$seller_id]);
$seller = $seller->fetch(PDO::FETCH_ASSOC);

// Count orders for this seller
$order_count = $pdo->prepare("
    SELECT COUNT(DISTINCT o.order_id) as cnt
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE p.seller_id = ?
");
$order_count->execute([$seller_id]);
$order_count = $order_count->fetch()['cnt'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Seller Dashboard - KasiBite</title>
<style>
:root { --primary: #e67e22; --dark: #1e1e1e; --light: #f5f5f5; }

body { margin: 0; font-family: Poppins, sans-serif; background: var(--light); }

.container { display: flex; }

.sidebar {
    width: 250px;
    background: var(--dark);
    color: white;
    min-height: 100vh;
    padding: 20px;
    box-sizing: border-box;
}

.sidebar h2 { color: var(--primary); }
.sidebar a { display: block; color: white; text-decoration: none; margin: 15px 0; }
.sidebar a:hover { color: var(--primary); }
.sidebar .role-tag {
    font-size: 11px;
    background: var(--primary);
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 5px;
}

.main { flex: 1; padding: 30px; }

h2 { border-bottom: 3px solid var(--primary); padding-bottom: 8px; display: inline-block; }

/* STATS ROW */
.stats {
    display: flex;
    gap: 15px;
    margin: 20px 0 30px;
    flex-wrap: wrap;
}

.stat-card {
    background: white;
    padding: 15px 25px;
    border-radius: 8px;
    border-left: 5px solid var(--primary);
    min-width: 140px;
}

.stat-card .num { font-size: 28px; font-weight: bold; color: var(--primary); }
.stat-card .label { font-size: 12px; color: #888; }

/* MESSAGES */
.success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
.error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }

/* ADD PRODUCT FORM */
.form-box {
    background: beige;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    max-width: 600px;
}

.form-box h3 { margin-top: 0; color: var(--dark); }

.form-row { display: flex; gap: 15px; }
.form-row > div { flex: 1; }

label { display: block; font-size: 13px; color: #555; margin-bottom: 4px; margin-top: 12px; }

input[type=text], input[type=number], select, textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    font-family: Poppins, sans-serif;
    box-sizing: border-box;
}

textarea { height: 70px; resize: vertical; }

.btn {
    padding: 10px 20px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-family: Poppins, sans-serif;
    font-size: 14px;
    margin-top: 15px;
}

/* PRODUCTS TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

th { background: var(--primary); color: white; padding: 12px; text-align: left; font-size: 13px; }
td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }

.del-btn {
    background: #c0392b;
    color: white;
    border: none;
    padding: 5px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.status-pending { color: #e67e22; font-weight: bold; }
.status-approved { color: #27ae60; font-weight: bold; }
</style>
</head>
<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>KasiBite</h2>
    <span class="role-tag">SELLER</span>
    <p style="font-size:14px;"><?= htmlspecialchars($seller['full_name']) ?></p>
    <a href="seller.php"><strong>📦 My Dashboard</strong></a>
    <a href="browse.php">🍽 Browse</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- MAIN -->
<div class="main">
    <h2>Seller Dashboard</h2>

    <!-- STATUS NOTICE -->
    <?php if ($seller['status'] == 'pending'): ?>
        <div class="error-msg">⏳ Your seller account is <strong>pending admin approval</strong>. You can add products but they won't be visible until approved.</div>
    <?php elseif ($seller['status'] == 'approved'): ?>
        <div class="success">✅ Your account is <strong>approved</strong>. Your listings are live!</div>
    <?php endif; ?>

    <?php if (isset($_SESSION['seller_success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['seller_success']) ?></div>
        <?php unset($_SESSION['seller_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['seller_error'])): ?>
        <div class="error-msg"><?= htmlspecialchars($_SESSION['seller_error']) ?></div>
        <?php unset($_SESSION['seller_error']); ?>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats">
        <div class="stat-card">
            <div class="num"><?= count($products) ?></div>
            <div class="label">Products Listed</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= $order_count ?></div>
            <div class="label">Orders Received</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= ucfirst($seller['status']) ?></div>
            <div class="label">Account Status</div>
        </div>
    </div>

    <!-- ADD PRODUCT FORM -->
    <div class="form-box">
        <h3>➕ Add New Product</h3>
        <form method="POST" action="add_product.php">

            <label>Product Name *</label>
            <input type="text" name="name" placeholder="e.g. Pap ne nyama" required>

            <label>Category *</label>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="form-row">
                <div>
                    <label>Price (R) *</label>
                    <input type="number" name="price" placeholder="e.g. 35.00" step="0.01" min="1" required>
                </div>
                <div>
                    <label>Stock / Portions</label>
                    <input type="number" name="stock" placeholder="e.g. 20" value="10" min="1">
                </div>
            </div>

            <label>Description</label>
            <textarea name="description" placeholder="Describe your food item..."></textarea>

            <button type="submit" class="btn">Add Product</button>
        </form>
    </div>

    <!-- PRODUCT LISTINGS -->
    <h3>My Listings (<?= count($products) ?>)</h3>

    <?php if (empty($products)): ?>
        <p style="color:#888;">You haven't added any products yet.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['category_name']) ?></td>
            <td>R<?= number_format($p['price'], 2) ?></td>
            <td><?= $p['stock'] ?></td>
            <td>
                <button class="del-btn" onclick="confirmDelete(<?= $p['product_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

</div>
</div>

<script>
function confirmDelete(id) {
    if (confirm("Delete this product?")) {
        window.location.href = "seller.php?delete=" + id;
    }
}
</script>

</body>
</html>
