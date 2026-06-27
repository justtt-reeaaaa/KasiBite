<?php
session_start();
require 'config.php';

$cat_filter=(int)($_GET['cat']??0);
$search=trim($_GET['search']??'');
$area_filter=trim($_GET['area']??'');

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
$default='https://images.unsplash.com/photo-1604908176997-125f25cc6f3d';

// Fetch distinct areas from approved sellers for the filter dropdown
$areas=$pdo->query("SELECT DISTINCT stall_address FROM users WHERE role='seller' AND status='approved' AND stall_address IS NOT NULL AND stall_address!='' ORDER BY stall_address")->fetchAll(PDO::FETCH_COLUMN);

// Build product query — joins seller info including stall_address and location
$sql="SELECT p.*,c.category_name,
      u.full_name as seller_name,
      u.stall_address,
      u.location as seller_area,
      u.status as seller_status,
      COALESCE((SELECT AVG(rating) FROM reviews r WHERE r.product_id=p.product_id),0) as avg_rating,
      COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.product_id=p.product_id),0) as review_count
      FROM products p
      JOIN categories c ON p.category_id=c.category_id
      JOIN users u ON p.seller_id=u.user_id
      WHERE p.status='active' AND u.status='approved'";
$params=[];
if($cat_filter){$sql.=" AND p.category_id=?";$params[]=$cat_filter;}
if($search){$sql.=" AND (p.name LIKE ? OR u.full_name LIKE ? OR u.stall_address LIKE ?)";$params[]="%$search%";$params[]="%$search%";$params[]="%$search%";}
if($area_filter){$sql.=" AND (u.stall_address LIKE ? OR u.location LIKE ?)";$params[]="%$area_filter%";$params[]="%$area_filter%";}
$sql.=" ORDER BY p.created_at DESC";
$stmt=$pdo->prepare($sql);$stmt->execute($params);
$products=$stmt->fetchAll(PDO::FETCH_ASSOC);
$categories=$pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

function stars($avg){$s='';for($i=1;$i<=5;$i++)$s.=$i<=$avg?'★':'☆';return $s;}
function getImg($p,$catImages,$default){return($p['image_path']&&file_exists($p['image_path']))?$p['image_path']:($catImages[$p['category_name']]??$default);}

