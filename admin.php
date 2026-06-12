<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'){header("Location:login.php");exit;}

if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));

// Handle actions
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!isset($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])) die("Invalid CSRF token");
    if(isset($_POST['approve'])){$pdo->prepare("UPDATE users SET status='approved' WHERE user_id=?")->execute([(int)$_POST['approve']]);}
    if(isset($_POST['reject'])){$pdo->prepare("UPDATE users SET status='rejected' WHERE user_id=?")->execute([(int)$_POST['reject']]);}
    if(isset($_POST['delete_user'])){$pdo->prepare("DELETE FROM users WHERE user_id=? AND role!='admin'")->execute([(int)$_POST['delete_user']]);}
    if(isset($_POST['delete_product'])){$pdo->prepare("DELETE FROM products WHERE product_id=?")->execute([(int)$_POST['delete_product']]);}
    if(isset($_POST['update_order_status'])){
        $pdo->prepare("UPDATE orders SET status=? WHERE order_id=?")->execute([$_POST['new_status'],(int)$_POST['order_id']]);
    }
    header("Location:admin.php?tab=".($_POST['tab']??'users'));exit;
}

$tab=$_GET['tab']??'users';
$users=$pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$products=$pdo->query("SELECT p.*,c.category_name,u.full_name as seller_name FROM products p JOIN categories c ON p.category_id=c.category_id JOIN users u ON p.seller_id=u.user_id ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$orders=$pdo->query("SELECT o.*,u.full_name as buyer_name FROM orders o JOIN users u ON o.buyer_id=u.user_id ORDER BY o.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$pending_sellers=count(array_filter($users,fn($u)=>$u['role']==='seller'&&$u['status']==='pending'));

$pageTitle="Admin Dashboard"; include 'header.php';
?>
<style>
.admin-wrap{max-width:1100px;margin:30px auto;padding:0 20px;}
.admin-header{background:var(--dark);color:white;border-radius:12px;padding:25px;margin-bottom:25px;display:flex;align-items:center;gap:20px;}
.admin-header h2{font-size:20px;margin-bottom:4px;}
.admin-header p{font-size:13px;color:#aaa;}
.stats-row{display:flex;gap:15px;flex-wrap:wrap;margin-bottom:25px;}
.stat-card{background:white;border-radius:10px;padding:18px;flex:1;min-width:120px;box-shadow:0 2px 8px rgba(0,0,0,0.08);text-align:center;}
.stat-card .num{font-size:26px;font-weight:700;color:var(--primary);}
.stat-card .label{font-size:12px;color:var(--muted);}
.tabs{display:flex;gap:0;border-bottom:2px solid #eee;margin-bottom:20px;}
.tab-btn{padding:10px 22px;border:none;background:none;cursor:pointer;font-family:Poppins;font-size:14px;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;transition:0.2s;}
.tab-btn.active{color:var(--primary);border-bottom-color:var(--primary);font-weight:600;}
.tab-btn .badge-count{background:var(--danger);color:white;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:6px;}
.section-box{background:white;border-radius:10px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
table{width:100%;border-collapse:collapse;}
th{background:var(--dark);color:white;padding:10px 14px;text-align:left;font-size:12px;}
td{padding:10px 14px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-approved,.badge-confirmed,.badge-active,.badge-completed{background:#d4edda;color:#155724;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-rejected,.badge-cancelled,.badge-inactive{background:#f8d7da;color:#721c24;}
.approve-btn{background:var(--success);color:white;padding:5px 10px;border:none;border-radius:4px;cursor:pointer;font-size:12px;margin-right:4px;}
.reject-btn{background:var(--danger);color:white;padding:5px 10px;border:none;border-radius:4px;cursor:pointer;font-size:12px;margin-right:4px;}
.delete-btn{background:#888;color:white;padding:5px 10px;border:none;border-radius:4px;cursor:pointer;font-size:12px;}
.tab-content{display:none;} .tab-content.active{display:block;}
select.status-sel{padding:4px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;}
</style>
<div class="admin-wrap">
  <div class="admin-header">
    <div style="font-size:40px;">🛡️</div>
    <div>
      <h2>Admin Dashboard</h2>
      <p>KasiBite Platform Management · <?=date('d M Y')?></p>
    </div>
    <?php if($pending_sellers>0): ?>
    <div style="background:var(--danger);color:white;padding:8px 16px;border-radius:8px;margin-left:auto;font-size:13px;">
      ⚠️ <?=$pending_sellers?> seller<?=$pending_sellers>1?'s':''?> awaiting approval
    </div>
    <?php endif; ?>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="num"><?=count($users)?></div><div class="label">Total Users</div></div>
    <div class="stat-card"><div class="num"><?=count(array_filter($users,fn($u)=>$u['role']==='seller'))?></div><div class="label">Sellers</div></div>
    <div class="stat-card"><div class="num"><?=count($products)?></div><div class="label">Products</div></div>
    <div class="stat-card"><div class="num"><?=count($orders)?></div><div class="label">Orders</div></div>
    <div class="stat-card"><div class="num">R<?=number_format(array_sum(array_column($orders,'total_amount')),2)?></div><div class="label">Revenue</div></div>
  </div>

  <div class="tabs">
    <button class="tab-btn <?=$tab==='users'?'active':''?>" onclick="switchTab('users')">Users <?php if($pending_sellers>0): ?><span class="badge-count"><?=$pending_sellers?></span><?php endif; ?></button>
    <button class="tab-btn <?=$tab==='products'?'active':''?>" onclick="switchTab('products')">Products</button>
    <button class="tab-btn <?=$tab==='orders'?'active':''?>" onclick="switchTab('orders')">Orders</button>
  </div>

  <!-- USERS TAB -->
  <div id="tab-users" class="tab-content <?=$tab==='users'?'active':''?> section-box">
    <table>
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($users as $u): ?>
      <tr>
        <td><?=$u['user_id']?></td>
        <td><strong><?=htmlspecialchars($u['full_name'])?></strong></td>
        <td><?=htmlspecialchars($u['email'])?></td>
        <td><?=ucfirst($u['role'])?></td>
        <td><span class="badge badge-<?=$u['status']?>"><?=ucfirst($u['status'])?></span></td>
        <td><?=date('d M Y',strtotime($u['created_at']))?></td>
        <td>
          <?php if($u['role']==='seller'&&$u['status']==='pending'): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="hidden" name="tab" value="users">
            <button type="submit" name="approve" value="<?=$u['user_id']?>" class="approve-btn">✓ Approve</button>
            <button type="submit" name="reject" value="<?=$u['user_id']?>" class="reject-btn">✗ Reject</button>
          </form>
          <?php elseif($u['role']!=='admin'): ?>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?')">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="hidden" name="tab" value="users">
            <button type="submit" name="delete_user" value="<?=$u['user_id']?>" class="delete-btn">Delete</button>
          </form>
          <?php else: ?><span style="color:var(--muted);font-size:12px;">Admin</span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- PRODUCTS TAB -->
  <div id="tab-products" class="tab-content <?=$tab==='products'?'active':''?> section-box">
    <table>
      <thead><tr><th>ID</th><th>Name</th><th>Seller</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($products as $p): ?>
      <tr>
        <td><?=$p['product_id']?></td>
        <td><strong><?=htmlspecialchars($p['name'])?></strong></td>
        <td><?=htmlspecialchars($p['seller_name'])?></td>
        <td><?=htmlspecialchars($p['category_name'])?></td>
        <td>R<?=number_format($p['price'],2)?></td>
        <td><span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span></td>
        <td>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?')">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="hidden" name="tab" value="products">
            <button type="submit" name="delete_product" value="<?=$p['product_id']?>" class="delete-btn">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ORDERS TAB -->
  <div id="tab-orders" class="tab-content <?=$tab==='orders'?'active':''?> section-box">
    <table>
      <thead><tr><th>Ref</th><th>Buyer</th><th>Amount</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
      <tbody>
      <?php foreach($orders as $o): ?>
      <tr>
        <td><strong><?=htmlspecialchars($o['payment_ref'])?></strong></td>
        <td><?=htmlspecialchars($o['buyer_name'])?></td>
        <td>R<?=number_format($o['total_amount'],2)?></td>
        <td><span class="badge badge-<?=$o['status']?>"><?=ucfirst($o['status'])?></span></td>
        <td><?=date('d M Y',strtotime($o['created_at']))?></td>
        <td>
          <form method="POST" style="display:flex;gap:5px;align-items:center;">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="hidden" name="tab" value="orders">
            <input type="hidden" name="order_id" value="<?=$o['order_id']?>">
            <select name="new_status" class="status-sel">
              <option value="pending" <?=$o['status']==='pending'?'selected':''?>>Pending</option>
              <option value="confirmed" <?=$o['status']==='confirmed'?'selected':''?>>Confirmed</option>
              <option value="completed" <?=$o['status']==='completed'?'selected':''?>>Completed</option>
              <option value="cancelled" <?=$o['status']==='cancelled'?'selected':''?>>Cancelled</option>
            </select>
            <button type="submit" name="update_order_status" class="approve-btn">Save</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function switchTab(name){
  document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(t=>t.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  event.target.classList.add('active');
}
</script>
<?php include 'footer.php'; ?>
