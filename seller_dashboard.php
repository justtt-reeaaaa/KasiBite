<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='seller'){header("Location:login.php");exit;}
if($_SESSION['status']!=='approved'){
    echo "<p style='text-align:center;padding:60px;font-family:Poppins;'>Your account is pending admin approval. <a href='logout.php'>Logout</a></p>";
    exit;
}
$seller_id=$_SESSION['user_id'];

// Handle add product
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['add_product'])){
    $name=trim($_POST['name']);
    $price=(float)$_POST['price'];
    $cat=(int)$_POST['category_id'];
    $desc=trim($_POST['description']);
    $stock=(int)$_POST['stock'];
    if($name&&$price>0&&$cat){
        $pdo->prepare("INSERT INTO products (seller_id,category_id,name,description,price,stock) VALUES (?,?,?,?,?,?)")
            ->execute([$seller_id,$cat,$name,$desc,$price,$stock]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Product added successfully!'];
    }
    header("Location:seller_dashboard.php");exit;
}

// Handle delete product
if(isset($_GET['delete'])){
    $pid=(int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE product_id=? AND seller_id=?")->execute([$pid,$seller_id]);
    header("Location:seller_dashboard.php");exit;
}

// Handle toggle status
if(isset($_GET['toggle'])){
    $pid=(int)$_GET['toggle'];
    $pdo->prepare("UPDATE products SET status=IF(status='active','inactive','active') WHERE product_id=? AND seller_id=?")->execute([$pid,$seller_id]);
    header("Location:seller_dashboard.php");exit;
}

$user=$pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$seller_id]);
$user=$user->fetch(PDO::FETCH_ASSOC);

$products=$pdo->prepare("SELECT p.*,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id WHERE p.seller_id=? ORDER BY p.created_at DESC");
$products->execute([$seller_id]);
$products=$products->fetchAll(PDO::FETCH_ASSOC);