$pageTitle="Browse Food"; include 'header.php';
?>
<style>
.browse-layout{display:flex;min-height:calc(100vh - 60px);}
.sidebar{width:230px;background:var(--dark);color:white;padding:22px 18px;flex-shrink:0;}
.sidebar h3{color:var(--primary);font-size:14px;margin-bottom:12px;border-bottom:1px solid #333;padding-bottom:7px;}
.sidebar a{display:block;color:#ccc;text-decoration:none;padding:7px 10px;border-radius:5px;font-size:13px;margin-bottom:3px;transition:0.2s;}
.sidebar a:hover,.sidebar a.active{background:var(--primary);color:white;}
.main{flex:1;padding:22px;}
.filters-bar{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
.filters-bar input,.filters-bar select{padding:9px 13px;border:1px solid var(--border);border-radius:6px;font-family:Poppins,sans-serif;font-size:13px;background:white;}
.filters-bar input{flex:1;min-width:180px;}
.filters-bar select{min-width:180px;}
.filters-bar button{padding:9px 16px;background:var(--primary);color:white;border:none;border-radius:6px;cursor:pointer;font-family:Poppins;font-size:13px;white-space:nowrap;}
.filters-bar a.clear-btn{padding:9px 13px;background:#eee;color:#333;border-radius:6px;font-size:13px;text-decoration:none;line-height:1.5;white-space:nowrap;}
.area-notice{background:var(--beige);border:1px solid #e0c8a0;border-radius:8px;padding:10px 16px;font-size:13px;color:#7a5c2e;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px;}
/* CARD */
.card{background:var(--beige);border-radius:10px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);transition:transform 0.2s;cursor:pointer;}
.card:hover{transform:translateY(-3px);}
.card img{width:100%;height:145px;object-fit:cover;}
.card-body{padding:11px;}
.card-body h3{font-size:13px;margin-bottom:3px;font-weight:700;}
.card-body .seller-line{font-size:11px;color:var(--primary);font-weight:600;margin-bottom:3px;}
.card-body .location-line{font-size:11px;color:var(--muted);margin-bottom:4px;display:flex;align-items:center;gap:3px;}
.card-body .price{color:var(--primary);font-weight:700;font-size:14px;margin-bottom:5px;}
.card-body .stars-display{color:#f1c40f;font-size:12px;}
.card-body .stars-display span{color:var(--muted);font-size:11px;margin-left:3px;}
.card-body .btn{width:100%;padding:7px;font-size:12px;margin-top:8px;display:block;text-align:center;border:none;cursor:pointer;border-radius:5px;}
.out-of-stock{background:#ccc!important;cursor:not-allowed;}
.verified-badge{background:#d4edda;color:#155724;font-size:10px;padding:1px 6px;border-radius:8px;font-weight:700;}
/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:1000;justify-content:center;align-items:flex-start;padding:30px 15px;overflow-y:auto;}
.modal-overlay.open{display:flex;}
.modal{background:white;border-radius:12px;width:100%;max-width:680px;overflow:hidden;margin:auto;position:relative;}
/* AREA PILLS */
.area-pills{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px;}
.area-pill{padding:5px 12px;background:#eee;border:none;border-radius:20px;font-size:12px;font-family:Poppins;cursor:pointer;color:#333;transition:0.2s;}
.area-pill:hover,.area-pill.active{background:var(--primary);color:white;}
/* seller group header */
.seller-group-header{background:var(--dark);color:white;border-radius:8px;padding:10px 16px;margin-bottom:12px;margin-top:20px;display:flex;align-items:center;gap:10px;font-size:13px;}
.seller-group-header .s-name{font-weight:700;color:var(--primary);font-size:15px;}
.seller-group-header .s-loc{color:#ccc;font-size:12px;}
</style>

<div class="browse-layout">
  <div class="sidebar">
    <h3>Categories</h3>
    <a href="browse.php" class="<?=!$cat_filter?'active':''?>">All Food</a>
    <?php foreach($categories as $c): ?>
    <a href="browse.php?cat=<?=$c['category_id']?><?=$area_filter?'&area='.urlencode($area_filter):''?>" class="<?=$cat_filter==$c['category_id']?'active':''?>"><?=htmlspecialchars($c['category_name'])?></a>
    <?php endforeach; ?>
    <hr style="border-color:#333;margin:15px 0;">
    <?php if(isset($_SESSION['user_id'])): ?>
    <?php if($_SESSION['role']==='buyer'): ?>
    <a href="buyer_dashboard.php">📊 My Dashboard</a>
    <a href="cart.php">🛒 My Cart</a>
    <?php elseif($_SESSION['role']==='seller'): ?>
    <a href="seller_dashboard.php">🏪 My Shop</a>
    <?php elseif($_SESSION['role']==='admin'): ?>
    <a href="admin.php">🛡️ Admin Panel</a>
    <?php endif; ?>
    <a href="logout.php">🚪 Logout</a>
    <?php else: ?>
    <a href="login.php">🔑 Login</a>
    <a href="register.php">📝 Register</a>
    <?php endif; ?>
  </div>

  <div class="main">
    <h2 style="font-size:20px;margin-bottom:6px;">Browse <span style="color:var(--primary);">Kasi Food</span></h2>
    <p style="font-size:12px;color:var(--muted);margin-bottom:14px;">Each listing shows the vendor's name and collection location — order from vendors closest to you.</p>

    <!-- SEARCH + AREA FILTER BAR -->
    <form method="GET" class="filters-bar">
      <?php if($cat_filter): ?><input type="hidden" name="cat" value="<?=$cat_filter?>"><?php endif; ?>
      <input type="text" name="search" placeholder="Search food or vendor name..." value="<?=htmlspecialchars($search)?>">
      <select name="area" onchange="this.form.submit()">
        <option value="">📍 All Areas</option>
        <?php foreach($areas as $a): $short=explode(',',$a)[0]; ?>
        <option value="<?=htmlspecialchars($short)?>" <?=$area_filter===$short?'selected':''?>><?=htmlspecialchars($short)?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Search</button>
      <?php if($search||$cat_filter||$area_filter): ?>
      <a href="browse.php" class="clear-btn">✕ Clear</a>
      <?php endif; ?>
    </form>

    <!-- AREA QUICK FILTER PILLS -->
    <?php if(!empty($areas)): ?>
    <div class="area-pills">
      <span style="font-size:12px;color:var(--muted);align-self:center;margin-right:4px;">Quick filter:</span>
      <a href="browse.php<?=$cat_filter?'?cat='.$cat_filter:''?>" class="area-pill <?=!$area_filter?'active':''?>">All Areas</a>
      <?php foreach($areas as $a): $short=explode(',',$a)[0]; ?>
      <a href="browse.php?area=<?=urlencode($short)?><?=$cat_filter?'&cat='.$cat_filter:''?>" class="area-pill <?=$area_filter===$short?'active':''?>"><?=htmlspecialchars($short)?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if($area_filter): ?>
    <div class="area-notice">
      📍 Showing vendors in / near <strong><?=htmlspecialchars($area_filter)?></strong> — <?=count($products)?> item<?=count($products)!=1?'s':''?> available
    </div>
    <?php else: ?>
    <p style="font-size:12px;color:var(--muted);margin-bottom:14px;"><?=count($products)?> item<?=count($products)!=1?'s':''?> available<?=$search?' matching "'.htmlspecialchars($search).'"':''?></p>
    <?php endif; ?>

    <?php if(empty($products)): ?>
    <div style="text-align:center;padding:60px;color:var(--muted);">
      <div style="font-size:50px;margin-bottom:15px;">🍽️</div>
      <p>No food found. Try a different search, category, or area.</p>
      <a href="browse.php" class="btn" style="margin-top:15px;">See All Food</a>
    </div>
    <?php else: ?>

    <!-- GROUP BY SELLER so buyers can clearly see which vendor sells what -->
    <?php
    // Group products by seller
    $grouped=[];
    foreach($products as $p){
        $key=$p['seller_name'].'__'.$p['stall_address'];
        $grouped[$key][]=$p;
    }
    ?>

    <?php foreach($grouped as $sellerKey=>$sellerProducts):
        $sp=$sellerProducts[0];
    ?>
    <div class="seller-group-header">
      <span style="font-size:20px;">🏪</span>
      <div>
        <div class="s-name">
          <?=htmlspecialchars($sp['seller_name'])?>
          <?php if($sp['seller_status']==='approved'): ?><span class="verified-badge">✓ Verified</span><?php endif; ?>
        </div>
        <div class="s-loc">
          📍 <?=htmlspecialchars($sp['stall_address']?:($sp['seller_area']?:'Location not specified'))?>
        </div>
      </div>
      <div style="margin-left:auto;font-size:12px;color:#aaa;"><?=count($sellerProducts)?> listing<?=count($sellerProducts)!=1?'s':''?></div>
    </div>

    <div class="grid" style="margin-bottom:10px;">
      <?php foreach($sellerProducts as $p):
        $img=getImg($p,$catImages,$default);
        $isOut=$p['stock']<=0;
      ?>
      <div class="card" onclick="openModal(<?=$p['product_id']?>)">
        <img src="<?=$img?>" alt="<?=htmlspecialchars($p['name'])?>">
        <div class="card-body">
          <h3><?=htmlspecialchars($p['name'])?></h3>
          <div class="seller-line">🏪 <?=htmlspecialchars($p['seller_name'])?></div>
          <div class="location-line">📍 <?=htmlspecialchars($p['stall_address']?:($p['seller_area']?:'Location not specified'))?></div>
          <div class="price">R<?=number_format($p['price'],2)?></div>
          <?php if($p['review_count']>0): ?>
          <div class="stars-display"><?=stars($p['avg_rating'])?><span>(<?=$p['review_count']?>)</span></div>
          <?php endif; ?>
          <?php if($isOut): ?>
          <div class="btn out-of-stock">Out of Stock</div>
          <?php else: ?>
          <button class="btn" onclick="event.stopPropagation();addToCart(<?=$p['product_id']?>,this)">Add to Cart 🛒</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- PRODUCT MODAL -->
<div class="modal-overlay" id="modal-overlay" onclick="closeModalOutside(event)">
  <div class="modal" id="modal-box">
    <div id="modal-content">
      <div style="padding:20px;text-align:center;color:var(--muted);">Loading...</div>
    </div>
  </div>
</div>

<script>
function openModal(id){
  document.getElementById('modal-overlay').classList.add('open');
  fetch('product_detail.php?id='+id)
    .then(r=>r.text())
    .then(html=>{document.getElementById('modal-content').innerHTML=html;});
}
function closeModal(){document.getElementById('modal-overlay').classList.remove('open');}
function closeModalOutside(e){if(e.target===document.getElementById('modal-overlay'))closeModal();}

function addToCart(id,btn){
  fetch('cart_add.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'product_id='+id})
  .then(r=>r.json()).then(d=>{
    if(d.status==='error'){alert('Please login to add items to cart.');window.location='login.php';}
    else{
      const orig=btn.textContent;
      btn.textContent='✓ Added!';btn.style.background='var(--success)';
      setTimeout(()=>{btn.textContent=orig;btn.style.background='';},1600);
    }
  });
}
</script>
<?php include 'footer.php'; ?>
