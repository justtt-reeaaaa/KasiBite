<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'){header("Location:login.php");exit;}
if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!isset($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])) die("Invalid CSRF");
    $tab=$_POST['tab']??'users';
    if(isset($_POST['approve'])){$pdo->prepare("UPDATE users SET status='approved' WHERE user_id=?")->execute([(int)$_POST['approve']]);}
    if(isset($_POST['reject'])){$pdo->prepare("UPDATE users SET status='rejected' WHERE user_id=?")->execute([(int)$_POST['reject']]);}
    if(isset($_POST['delete_user'])){$pdo->prepare("DELETE FROM users WHERE user_id=? AND role!='admin'")->execute([(int)$_POST['delete_user']]);}
    if(isset($_POST['delete_product'])){$pdo->prepare("DELETE FROM products WHERE product_id=?")->execute([(int)$_POST['delete_product']]);}
    if(isset($_POST['update_order_status'])){$pdo->prepare("UPDATE orders SET status=? WHERE order_id=?")->execute([$_POST['new_status'],(int)$_POST['order_id']]);}
    if(isset($_POST['update_dispute'])){$pdo->prepare("UPDATE disputes SET status=? WHERE dispute_id=?")->execute([$_POST['dispute_status'],(int)$_POST['dispute_id']]);$tab='disputes';}
    header("Location:admin.php?tab=$tab"); exit;
}

