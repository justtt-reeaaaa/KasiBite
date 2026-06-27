<?php
session_start();
$role = $_GET['role'] ?? null;
if(!$role || !in_array($role,['buyer','seller'])){header("Location:register.php");exit;}
$_SESSION['reg_role'] = $role;
$pageTitle = "Register as ".ucfirst($role);
include 'header.php';
?>
<style>
.auth-wrap{display:flex;justify-content:center;padding:40px 20px;}
.auth-box{background:white;padding:35px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.1);width:100%;max-width:520px;}
.auth-box h2{font-size:22px;margin-bottom:6px;}
.auth-box .sub{font-size:13px;color:var(--muted);margin-bottom:20px;}
.auth-box .sub a{color:var(--primary);font-weight:600;}
.role-badge{display:inline-block;background:var(--primary);color:white;padding:3px 12px;border-radius:20px;font-size:12px;margin-bottom:16px;}
.seller-note{background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:10px 14px;font-size:12px;color:#856404;margin-bottom:18px;}
.section-label{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin:20px 0 10px;border-top:1px solid #eee;padding-top:14px;}
.declaration-box{background:#f9f9f9;border:1px solid #ddd;border-radius:6px;padding:12px;font-size:12px;color:#555;margin-bottom:12px;}
.checkbox-row{display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;}
.checkbox-row input{margin-top:3px;accent-color:var(--primary);}
.checkbox-row label{font-size:13px;color:var(--dark);}
.password-wrap{position:relative;}
.password-wrap input{padding-right:40px;}
.toggle-pw{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;padding:0;}
.strength-bar{height:4px;border-radius:2px;margin-top:4px;transition:width 0.3s,background 0.3s;width:0;}
.strength-text{font-size:11px;margin-top:2px;}
</style>

<div class="auth-wrap">
<div class="auth-box">
  <h2>Create Account</h2>
  <p class="sub">Already have one? <a href="login.php">Log in here</a></p>
  <div class="role-badge"><?=ucfirst($role)?> Account</div>

  <?php if($role==='seller'): ?>
  <div class="seller-note">
    ⏳ <strong>Seller accounts require admin verification.</strong> Please complete all fields accurately — the admin will review your details before approving your account.
  </div>
  <?php endif; ?>

  <form action="register_user.php" method="POST" enctype="multipart/form-data">

    <!-- Basic info (both roles) -->
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="name" placeholder="e.g. Thandi Mokoena" required>
    </div>
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="your@email.com" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <div class="password-wrap">
        <input type="password" name="password" id="pw-field" placeholder="Min. 8 characters" required minlength="8" oninput="checkStrength(this.value)">
        <button type="button" class="toggle-pw" onclick="togglePw()">👁️</button>
      </div>
      <div class="strength-bar" id="strength-bar"></div>
      <div class="strength-text" id="strength-text"></div>
    </div>
    <div class="form-group">
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" id="pw-confirm" placeholder="Repeat your password" required>
    </div>

    <?php if($role==='seller'): ?>
    <!-- Extended seller verification fields -->
    <div class="section-label">📋 Seller Verification Details</div>
    <div class="form-group">
      <label>Phone Number <span style="color:var(--danger)">*</span></label>
      <input type="tel" name="phone" placeholder="e.g. 071 234 5678" required>
    </div>
    <div class="form-group">
      <label>SA ID Number <span style="color:var(--danger)">*</span></label>
      <input type="text" name="sa_id_number" placeholder="13-digit SA ID number" maxlength="13" required>
    </div>
    <div class="form-group">
      <label>Stall / Trading Address <span style="color:var(--danger)">*</span></label>
      <input type="text" name="stall_address" placeholder="e.g. Stall 12, Soweto Taxi Rank, Johannesburg" required>
    </div>
    <div class="form-group">
      <label>About You & Your Food <span style="color:var(--danger)">*</span></label>
      <textarea name="bio" rows="3" placeholder="Describe yourself, your food, and how long you've been selling..." required style="resize:vertical;"></textarea>
    </div>

    <div class="section-label">✅ Food Safety Declaration</div>
    <div class="declaration-box">
      By ticking below, you confirm that: (1) All food you sell is prepared in a hygienic environment. (2) You comply with applicable South African food safety regulations. (3) The information provided above is accurate and truthful. (4) You understand that providing false information may result in account rejection or removal.
    </div>
    <div class="checkbox-row">
      <input type="checkbox" name="food_safety_declaration" id="fsd" value="1" required>
      <label for="fsd"><strong>I agree to the food safety declaration above</strong></label>
    </div>
    <?php else: ?>
    <input type="hidden" name="phone" value="">
    <input type="hidden" name="sa_id_number" value="">
    <input type="hidden" name="stall_address" value="">
    <input type="hidden" name="bio" value="">
    <?php endif; ?>

    <button type="submit" class="btn btn-full" onclick="return validateForm()">
      Register as <?=ucfirst($role)?>
    </button>
  </form>
</div>
</div>

<script>
function togglePw(){
  const f=document.getElementById('pw-field');
  f.type=f.type==='password'?'text':'password';
}
function checkStrength(val){
  const bar=document.getElementById('strength-bar');
  const txt=document.getElementById('strength-text');
  let score=0;
  if(val.length>=8) score++;
  if(/[A-Z]/.test(val)) score++;
  if(/[0-9]/.test(val)) score++;
  if(/[^A-Za-z0-9]/.test(val)) score++;
  const levels=[
    {w:'0%',c:'transparent',t:''},
    {w:'25%',c:'#e74c3c',t:'Weak'},
    {w:'50%',c:'#e67e22',t:'Fair'},
    {w:'75%',c:'#f1c40f',t:'Good'},
    {w:'100%',c:'#27ae60',t:'Strong'},
  ];
  bar.style.width=levels[score].w;
  bar.style.background=levels[score].c;
  txt.textContent=levels[score].t;
  txt.style.color=levels[score].c;
}
function validateForm(){
  const pw=document.getElementById('pw-field').value;
  const cf=document.getElementById('pw-confirm').value;
  if(pw!==cf){alert('Passwords do not match!');return false;}
  if(pw.length<8){alert('Password must be at least 8 characters.');return false;}
  return true;
}
</script>
<?php include 'footer.php'; ?>