$cats=$pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// Orders for this seller's products
$orders=$pdo->prepare("
  SELECT DISTINCT o.order_id,o.total_amount,o.status,o.payment_ref,o.created_at,u.full_name as buyer_name
  FROM orders o
  JOIN order_items oi ON o.order_id=oi.order_id
  JOIN products p ON oi.product_id=p.product_id
  JOIN users u ON o.buyer_id=u.user_id
  WHERE p.seller_id=?
  ORDER BY o.created_at DESC LIMIT 10
");
$orders->execute([$seller_id]);
$orders=$orders->fetchAll(PDO::FETCH_ASSOC);

$pageTitle="Seller Dashboard"; include 'header.php';
?>
<style>
.dash-wrap{max-width:1100px;margin:40px auto;padding:0 20px;}
.dash-header{display:flex;align-items:center;gap:20px;background:white;border-radius:12px;padding:25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:25px;flex-wrap:wrap;}
.avatar{width:60px;height:60px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;color:white;font-weight:700;flex-shrink:0;}
.stats-row{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:25px;}
.stat-card{background:white;border-radius:10px;padding:20px;flex:1;min-width:140px;box-shadow:0 2px 8px rgba(0,0,0,0.08);text-align:center;}
.stat-card .num{font-size:28px;font-weight:700;color:var(--primary);}
.stat-card .label{font-size:12px;color:var(--muted);margin-top:4px;}
.two-col{display:flex;gap:25px;flex-wrap:wrap;}
.col-main{flex:1;min-width:300px;}
.col-side{width:320px;}
.section-box{background:white;border-radius:10px;padding:25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px;}
.section-box h3{font-size:16px;margin-bottom:18px;border-bottom:2px solid var(--primary);padding-bottom:8px;}
.prod-table{width:100%;border-collapse:collapse;}
.prod-table th{background:var(--dark);color:white;padding:10px 14px;text-align:left;font-size:12px;}
.prod-table td{padding:10px 14px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:middle;}
.prod-table tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-active{background:#d4edda;color:#155724;}
.badge-inactive{background:#f8d7da;color:#721c24;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-confirmed{background:#d4edda;color:#155724;}
.action-links a{font-size:12px;margin-right:8px;}
</style>
<div class="dash-wrap">
  <div class="dash-header">
    <div class="avatar"><?=strtoupper(substr($user['full_name'],0,1))?></div>
    <div>
      <h2 style="margin-bottom:4px;">Seller Dashboard</h2>
      <p style="font-size:13px;color:var(--muted);"><?=htmlspecialchars($user['full_name'])?> · <?=htmlspecialchars($user['email'])?></p>
      <?php if($user['location']): ?><p style="font-size:12px;color:var(--muted);">📍 <?=htmlspecialchars($user['location'])?></p><?php endif; ?>
    </div>
    <span class="badge badge-active" style="margin-left:auto;">✓ Verified Seller</span>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="num"><?=count($products)?></div><div class="label">My Products</div></div>
    <div class="stat-card"><div class="num"><?=count($orders)?></div><div class="label">Recent Orders</div></div>
    <div class="stat-card"><div class="num"><?=count(array_filter($products,fn($p)=>$p['status']==='active'))?></div><div class="label">Active Listings</div></div>
  </div>

  <div class="two-col">
    <div class="col-main">
      <!-- My Products -->
      <div class="section-box">
        <h3>🍽️ My Products</h3>
        <?php if(empty($products)): ?>
        <p style="color:var(--muted);font-size:14px;">No products yet. Add your first listing →</p>
        <?php else: ?>
        <table class="prod-table">
          <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($products as $p): ?>
          <tr>
            <td><strong><?=htmlspecialchars($p['name'])?></strong></td>
            <td><?=htmlspecialchars($p['category_name'])?></td>
            <td>R<?=number_format($p['price'],2)?></td>
            <td><?=$p['stock']?></td>
            <td><span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span></td>
            <td class="action-links">
              <a href="seller_dashboard.php?toggle=<?=$p['product_id']?>" style="color:var(--primary);"><?=$p['status']==='active'?'Deactivate':'Activate'?></a>
              <a href="seller_dashboard.php?delete=<?=$p['product_id']?>" style="color:var(--danger);" onclick="return confirm('Delete this product?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- Recent Orders -->
      <div class="section-box">
        <h3>📦 Recent Orders</h3>
        <?php if(empty($orders)): ?>
        <p style="color:var(--muted);font-size:14px;">No orders yet.</p>
        <?php else: ?>
        <table class="prod-table">
          <thead><tr><th>Ref</th><th>Buyer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach($orders as $o): ?>
          <tr>
            <td><strong><?=htmlspecialchars($o['payment_ref'])?></strong></td>
            <td><?=htmlspecialchars($o['buyer_name'])?></td>
            <td>R<?=number_format($o['total_amount'],2)?></td>
            <td><span class="badge badge-<?=$o['status']?>"><?=ucfirst($o['status'])?></span></td>
            <td style="font-size:12px;"><?=date('d M Y',strtotime($o['created_at']))?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-side">
      <!-- Add Product Form -->
      <div class="section-box">
        <h3>➕ Add New Product</h3>
        <form method="POST">
          <div class="form-group"><label>Product Name</label><input type="text" name="name" placeholder="e.g. Pap ne Nyama" required></div>
          <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
              <option value="">-- Select Category --</option>
              <?php foreach($cats as $c): ?>
              <option value="<?=$c['category_id']?>"><?=htmlspecialchars($c['category_name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Description</label><textarea name="description" rows="3" placeholder="Describe your food..."></textarea></div>
          <div class="form-group"><label>Price (R)</label><input type="number" name="price" placeholder="45.00" step="0.01" min="1" required></div>
          <div class="form-group"><label>Stock / Servings Available</label><input type="number" name="stock" placeholder="20" min="1" value="10" required></div>
          <button type="submit" name="add_product" class="btn btn-full">Add Product</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
