<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='buyer'){header("Location:login.php");exit;}
$user_id=$_SESSION['user_id'];
$stmt=$pdo->prepare("SELECT c.cart_id,c.quantity,c.special_instructions,p.product_id,p.name,p.price,p.stock FROM cart c JOIN products p ON c.product_id=p.product_id WHERE c.user_id=? AND p.status='active' AND p.stock > 0");
$stmt->execute([$user_id]);
$items=$stmt->fetchAll(PDO::FETCH_ASSOC);
if(empty($items)){header("Location:cart.php");exit;}
$total=0; foreach($items as $i) $total+=$i['price']*$i['quantity'];
$pageTitle="Checkout"; include 'header.php';
?>
<style>
.checkout-wrap{max-width:900px;margin:40px auto;padding:0 20px;display:flex;gap:30px;flex-wrap:wrap;}
.checkout-left{flex:1;min-width:280px;}
.checkout-right{width:300px;}
.section-box{background:white;border-radius:10px;padding:25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px;}
.section-box h3{font-size:16px;margin-bottom:18px;border-bottom:2px solid var(--primary);padding-bottom:8px;}
.payment-option{border:2px solid var(--border);border-radius:8px;padding:12px 16px;cursor:pointer;display:flex;align-items:center;gap:12px;font-size:14px;transition:border-color 0.2s;margin-bottom:10px;}
.payment-option:has(input:checked){border-color:var(--primary);background:#fff8f3;}
.sim-note{background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:10px 14px;font-size:12px;color:#856404;margin-top:10px;}
.order-item{display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid #f5f5f5;}
.total-row{display:flex;justify-content:space-between;font-weight:700;font-size:16px;margin-top:12px;border-top:2px solid #eee;padding-top:12px;}
.total-row span:last-child{color:var(--primary);}
</style>
<div class="checkout-wrap">
  <div class="checkout-left">
    <div class="section-box">
      <h3>📍 Collection Details</h3>
      <div class="form-group"><label>Full Name</label><input type="text" value="<?=htmlspecialchars($_SESSION['full_name'])?>" readonly></div>
      <div class="form-group"><label>Delivery / Collection Notes</label><input type="text" name="delivery_notes" form="checkout-form" placeholder="e.g. Collect at seller stall, Soweto Taxi Rank"></div>
      <div class="form-group"><label>Phone Number</label><input type="tel" id="phone" placeholder="e.g. 071 234 5678"></div>
    </div>
    <div class="section-box">
      <h3>💳 Payment Method</h3>
      <label class="payment-option"><input type="radio" name="payment" value="card" checked> 💳 Card Payment (Simulated)</label>
      <label class="payment-option"><input type="radio" name="payment" value="eft"> 🏦 EFT / Bank Transfer (Simulated)</label>
      <label class="payment-option"><input type="radio" name="payment" value="cash"> 💵 Cash on Collection</label>
      <div id="card-fields">
        <div class="form-group"><label>Card Number</label><input type="text" id="card_num" placeholder="1234 5678 9012 3456" maxlength="19"></div>
        <div style="display:flex;gap:12px;">
          <div class="form-group" style="flex:1;"><label>Expiry</label><input type="text" placeholder="MM/YY" maxlength="5"></div>
          <div class="form-group" style="flex:1;"><label>CVV</label><input type="text" placeholder="123" maxlength="3"></div>
        </div>
      </div>
      
    </div>
  </div>
  <div class="checkout-right">
    <div class="section-box">
      <h3>🧾 Order Summary</h3>
      <?php foreach($items as $i): ?>
      <div class="order-item"><span><?=htmlspecialchars($i['name'])?> x<?=$i['quantity']?></span><span>R<?=number_format($i['price']*$i['quantity'],2)?></span></div>
      <?php if(!empty($i['special_instructions'])): ?><div style="font-size:12px;color:var(--muted);margin-bottom:6px;">Note: <?=htmlspecialchars($i['special_instructions'])?></div><?php endif; ?>
      <?php endforeach; ?>
      <div class="order-item"><span style="color:var(--muted);">Delivery</span><span style="color:var(--success);">Free</span></div>
      <div class="total-row"><span>Total</span><span>R<?=number_format($total,2)?></span></div>
      <form action="place_order.php" method="POST" id="checkout-form" style="margin-top:20px;">
        <input type="hidden" name="total" value="<?=$total?>">
        <input type="hidden" name="payment_method" id="pay_method_input" value="card">
        <button type="button" onclick="simulatePayment()" class="btn btn-full" id="pay-btn">Pay R<?=number_format($total,2)?> →</button>
      </form>
    </div>
  </div>
</div>
<script>
document.querySelectorAll('input[name=payment]').forEach(r=>{
  r.addEventListener('change',function(){
    document.getElementById('pay_method_input').value=this.value;
    document.getElementById('card-fields').style.display=this.value==='card'?'block':'none';
  });
});
document.getElementById('card_num').addEventListener('input',function(){
  let v=this.value.replace(/\D/g,'').substring(0,16);
  this.value=v.replace(/(.{4})/g,'$1 ').trim();
});
function simulatePayment(){
  const btn=document.getElementById('pay-btn');
  btn.textContent='Processing...';btn.disabled=true;btn.style.background='#888';
  setTimeout(()=>document.getElementById('checkout-form').submit(),2000);
}
</script>
<?php include 'footer.php'; ?>
