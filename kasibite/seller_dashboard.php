<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='seller'){header("Location:login.php");exit;}
if($_SESSION['status']!=='approved'){
    $pageTitle="Pending Approval"; include 'header.php';
    echo '<div style="text-align:center;padding:80px 20px;"><div style="font-size:60px;margin-bottom:20px;">⏳</div><h2 style="color:var(--primary);">Awaiting Admin Approval</h2><p style="color:var(--muted);margin:15px 0 25px;">Your seller account is under review. You will be able to access your dashboard once approved.</p><a href="logout.php" class="btn btn-dark">Logout</a></div>';
    include 'footer.php'; exit;
}
$seller_id=$_SESSION['user_id'];

// Handle add product with image upload
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['add_product'])){
    $name=trim($_POST['name']);
    $price=(float)$_POST['price'];
    $cat=(int)$_POST['category_id'];
    $desc=trim($_POST['description']);
    $stock=(int)$_POST['stock'];
    $image_path=null;

    if(isset($_FILES['product_image'])&&$_FILES['product_image']['error']===0){
        $allowed=['image/jpeg','image/png','image/webp'];
        $ftype=$_FILES['product_image']['type'];
        $fsize=$_FILES['product_image']['size'];
        if(in_array($ftype,$allowed)&&$fsize<=3145728){
            $ext=pathinfo($_FILES['product_image']['name'],PATHINFO_EXTENSION);
            $filename='prod_'.time().'_'.$seller_id.'.'.$ext;
            $dest='uploads/products/'.$filename;
            if(move_uploaded_file($_FILES['product_image']['tmp_name'],$dest)){
                $image_path=$dest;
            }
        } else {
            $_SESSION['flash']=['type'=>'error','msg'=>'Image must be JPG/PNG/WEBP and under 3MB.'];
            header("Location:seller_dashboard.php"); exit;
        }
    }

    if($name&&$price>0&&$cat&&$stock>0){
        $pdo->prepare("INSERT INTO products (seller_id,category_id,name,description,price,stock,image_path) VALUES (?,?,?,?,?,?,?)")
            ->execute([$seller_id,$cat,$name,$desc,$price,$stock,$image_path]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Product added! It is now live on the Browse page.'];
    } else {
        $_SESSION['flash']=['type'=>'error','msg'=>'Please fill in all required fields.'];
    }
    header("Location:seller_dashboard.php"); exit;
}

if(isset($_GET['delete'])){
    $pid=(int)$_GET['delete'];
    // Delete image file if exists
    $row=$pdo->prepare("SELECT image_path FROM products WHERE product_id=? AND seller_id=?");
    $row->execute([$pid,$seller_id]);
    $r=$row->fetch();
    if($r&&$r['image_path']&&file_exists($r['image_path'])) unlink($r['image_path']);
    $pdo->prepare("DELETE FROM products WHERE product_id=? AND seller_id=?")->execute([$pid,$seller_id]);
    $_SESSION['flash']=['type'=>'success','msg'=>'Product deleted.'];
    header("Location:seller_dashboard.php"); exit;
}

if(isset($_GET['toggle'])){
    $pid=(int)$_GET['toggle'];
    $pdo->prepare("UPDATE products SET status=IF(status='active','inactive','active') WHERE product_id=? AND seller_id=?")->execute([$pid,$seller_id]);
    header("Location:seller_dashboard.php"); exit;
}

$user=$pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$seller_id]);
$user=$user->fetch(PDO::FETCH_ASSOC);

$products=$pdo->prepare("SELECT p.*,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id WHERE p.seller_id=? ORDER BY p.created_at DESC");
$products->execute([$seller_id]);
$products=$products->fetchAll(PDO::FETCH_ASSOC);

$cats=$pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

