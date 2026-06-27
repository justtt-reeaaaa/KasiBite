<?php
session_start();
require 'config.php';
require_once 'helpers.php';

$cat_filter = (int)($_GET['cat'] ?? 0);
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT p.*, c.category_name, c.image_url AS category_image_url, u.full_name AS seller_name,
           COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.review_id) AS review_count
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    JOIN users u ON p.seller_id = u.user_id
    LEFT JOIN reviews r ON p.product_id = r.product_id
    WHERE p.status = 'active' AND p.stock > 0
";
$params = [];
if ($cat_filter) { $sql .= " AND p.category_id = ?"; $params[] = $cat_filter; }
if ($search) { $sql .= " AND p.name LIKE ?"; $params[] = "%$search%"; }
$sql .= " GROUP BY p.product_id ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Browse Food 🍜";
include 'header.php';
?>
<style>
.browse-layout{display:flex;min-height:calc(100vh - 60px);}
.sidebar{width:240px;background:var(--dark);color:white;padding:25px 20px;flex-shrink:0;}
.sidebar h3{color:var(--primary);font-size:15px;margin-bottom:15px;border-bottom:1px solid #333;padding-bottom:8px;}
.sidebar a{display:block;color:#ccc;text-decoration:none;padding:8px 10px;border-radius:6px;font-size:13px;margin-bottom:4px;transition:0.2s;}
.sidebar a:hover,.sidebar a.active{background:var(--primary);color:white;}
.main{flex:1;padding:25px;}
.main h2{font-size:22px;margin-bottom:6px;}
.search-bar{display:flex;gap:10px;margin-bottom:20px;margin-top:15px;}
.search-bar input{flex:1;padding:10px 14px;border:1px solid var(--border);border-radius:6px;font-family:Poppins;font-size:14px;}
.search-bar button{padding:10px 18px;background:var(--primary);color:white;border:none;border-radius:6px;cursor:pointer;font-family:Poppins;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;}
.card{background:var(--beige);border-radius:10px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);transition:transform 0.2s;}
.card:hover{transform:translateY(-3px);}
.card img{width:100%;height:150px;object-fit:cover;}
.card-body{padding:12px;}
.card-body h3{font-size:14px;margin-bottom:4px;}
.card-body .cat{font-size:12px;color:var(--muted);margin-bottom:4px;}
.card-body .price{color:var(--primary);font-weight:700;font-size:15px;margin-bottom:8px;}
.card-body .btn{width:100%;padding:8px;font-size:13px;margin-bottom:6px;display:block;text-align:center;}
.results-info{font-size:13px;color:var(--muted);margin-bottom:15px;}
</style>
<div class="browse-layout">
  <div class="sidebar">
    <h3>Categories</h3>
    <a href="browse.php" class="<?=!$cat_filter?'active':''?>">All Food</a>
    <?php foreach($categories as $c): ?>
    <a href="browse.php?cat=<?=$c['category_id']?>" class="<?=$cat_filter==$c['category_id']?'active':''?>"><?=e($c['category_name'])?></a>
    <?php endforeach; ?>
    <hr style="border-color:#333;margin:20px 0;">
    <?php if(isset($_SESSION['user_id'])): ?>
    <a href="<?=$_SESSION['role']==='seller'?'seller_dashboard.php':($_SESSION['role']==='admin'?'admin.php':'buyer_dashboard.php')?>">My Dashboard</a>
    <?php if($_SESSION['role']==='buyer'): ?><a href="cart.php">My Cart</a><?php endif; ?>
    <a href="logout.php">Logout</a>
    <?php else: ?>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
    <?php endif; ?>
  </div>
  <div class="main">
    <h2>Browse <span style="color:var(--primary);">Kasi Food</span></h2>
    <form method="GET" class="search-bar">
      <?php if($cat_filter): ?><input type="hidden" name="cat" value="<?=$cat_filter?>"><?php endif; ?>
      <input type="text" name="search" placeholder="Search food..." value="<?=e($search)?>">
      <button type="submit">Search</button>
      <?php if($search||$cat_filter): ?><a href="browse.php" style="padding:10px 14px;background:#eee;color:#333;border-radius:6px;font-size:13px;line-height:1.5;">Clear</a><?php endif; ?>
    </form>
    <p class="results-info"><?=count($products)?> item<?=count($products)!=1?'s':''?> found<?=$search?' for "'.e($search).'"':''?></p>
    <?php if(empty($products)): ?>
    <div style="text-align:center;padding:60px;color:var(--muted);">
      <div style="font-size:50px;margin-bottom:15px;">Food</div>
      <p>No food found. Try a different search or category.</p>
    </div>
    <?php else: ?>
    <div class="grid">
      <?php foreach($products as $p): ?>
      <div class="card">
        <img src="<?=e(category_image($p))?>" alt="<?=e($p['name'])?>">
        <div class="card-body">
          <h3><?=e($p['name'])?></h3>
          <div class="cat"><?=e($p['category_name'])?></div>
          <div class="cat">Seller: <?=e($p['seller_name'])?></div>
          <div class="price">R<?=number_format($p['price'],2)?></div>
          <div style="font-size:12px;color:#555;margin-bottom:6px;">
            <?=$p['review_count'] ? number_format($p['avg_rating'],1).' / 5 from '.$p['review_count'].' review(s)' : 'No reviews yet'?>
          </div>
          <?php if($p['description']): ?><p style="font-size:12px;color:#666;margin-bottom:10px;"><?=e(substr($p['description'],0,70)).(strlen($p['description'])>70?'...':'')?></p><?php endif; ?>
          <button class="btn" onclick="addToCart(event, <?=$p['product_id']?>)">Add to Cart</button>
          <?php if(isset($_SESSION['role']) && $_SESSION['role']==='buyer'): ?>
          <a class="btn btn-dark" href="message_seller.php?product_id=<?=$p['product_id']?>">Message Seller</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<script>
function addToCart(evt, id){
    fetch('cart_add.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'product_id='+id})
    .then(r=>r.json())
    .then(d=>{
        if(d.status==='error'){
            alert(d.msg || 'Please login to add items to cart.');
            if(d.msg==='Login required') window.location='login.php';
        } else {
            evt.target.textContent='Added';
            evt.target.style.background='var(--success)';
            setTimeout(()=>{evt.target.textContent='Add to Cart';evt.target.style.background='';},1500);
        }
    });
}
</script>
<?php include 'footer.php'; ?>
