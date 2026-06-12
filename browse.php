<?php
session_start();
require 'config.php';

$cat_filter=(int)($_GET['cat']??0);
$search=trim($_GET['search']??'');

$sql="SELECT p.*,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id WHERE p.status='active'";
$params=[];
if($cat_filter){$sql.=" AND p.category_id=?";$params[]=$cat_filter;}
if($search){$sql.=" AND p.name LIKE ?";$params[]="%$search%";}
$sql.=" ORDER BY p.created_at DESC";
$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$products=$stmt->fetchAll(PDO::FETCH_ASSOC);
$categories=$pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

$catImages=[
    'Amagwinya'=>'https://iol-prod.appspot.com/image/0a866824961ebe8a09ad8875ebac339f70fdbe4e=w700',
    'Pap & Stew'=>'https://th.bing.com/th/id/R.79bd82699014d8e920c92c40ef5436ff?rik=raN2D5pdTbIujQ&pid=ImgRaw&r=0',
    'Grilled Meat'=>'https://www.suburbansimplicity.com/wp-content/uploads/2021/06/How-to-keep-meat-moist-on-the-grill.jpg',
    'Breakfast'=>'https://tse3.mm.bing.net/th/id/OIP.cV8IfMXFn2uqn3YOR4ne0gHaHa?r=0&pid=ImgDetMain',
    'Beverages'=>'https://th.bing.com/th/id/R.4327e9e3d10634e6af86b81314bacd0d?rik=9VdoAm28zgLKsQ&pid=ImgRaw&r=0',
    'Vetkoek'=>'https://as2.ftcdn.net/v2/jpg/02/23/81/47/1000_F_223814741_k90kjLiXIFbLXpUtlnlOWyioTUoMt1vU.jpg',
    'Umngqusho'=>'https://www.thesouthafrican.com/wp-content/uploads/2020/07/087f68fa-umgquasho-samp-and-beans-with-lamb-and-chakalaka.jpg',
    'Snacks & Sides'=>'https://healy-group.com/wp-content/uploads/AdobeStock_953274304-min-1920x1076.jpeg',
    'Smiley & Walkie Talkies'=>'https://www.houseofyork.co.za/images/cmsimages/big/news-288-2588-walkie-talkie.jpeg',
    'Bunny Chow'=>'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d',
];
$defaultImg='https://images.unsplash.com/photo-1604908176997-125f25cc6f3d';
$pageTitle="Browse Food"; include 'header.php';
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
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:18px;}
.card{background:var(--beige);border-radius:10px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);transition:transform 0.2s;}
.card:hover{transform:translateY(-3px);}
.card img{width:100%;height:150px;object-fit:cover;}
.card-body{padding:12px;}
.card-body h3{font-size:14px;margin-bottom:4px;}
.card-body .cat{font-size:12px;color:var(--muted);margin-bottom:4px;}
.card-body .price{color:var(--primary);font-weight:700;font-size:15px;margin-bottom:10px;}
.card-body .btn{width:100%;padding:8px;font-size:13px;margin-bottom:6px;display:block;text-align:center;}
.results-info{font-size:13px;color:var(--muted);margin-bottom:15px;}
</style>
<div class="browse-layout">
  <div class="sidebar">
    <h3>Categories</h3>
    <a href="browse.php" class="<?=!$cat_filter?'active':''?>">All Food</a>
    <?php foreach($categories as $c): ?>
    <a href="browse.php?cat=<?=$c['category_id']?>" class="<?=$cat_filter==$c['category_id']?'active':''?>"><?=htmlspecialchars($c['category_name'])?></a>
    <?php endforeach; ?>
    <hr style="border-color:#333;margin:20px 0;">
    <?php if(isset($_SESSION['user_id'])): ?>
    <a href="<?=$_SESSION['role']==='seller'?'seller_dashboard.php':($_SESSION['role']==='admin'?'admin.php':'buyer_dashboard.php')?>">📊 My Dashboard</a>
    <?php if($_SESSION['role']==='buyer'): ?><a href="cart.php">🛒 My Cart</a><?php endif; ?>
    <a href="logout.php">🚪 Logout</a>
    <?php else: ?>
    <a href="login.php">🔑 Login</a>
    <a href="register.php">📝 Register</a>
    <?php endif; ?>
  </div>
  <div class="main">
    <h2>Browse <span style="color:var(--primary);">Kasi Food</span></h2>
    <form method="GET" class="search-bar">
      <?php if($cat_filter): ?><input type="hidden" name="cat" value="<?=$cat_filter?>"><?php endif; ?>
      <input type="text" name="search" placeholder="Search food..." value="<?=htmlspecialchars($search)?>">
      <button type="submit">Search</button>
      <?php if($search||$cat_filter): ?><a href="browse.php" style="padding:10px 14px;background:#eee;color:#333;border-radius:6px;font-size:13px;line-height:1.5;">Clear</a><?php endif; ?>
    </form>
    <p class="results-info"><?=count($products)?> item<?=count($products)!=1?'s':''?> found<?=$search?' for "'.htmlspecialchars($search).'"':''?></p>
    <?php if(empty($products)): ?>
    <div style="text-align:center;padding:60px;color:var(--muted);">
      <div style="font-size:50px;margin-bottom:15px;">🍽️</div>
      <p>No food found. Try a different search or category.</p>
    </div>
    <?php else: ?>
    <div class="grid">
      <?php foreach($products as $p):
        $img=$catImages[$p['category_name']]??$defaultImg;
      ?>
      <div class="card">
        <img src="<?=$img?>" alt="<?=htmlspecialchars($p['name'])?>">
        <div class="card-body">
          <h3><?=htmlspecialchars($p['name'])?></h3>
          <div class="cat"><?=htmlspecialchars($p['category_name'])?></div>
          <div class="price">R<?=number_format($p['price'],2)?></div>
          <?php if($p['description']): ?><p style="font-size:12px;color:#666;margin-bottom:10px;"><?=htmlspecialchars(substr($p['description'],0,70)).(strlen($p['description'])>70?'...':'')?></p><?php endif; ?>
          <button class="btn" onclick="addToCart(<?=$p['product_id']?>)">Add to Cart 🛒</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<script>
function addToCart(id){
    fetch('cart_add.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'product_id='+id})
    .then(r=>r.json())
    .then(d=>{
        if(d.status==='error'){alert('Please login to add items to cart.');window.location='login.php';}
        else{
            // Simple feedback
            event.target.textContent='✓ Added!';
            event.target.style.background='var(--success)';
            setTimeout(()=>{event.target.textContent='Add to Cart 🛒';event.target.style.background='';},1500);
        }
    });
}
</script>
<?php include 'footer.php'; ?>