$orders=$pdo->prepare("
    SELECT o.order_id,o.total_amount,o.status,o.payment_ref,o.created_at,
           u.full_name as buyer_name,
           GROUP_CONCAT(p.name SEPARATOR ', ') as items,
           GROUP_CONCAT(oi.special_instructions SEPARATOR '|||') as instructions,
           GROUP_CONCAT(oi.quantity SEPARATOR ',') as quantities
    FROM orders o
    JOIN order_items oi ON o.order_id=oi.order_id
    JOIN products p ON oi.product_id=p.product_id
    JOIN users u ON o.buyer_id=u.user_id
    WHERE p.seller_id=?
    GROUP BY o.order_id
    ORDER BY o.created_at DESC LIMIT 15
");
$orders->execute([$seller_id]);
$orders=$orders->fetchAll(PDO::FETCH_ASSOC);

$pageTitle="Seller Dashboard"; include 'header.php';
?>
<style>
.dash-wrap{max-width:1100px;margin:30px auto;padding:0 20px;}
.dash-header{display:flex;align-items:center;gap:20px;background:white;border-radius:12px;padding:22px 25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px;flex-wrap:wrap;}
.avatar{width:56px;height:56px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;color:white;font-weight:700;flex-shrink:0;}
.stats-row{display:flex;gap:15px;flex-wrap:wrap;margin-bottom:20px;}
.stat-card{background:white;border-radius:10px;padding:18px;flex:1;min-width:130px;box-shadow:0 2px 8px rgba(0,0,0,0.08);text-align:center;}
.stat-card .num{font-size:26px;font-weight:700;color:var(--primary);}
.stat-card .lbl{font-size:12px;color:var(--muted);}
.two-col{display:flex;gap:20px;flex-wrap:wrap;}
.col-main{flex:1;min-width:300px;}
.col-side{width:310px;}
.box{background:white;border-radius:10px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px;}
.box h3{font-size:15px;margin-bottom:16px;border-bottom:2px solid var(--primary);padding-bottom:8px;}
table{width:100%;border-collapse:collapse;}
th{background:var(--dark);color:white;padding:9px 12px;text-align:left;font-size:12px;}
td{padding:9px 12px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-active{background:#d4edda;color:#155724;}
.badge-inactive{background:#f8d7da;color:#721c24;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-confirmed{background:#d4edda;color:#155724;}
.badge-completed{background:#cce5ff;color:#004085;}
.badge-cancelled{background:#f8d7da;color:#721c24;}
.prod-img{width:45px;height:45px;object-fit:cover;border-radius:6px;}
.instruction-chip{background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:3px 8px;font-size:11px;color:#856404;margin-top:4px;display:inline-block;}
.file-preview{width:100%;height:120px;object-fit:cover;border-radius:6px;margin-top:6px;display:none;}
</style>
<div class="dash-wrap">

  <div class="dash-header">
    <div class="avatar"><?=strtoupper(substr($user['full_name'],0,1))?></div>
    <div>
      <div style="font-size:18px;font-weight:700;"><?=htmlspecialchars($user['full_name'])?></div>
      <div style="font-size:13px;color:var(--muted);"><?=htmlspecialchars($user['email'])?></div>
      <?php if($user['stall_address']): ?><div style="font-size:12px;color:var(--muted);">📍 <?=htmlspecialchars($user['stall_address'])?></div><?php endif; ?>
    </div>
    <span class="badge badge-active" style="margin-left:auto;font-size:12px;padding:5px 12px;">✓ Verified Seller</span>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="num"><?=count($products)?></div><div class="lbl">Products</div></div>
    <div class="stat-card"><div class="num"><?=count($orders)?></div><div class="lbl">Orders</div></div>
    <div class="stat-card"><div class="num"><?=count(array_filter($products,fn($p)=>$p['status']==='active'))?></div><div class="lbl">Active</div></div>
    <div class="stat-card"><div class="num">R<?=number_format(array_sum(array_column($orders,'total_amount')),0)?></div><div class="lbl">Revenue</div></div>
  </div>

  <div class="two-col">
    <div class="col-main">

      <!-- PRODUCTS -->
      <div class="box">
        <h3>🍽️ My Products</h3>
        <?php if(empty($products)): ?>
        <p style="color:var(--muted);font-size:14px;">No products yet. Add your first listing using the form →</p>
        <?php else: ?>
        <table>
          <thead><tr><th>Photo</th><th>Name</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($products as $p): ?>
          <tr>
            <td>
              <?php if($p['image_path']&&file_exists($p['image_path'])): ?>
                <img src="<?=$p['image_path']?>" class="prod-img">
              <?php else: ?>
                <div style="width:45px;height:45px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:18px;">🍽️</div>
              <?php endif; ?>
            </td>
            <td><strong><?=htmlspecialchars($p['name'])?></strong><br><small style="color:var(--muted);"><?=htmlspecialchars($p['category_name'])?></small></td>
            <td>R<?=number_format($p['price'],2)?></td>
            <td><?=$p['stock']?></td>
            <td><span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span></td>
            <td>
              <a href="?toggle=<?=$p['product_id']?>" style="color:var(--primary);font-size:12px;display:block;margin-bottom:4px;"><?=$p['status']==='active'?'Deactivate':'Activate'?></a>
              <a href="?delete=<?=$p['product_id']?>" style="color:var(--danger);font-size:12px;" onclick="return confirm('Delete this product?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- ORDERS WITH INSTRUCTIONS -->
      <div class="box">
        <h3>📦 Incoming Orders & Buyer Instructions</h3>
        <?php if(empty($orders)): ?>
        <p style="color:var(--muted);font-size:14px;">No orders yet.</p>
        <?php else: ?>
        <table>
          <thead><tr><th>Ref</th><th>Buyer</th><th>Items & Instructions</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($orders as $o):
            $instArr=explode('|||',$o['instructions']??'');
            $itemArr=explode(',',$o['items']??'');
          ?>
          <tr>
            <td><strong style="font-size:12px;"><?=htmlspecialchars($o['payment_ref'])?></strong><br><small style="color:var(--muted);"><?=date('d M',strtotime($o['created_at']))?></small></td>
            <td><?=htmlspecialchars($o['buyer_name'])?></td>
            <td>
              <?php for($i=0;$i<count($itemArr);$i++):
                $inst=trim($instArr[$i]??'');
              ?>
                <div style="margin-bottom:4px;">• <?=htmlspecialchars($itemArr[$i])?></div>
                <?php if($inst): ?>
                  <div class="instruction-chip">📝 <?=htmlspecialchars($inst)?></div>
                <?php endif; ?>
              <?php endfor; ?>
            </td>
            <td>R<?=number_format($o['total_amount'],2)?></td>
            <td><span class="badge badge-<?=$o['status']?>"><?=ucfirst($o['status'])?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- ADD PRODUCT FORM -->
    <div class="col-side">
      <div class="box">
        <h3>➕ Add New Product</h3>
        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label>Product Name <span style="color:var(--danger)">*</span></label>
            <input type="text" name="name" placeholder="e.g. Pap ne Nyama" required>
          </div>
          <div class="form-group">
            <label>Category <span style="color:var(--danger)">*</span></label>
            <select name="category_id" required>
              <option value="">-- Select Category --</option>
              <?php foreach($cats as $c): ?>
              <option value="<?=$c['category_id']?>"><?=htmlspecialchars($c['category_name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Describe your food..." style="resize:vertical;"></textarea>
          </div>
          <div class="form-group">
            <label>Price (R) <span style="color:var(--danger)">*</span></label>
            <input type="number" name="price" placeholder="45.00" step="0.01" min="1" required>
          </div>
          <div class="form-group">
            <label>Stock / Servings Available <span style="color:var(--danger)">*</span></label>
            <input type="number" name="stock" placeholder="20" min="1" value="10" required>
          </div>
          <div class="form-group">
            <label>Product Photo (JPG/PNG/WEBP, max 3MB)</label>
            <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" onchange="previewImg(this)">
            <img id="img-preview" class="file-preview" alt="Preview">
            <p style="font-size:11px;color:var(--muted);margin-top:4px;">If no photo is uploaded, a category default image will be used.</p>
          </div>
          <button type="submit" name="add_product" class="btn btn-full">Add Product</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function previewImg(input){
  const preview=document.getElementById('img-preview');
  if(input.files&&input.files[0]){
    const reader=new FileReader();
    reader.onload=e=>{preview.src=e.target.result;preview.style.display='block';};
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
<?php include 'footer.php'; ?>
