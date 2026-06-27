<?php
session_start();
require 'config.php';
require_once 'helpers.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer'){
    header("Location:login.php");
    exit;
}

$buyer_id = (int)$_SESSION['user_id'];
$product_id = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, u.full_name AS seller_name FROM products p JOIN users u ON p.seller_id=u.user_id WHERE p.product_id=?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product){
    $_SESSION['flash']=['type'=>'error','msg'=>'Product not found.'];
    header("Location:browse.php");
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $message = trim($_POST['message'] ?? '');
    if($message){
        $pdo->prepare("INSERT INTO messages (buyer_id,seller_id,product_id,message) VALUES (?,?,?,?)")
            ->execute([$buyer_id,$product['seller_id'],$product_id,$message]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Message sent to seller.'];
        header("Location:browse.php");
        exit;
    }
}

$pageTitle="Message Seller";
include 'header.php';
?>
<style>
.wrap{max-width:560px;margin:45px auto;padding:0 20px;}
.box{background:white;border-radius:12px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
.box h2{font-size:22px;margin-bottom:8px;}
.box p{font-size:13px;color:var(--muted);margin-bottom:20px;}
</style>
<div class="wrap">
  <div class="box">
    <h2>Message Seller</h2>
    <p>Ask <?=e($product['seller_name'])?> about <?=e($product['name'])?> before ordering.</p>
    <form method="POST">
      <input type="hidden" name="product_id" value="<?=$product_id?>">
      <div class="form-group">
        <label>Your Message</label>
        <textarea name="message" rows="5" placeholder="Ask about ingredients, availability, collection time, or delivery..." required></textarea>
      </div>
      <button class="btn btn-full" type="submit">Send Message</button>
    </form>
  </div>
</div>
<?php include 'footer.php'; ?>
