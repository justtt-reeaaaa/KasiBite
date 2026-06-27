<?php
require 'config.php';
$id=(int)($_GET['id']??0);
if(!$id){echo "<div style='padding:20px;color:red;'>Product not found.</div>"; exit;}

$prod=$pdo->prepare("SELECT p.*,c.category_name,u.full_name as seller_name,u.stall_address,u.status as seller_status
    FROM products p JOIN categories c ON p.category_id=c.category_id JOIN users u ON p.seller_id=u.user_id
    WHERE p.product_id=? AND p.status='active'");
$prod->execute([$id]); $prod=$prod->fetch(PDO::FETCH_ASSOC);
if(!$prod){echo "<div style='padding:20px;color:red;'>Product not found.</div>"; exit;}

$reviews=$pdo->prepare("SELECT r.*,u.full_name as buyer_name FROM reviews r JOIN users u ON r.buyer_id=u.user_id WHERE r.product_id=? ORDER BY r.created_at DESC");
$reviews->execute([$id]); $reviews=$reviews->fetchAll(PDO::FETCH_ASSOC);
$avgRating=$reviews?round(array_sum(array_column($reviews,'rating'))/count($reviews),1):0;

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
$img=($prod['image_path']&&file_exists($prod['image_path']))?$prod['image_path']:($catImages[$prod['category_name']]??$default);
function starsHtml($r){$s='';for($i=1;$i<=5;$i++)$s.=$i<=$r?'★':'☆';return $s;}
?>
<button onclick="closeModal()" style="position:absolute;right:20px;top:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#999;z-index:10;">✕</button>
<img src="<?=$img?>" style="width:100%;height:230px;object-fit:cover;">
<div style="padding:22px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
    <div>
      <h2 style="font-size:20px;margin-bottom:5px;"><?=htmlspecialchars($prod['name'])?></h2>
      <div style="font-size:13px;color:#777;margin-bottom:5px;">
        📂 <?=htmlspecialchars($prod['category_name'])?>  &nbsp;|&nbsp;
        🏪 <strong style="color:var(--primary);"><?=htmlspecialchars($prod['seller_name'])?></strong>
        <?php if($prod['seller_status']==='approved'): ?>&nbsp;<span style="background:#d4edda;color:#155724;font-size:11px;padding:1px 7px;border-radius:10px;font-weight:700;">✓ Verified</span><?php endif; ?>
      </div>
      <?php if($prod['stall_address']): ?><div style="font-size:12px;color:#999;">📍 <?=htmlspecialchars($prod['stall_address'])?></div><?php endif; ?>
    </div>
    <div style="font-size:24px;font-weight:700;color:var(--primary);">R<?=number_format($prod['price'],2)?></div>
  </div>

  <?php if($reviews): ?>
  <div style="margin:10px 0;font-size:20px;color:#f1c40f;">
    <?=starsHtml(round($avgRating))?> <span style="font-size:13px;color:#777;"><?=$avgRating?>/5 (<?=count($reviews)?> review<?=count($reviews)!=1?'s':''?>)</span>
  </div>
  <?php endif; ?>

  <?php if($prod['stock']>0): ?>
    <div style="display:inline-block;background:#d4edda;color:#155724;padding:3px 10px;border-radius:10px;font-size:12px;font-weight:600;margin:8px 0;"><?=$prod['stock']?> portions available</div>
  <?php else: ?>
    <div style="display:inline-block;background:#f8d7da;color:#721c24;padding:3px 10px;border-radius:10px;font-size:12px;font-weight:600;margin:8px 0;">Out of Stock</div>
  <?php endif; ?>

  <?php if($prod['description']): ?>
  <p style="font-size:14px;color:#555;margin:12px 0;line-height:1.6;"><?=htmlspecialchars($prod['description'])?></p>
  <?php endif; ?>

  <?php if($prod['stock']>0): ?>
  <button onclick="addToCart(<?=$prod['product_id']?>,this)" class="btn" style="width:100%;margin-top:5px;">Add to Cart 🛒</button>
  <?php endif; ?>

  <!-- REVIEWS SECTION -->
  <div style="border-top:2px solid #eee;padding-top:18px;margin-top:20px;">
    <h4 style="font-size:15px;margin-bottom:14px;">⭐ Customer Reviews</h4>
    <?php if(empty($reviews)): ?>
    <p style="color:#999;font-size:13px;font-style:italic;">No reviews yet. Be the first to order and review this item!</p>
    <?php else: ?>
    <?php foreach($reviews as $r): ?>
    <div style="background:#f9f9f9;border-radius:8px;padding:12px;margin-bottom:10px;">
      <div style="font-size:12px;font-weight:700;color:var(--dark);margin-bottom:3px;"><?=htmlspecialchars($r['buyer_name'])?></div>
      <div style="color:#f1c40f;font-size:14px;margin-bottom:5px;"><?=starsHtml($r['rating'])?></div>
      <div style="font-size:13px;color:#555;"><?=htmlspecialchars($r['comment'])?></div>
      <div style="font-size:11px;color:#aaa;margin-top:5px;"><?=date('d M Y',strtotime($r['created_at']))?></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