$tab=$_GET['tab']??'users';
$users=$pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$products=$pdo->query("SELECT p.*,c.category_name,u.full_name as seller_name FROM products p JOIN categories c ON p.category_id=c.category_id JOIN users u ON p.seller_id=u.user_id ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$orders=$pdo->query("SELECT o.*,u.full_name as buyer_name FROM orders o JOIN users u ON o.buyer_id=u.user_id ORDER BY o.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$disputes=$pdo->query("SELECT d.*,o.payment_ref,ub.full_name as buyer_name,us.full_name as seller_name FROM disputes d JOIN orders o ON d.order_id=o.order_id JOIN users ub ON d.buyer_id=ub.user_id JOIN users us ON d.seller_id=us.user_id ORDER BY d.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Pending seller for selected view
$view_seller=null;
if(isset($_GET['view_seller'])){
    $vs=$pdo->prepare("SELECT * FROM users WHERE user_id=? AND role='seller'");
    $vs->execute([(int)$_GET['view_seller']]);
    $view_seller=$vs->fetch(PDO::FETCH_ASSOC);
}

$pending_sellers=count(array_filter($users,fn($u)=>$u['role']==='seller'&&$u['status']==='pending'));
$open_disputes=count(array_filter($disputes,fn($d)=>$d['status']==='open'));

$pageTitle="Admin Dashboard"; include 'header.php';
?>
<style>
.admin-wrap{max-width:1100px;margin:25px auto;padding:0 20px;}
.admin-hdr{background:var(--dark);color:white;border-radius:12px;padding:22px 25px;margin-bottom:20px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;}
.admin-hdr h2{font-size:19px;margin-bottom:3px;}
.admin-hdr p{font-size:12px;color:#aaa;}
.stats-row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;}
.stat-card{background:white;border-radius:10px;padding:16px;flex:1;min-width:110px;box-shadow:0 2px 8px rgba(0,0,0,0.08);text-align:center;}
.stat-card .num{font-size:24px;font-weight:700;color:var(--primary);}
.stat-card .lbl{font-size:11px;color:var(--muted);}
.tabs{display:flex;gap:0;border-bottom:2px solid #eee;margin-bottom:18px;flex-wrap:wrap;}
.tab-btn{padding:9px 18px;border:none;background:none;cursor:pointer;font-family:Poppins,sans-serif;font-size:13px;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;}
.tab-btn.active{color:var(--primary);border-bottom-color:var(--primary);font-weight:600;}
.bc{display:inline-block;background:var(--danger);color:white;border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px;}
.box{background:white;border-radius:10px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
table{width:100%;border-collapse:collapse;}
th{background:var(--dark);color:white;padding:9px 12px;text-align:left;font-size:12px;}
td{padding:9px 12px;border-bottom:1px solid #f0f0f0;font-size:13px;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-approved,.badge-confirmed,.badge-active,.badge-completed{background:#d4edda;color:#155724;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-rejected,.badge-cancelled,.badge-inactive{background:#f8d7da;color:#721c24;}
.badge-open{background:#f8d7da;color:#721c24;}
.badge-reviewing{background:#fff3cd;color:#856404;}
.badge-resolved{background:#d4edda;color:#155724;}
.approve-btn{background:var(--success);color:white;padding:4px 9px;border:none;border-radius:4px;cursor:pointer;font-size:12px;margin-right:3px;}
.reject-btn{background:var(--danger);color:white;padding:4px 9px;border:none;border-radius:4px;cursor:pointer;font-size:12px;margin-right:3px;}
.delete-btn{background:#888;color:white;padding:4px 9px;border:none;border-radius:4px;cursor:pointer;font-size:12px;}
.view-btn{background:var(--primary);color:white;padding:4px 9px;border:none;border-radius:4px;cursor:pointer;font-size:12px;text-decoration:none;margin-right:3px;}
.tab-content{display:none;} .tab-content.active{display:block;}
select.ss{padding:4px 7px;border:1px solid #ccc;border-radius:4px;font-size:12px;}
/* Seller detail card */
.seller-detail{background:#fffdf0;border:1px solid #ffc107;border-radius:10px;padding:20px;margin-bottom:18px;}
.seller-detail h3{color:var(--dark);font-size:16px;margin-bottom:14px;border-bottom:2px solid var(--primary);padding-bottom:6px;}
.detail-row{display:flex;gap:8px;margin-bottom:8px;font-size:13px;}
.detail-row .lbl{font-weight:700;color:var(--dark);min-width:160px;flex-shrink:0;}
.detail-row .val{color:#555;}
.fsd-yes{background:#d4edda;color:#155724;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:700;}
.fsd-no{background:#f8d7da;color:#721c24;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:700;}
</style>

<div class="admin-wrap">
  <div class="admin-hdr">
    <div style="font-size:36px;">🛡️</div>
    <div><h2>Admin Dashboard</h2><p>KasiBite Platform Management · <?=date('d M Y')?></p></div>
    <?php if($pending_sellers>0||$open_disputes>0): ?>
    <div style="margin-left:auto;display:flex;gap:10px;flex-wrap:wrap;">
      <?php if($pending_sellers>0): ?><div style="background:var(--primary);color:white;padding:6px 14px;border-radius:8px;font-size:12px;">⏳ <?=$pending_sellers?> seller<?=$pending_sellers>1?'s':''?> pending</div><?php endif; ?>
      <?php if($open_disputes>0): ?><div style="background:var(--danger);color:white;padding:6px 14px;border-radius:8px;font-size:12px;">⚠️ <?=$open_disputes?> open dispute<?=$open_disputes>1?'s':''?></div><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="num"><?=count($users)?></div><div class="lbl">Users</div></div>
    <div class="stat-card"><div class="num"><?=count(array_filter($users,fn($u)=>$u['role']==='seller'))?></div><div class="lbl">Sellers</div></div>
    <div class="stat-card"><div class="num"><?=count($products)?></div><div class="lbl">Products</div></div>
    <div class="stat-card"><div class="num"><?=count($orders)?></div><div class="lbl">Orders</div></div>
    <div class="stat-card"><div class="num">R<?=number_format(array_sum(array_column($orders,'total_amount')),0)?></div><div class="lbl">Revenue</div></div>
    <div class="stat-card"><div class="num"><?=count($disputes)?></div><div class="lbl">Disputes</div></div>
  </div>

  <div class="tabs">
    <button class="tab-btn <?=$tab==='users'?'active':''?>" onclick="switchTab('users')">Users<?php if($pending_sellers): ?><span class="bc"><?=$pending_sellers?></span><?php endif; ?></button>
    <button class="tab-btn <?=$tab==='products'?'active':''?>" onclick="switchTab('products')">Products</button>
    <button class="tab-btn <?=$tab==='orders'?'active':''?>" onclick="switchTab('orders')">Orders</button>
    <button class="tab-btn <?=$tab==='disputes'?'active':''?>" onclick="switchTab('disputes')">Disputes<?php if($open_disputes): ?><span class="bc"><?=$open_disputes?></span><?php endif; ?></button>
  </div>

  <!-- USERS TAB -->
  <div id="tab-users" class="tab-content <?=$tab==='users'?'active':''?> box">
    <?php if($view_seller): ?>
    <div class="seller-detail">
      <h3>🔍 Seller Verification Details — <?=htmlspecialchars($view_seller['full_name'])?></h3>
      <div class="detail-row"><div class="lbl">Full Name:</div><div class="val"><?=htmlspecialchars($view_seller['full_name'])?></div></div>
      <div class="detail-row"><div class="lbl">Email:</div><div class="val"><?=htmlspecialchars($view_seller['email'])?></div></div>
      <div class="detail-row"><div class="lbl">Phone:</div><div class="val"><?=htmlspecialchars($view_seller['phone']??'Not provided')?></div></div>
      <div class="detail-row"><div class="lbl">SA ID Number:</div><div class="val"><?=htmlspecialchars($view_seller['sa_id_number']??'Not provided')?></div></div>
      <div class="detail-row"><div class="lbl">Stall Address:</div><div class="val"><?=htmlspecialchars($view_seller['stall_address']??'Not provided')?></div></div>
      <div class="detail-row"><div class="lbl">About:</div><div class="val"><?=htmlspecialchars($view_seller['bio']??'Not provided')?></div></div>
      <div class="detail-row"><div class="lbl">Food Safety Declaration:</div><div class="val"><?=$view_seller['food_safety_declaration']?'<span class="fsd-yes">✓ Agreed</span>':'<span class="fsd-no">✗ Not agreed</span>'?></div></div>
      <div class="detail-row"><div class="lbl">Registered:</div><div class="val"><?=date('d M Y, H:i',strtotime($view_seller['created_at']))?></div></div>
      <div class="detail-row"><div class="lbl">Current Status:</div><div class="val"><span class="badge badge-<?=$view_seller['status']?>"><?=ucfirst($view_seller['status'])?></span></div></div>
      <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
        <form method="POST" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
          <input type="hidden" name="tab" value="users">
          <button type="submit" name="approve" value="<?=$view_seller['user_id']?>" class="approve-btn" style="padding:7px 14px;">✓ Approve Seller</button>
          <button type="submit" name="reject" value="<?=$view_seller['user_id']?>" class="reject-btn" style="padding:7px 14px;">✗ Reject Seller</button>
        </form>
        <a href="admin.php?tab=users" style="padding:7px 14px;background:#eee;color:#333;border-radius:4px;font-size:13px;text-decoration:none;">← Back to Users</a>
      </div>
    </div>
    <?php endif; ?>

    <table>
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($users as $u): ?>
      <tr>
        <td><?=$u['user_id']?></td>
        <td><strong><?=htmlspecialchars($u['full_name'])?></strong></td>
        <td style="font-size:12px;"><?=htmlspecialchars($u['email'])?></td>
        <td><?=ucfirst($u['role'])?></td>
        <td><span class="badge badge-<?=$u['status']?>"><?=ucfirst($u['status'])?></span></td>
        <td style="font-size:12px;"><?=date('d M Y',strtotime($u['created_at']))?></td>
        <td>
          <?php if($u['role']==='seller'): ?>
          <a href="admin.php?tab=users&view_seller=<?=$u['user_id']?>" class="view-btn">View Details</a>
          <?php if($u['status']==='pending'): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="hidden" name="tab" value="users">
            <button type="submit" name="approve" value="<?=$u['user_id']?>" class="approve-btn">✓</button>
            <button type="submit" name="reject" value="<?=$u['user_id']?>" class="reject-btn">✗</button>
          </form>
          <?php endif; ?>
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
  <div id="tab-products" class="tab-content <?=$tab==='products'?'active':''?> box">
    <table>
      <thead><tr><th>Name</th><th>Seller</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach($products as $p): ?>
      <tr>
        <td><strong><?=htmlspecialchars($p['name'])?></strong></td>
        <td><?=htmlspecialchars($p['seller_name'])?></td>
        <td><?=htmlspecialchars($p['category_name'])?></td>
        <td>R<?=number_format($p['price'],2)?></td>
        <td><?=$p['stock']?></td>
        <td><span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span></td>
        <td>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
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
  <div id="tab-orders" class="tab-content <?=$tab==='orders'?'active':''?> box">
    <table>
      <thead><tr><th>Ref</th><th>Buyer</th><th>Amount</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
      <tbody>
      <?php foreach($orders as $o): ?>
      <tr>
        <td><strong><?=htmlspecialchars($o['payment_ref'])?></strong></td>
        <td><?=htmlspecialchars($o['buyer_name'])?></td>
        <td>R<?=number_format($o['total_amount'],2)?></td>
        <td><span class="badge badge-<?=$o['status']?>"><?=ucfirst($o['status'])?></span></td>
        <td style="font-size:12px;"><?=date('d M Y',strtotime($o['created_at']))?></td>
        <td>
          <form method="POST" style="display:flex;gap:5px;align-items:center;">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="hidden" name="tab" value="orders">
            <input type="hidden" name="order_id" value="<?=$o['order_id']?>">
            <select name="new_status" class="ss">
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

  <!-- DISPUTES TAB -->
  <div id="tab-disputes" class="tab-content <?=$tab==='disputes'?'active':''?> box">
    <?php if(empty($disputes)): ?>
    <p style="color:var(--muted);font-size:14px;text-align:center;padding:30px;">No disputes filed yet.</p>
    <?php else: ?>
    <table>
      <thead><tr><th>Order</th><th>Buyer</th><th>Against Seller</th><th>Reason</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
      <tbody>
      <?php foreach($disputes as $d): ?>
      <tr>
        <td><strong><?=htmlspecialchars($d['payment_ref'])?></strong></td>
        <td><?=htmlspecialchars($d['buyer_name'])?></td>
        <td><?=htmlspecialchars($d['seller_name'])?></td>
        <td style="max-width:200px;font-size:12px;"><?=htmlspecialchars(substr($d['reason'],0,80)).(strlen($d['reason'])>80?'...':'')?></td>
        <td><span class="badge badge-<?=$d['status']?>"><?=ucfirst($d['status'])?></span></td>
        <td style="font-size:12px;"><?=date('d M Y',strtotime($d['created_at']))?></td>
        <td>
          <form method="POST" style="display:flex;gap:5px;align-items:center;">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="hidden" name="tab" value="disputes">
            <input type="hidden" name="dispute_id" value="<?=$d['dispute_id']?>">
            <select name="dispute_status" class="ss">
              <option value="open" <?=$d['status']==='open'?'selected':''?>>Open</option>
              <option value="reviewing" <?=$d['status']==='reviewing'?'selected':''?>>Reviewing</option>
              <option value="resolved" <?=$d['status']==='resolved'?'selected':''?>>Resolved</option>
            </select>
            <button type="submit" name="update_dispute" class="approve-btn">Save</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
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
