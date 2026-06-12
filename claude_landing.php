<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>KasiBite — Umlilo Wokudla</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
/* ── VARIABLES ─────────────────────────── */
:root{
  --fire:    #D94F00;
  --ember:   #F28C1E;
  --sun:     #F5C842;
  --earth:   #2B1A0E;
  --soil:    #3D2410;
  --clay:    #7A3B1E;
  --cream:   #FDF3E3;
  --green:   #2D6A2D;
  --pattern: #C04000;
}

*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}

body{
  font-family:'Nunito',sans-serif;
  background:var(--earth);
  color:var(--cream);
  overflow-x:hidden;
}

/* ── AFRICAN PATTERN BORDER ────────────── */
.kente-bar{
  height:10px;
  background:repeating-linear-gradient(
    90deg,
    var(--fire) 0px,    var(--fire) 20px,
    var(--sun)  20px,   var(--sun)  40px,
    var(--green) 40px,  var(--green) 60px,
    var(--ember) 60px,  var(--ember) 80px,
    var(--earth) 80px,  var(--earth) 100px
  );
}

/* ── NAVBAR ────────────────────────────── */
nav{
  position:sticky;top:0;z-index:200;
  display:flex;align-items:center;
  justify-content:space-between;
  padding:0 6%;height:70px;
  background:rgba(43,26,14,0.97);
  backdrop-filter:blur(12px);
  border-bottom:2px solid var(--fire);
}

.logo{
  font-family:'Bebas Neue',sans-serif;
  font-size:2rem;letter-spacing:2px;
  color:var(--cream);
}
.logo em{color:var(--ember);font-style:normal;}

.nav-links{display:flex;align-items:center;gap:32px;}
.nav-links a{
  color:#C4956A;font-size:0.9rem;font-weight:700;
  text-decoration:none;letter-spacing:0.5px;
  text-transform:uppercase;transition:color .2s;
}
.nav-links a:hover{color:var(--sun);}

.nav-cta{display:flex;gap:10px;}
.btn-nav-outline{
  padding:8px 20px;border-radius:4px;
  border:2px solid var(--ember);
  color:var(--ember);font-weight:700;
  font-size:0.85rem;text-decoration:none;
  letter-spacing:0.5px;transition:all .2s;
}
.btn-nav-outline:hover{background:var(--ember);color:var(--earth);}
.btn-nav-solid{
  padding:8px 20px;border-radius:4px;
  background:var(--fire);color:white;
  font-weight:700;font-size:0.85rem;
  text-decoration:none;letter-spacing:0.5px;
  transition:all .2s;border:2px solid var(--fire);
}
.btn-nav-solid:hover{background:var(--ember);border-color:var(--ember);}

/* ── HERO ──────────────────────────────── */
.hero{
  position:relative;
  min-height:92vh;
  display:grid;
  grid-template-columns:1fr 1fr;
  align-items:center;
  overflow:hidden;
}

/* Textured dark bg */
.hero::before{
  content:'';position:absolute;inset:0;
  background:
    radial-gradient(ellipse 70% 90% at 30% 50%, rgba(217,79,0,0.15) 0%, transparent 65%),
    radial-gradient(ellipse 50% 70% at 80% 30%, rgba(242,140,30,0.08) 0%, transparent 60%),
    var(--earth);
  z-index:0;
}

/* Geometric African pattern overlay */
.hero-pattern{
  position:absolute;inset:0;z-index:1;opacity:0.04;
  background-image:
    repeating-linear-gradient(0deg,   transparent, transparent 39px, rgba(255,255,255,.5) 39px, rgba(255,255,255,.5) 40px),
    repeating-linear-gradient(90deg,  transparent, transparent 39px, rgba(255,255,255,.5) 39px, rgba(255,255,255,.5) 40px);
  background-size:40px 40px;
}

.hero-left{
  position:relative;z-index:2;
  padding:80px 6%;
}

.hero-tag{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(217,79,0,0.18);
  border:1.5px solid rgba(217,79,0,0.5);
  color:var(--ember);
  padding:7px 18px;border-radius:3px;
  font-size:0.78rem;font-weight:800;
  letter-spacing:2px;text-transform:uppercase;
  margin-bottom:28px;
}

.hero h1{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(3.2rem,6vw,5.5rem);
  line-height:0.95;
  letter-spacing:2px;
  color:var(--cream);
  margin-bottom:24px;
}

.hero h1 .line-fire{
  color:var(--fire);
  display:block;
  -webkit-text-stroke:1px var(--ember);
  text-shadow:0 0 40px rgba(217,79,0,0.5);
}
.hero h1 .line-gold{
  color:var(--sun);display:block;
}
.hero h1 .line-white{display:block;}

.hero-sub{
  font-size:1.05rem;color:#C4956A;
  line-height:1.7;max-width:440px;
  margin-bottom:40px;font-weight:500;
}

.hero-sub strong{color:var(--sun);}

.hero-actions{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:56px;}

.btn-fire{
  display:inline-flex;align-items:center;gap:10px;
  padding:15px 36px;border-radius:4px;
  background:var(--fire);color:white;
  font-family:'Nunito',sans-serif;
  font-size:1rem;font-weight:800;
  text-decoration:none;letter-spacing:0.5px;
  text-transform:uppercase;
  border:2px solid var(--fire);
  transition:all .25s;
  box-shadow:0 6px 24px rgba(217,79,0,0.4);
}
.btn-fire:hover{
  background:transparent;color:var(--fire);
  transform:translateY(-3px);
  box-shadow:0 10px 32px rgba(217,79,0,0.3);
}

.btn-ghost{
  display:inline-flex;align-items:center;gap:10px;
  padding:15px 36px;border-radius:4px;
  background:transparent;
  border:2px solid rgba(196,149,106,0.4);
  color:#C4956A;
  font-family:'Nunito',sans-serif;
  font-size:1rem;font-weight:700;
  text-decoration:none;
  text-transform:uppercase;letter-spacing:0.5px;
  transition:all .25s;
}
.btn-ghost:hover{
  border-color:var(--sun);color:var(--sun);
  transform:translateY(-3px);
}

/* Stats row */
.hero-stats{
  display:flex;gap:0;
}
.stat{
  padding:20px 32px;
  border-right:1px solid rgba(196,149,106,0.2);
}
.stat:first-child{padding-left:0;}
.stat:last-child{border-right:none;}
.stat-num{
  font-family:'Bebas Neue',sans-serif;
  font-size:2.2rem;letter-spacing:2px;
  color:var(--ember);line-height:1;
}
.stat-label{
  font-size:0.75rem;color:#7A5A3A;
  font-weight:700;text-transform:uppercase;
  letter-spacing:1px;margin-top:4px;
}

/* ── HERO RIGHT — IMAGE COLLAGE ─────────── */
.hero-right{
  position:relative;z-index:2;
  height:100%;min-height:92vh;
  display:flex;align-items:center;
  justify-content:center;
  padding:40px 5% 40px 0;
}

.img-collage{
  position:relative;
  width:100%;max-width:500px;
  height:520px;
}

.img-card{
  position:absolute;
  border-radius:8px;overflow:hidden;
  box-shadow:0 20px 60px rgba(0,0,0,0.5);
  border:3px solid var(--soil);
}

.img-card img{
  width:100%;height:100%;
  object-fit:cover;display:block;
  transition:transform .4s;
}
.img-card:hover img{transform:scale(1.05);}

.img-card-1{
  width:280px;height:320px;
  top:0;left:40px;
  border-color:var(--fire);
  z-index:3;
}
.img-card-2{
  width:220px;height:240px;
  top:60px;right:0;
  z-index:2;
  border-color:var(--ember);
}
.img-card-3{
  width:200px;height:180px;
  bottom:0;left:0;
  z-index:4;
  border-color:var(--sun);
}

.img-label{
  position:absolute;bottom:0;left:0;right:0;
  background:linear-gradient(0deg,rgba(43,26,14,0.95),transparent);
  padding:20px 14px 10px;
  font-size:0.78rem;font-weight:800;
  color:var(--sun);text-transform:uppercase;
  letter-spacing:1px;
}

.floating-badge{
  position:absolute;
  background:var(--fire);color:white;
  font-family:'Bebas Neue',sans-serif;
  font-size:1.1rem;letter-spacing:1.5px;
  padding:10px 18px;border-radius:4px;
  box-shadow:0 8px 24px rgba(217,79,0,0.5);
  z-index:10;
  animation:float 3s ease-in-out infinite;
}
.badge-1{top:20px;right:30px;}
.badge-2{bottom:100px;right:20px;background:var(--green);}

@keyframes float{
  0%,100%{transform:translateY(0);}
  50%{transform:translateY(-8px);}
}

/* ── MARQUEE STRIP ─────────────────────── */
.marquee-section{
  background:var(--fire);
  padding:14px 0;overflow:hidden;
  border-top:3px solid var(--ember);
  border-bottom:3px solid var(--ember);
}
.marquee-track{
  display:flex;gap:0;
  animation:marquee 20s linear infinite;
  white-space:nowrap;
}
.marquee-item{
  display:flex;align-items:center;gap:12px;
  padding:0 32px;
  font-family:'Bebas Neue',sans-serif;
  font-size:1.15rem;letter-spacing:2px;
  color:white;
}
.marquee-item i{color:var(--sun);font-size:0.9rem;}
@keyframes marquee{
  from{transform:translateX(0);}
  to{transform:translateX(-50%);}
}

/* ── FEATURES STRIP ────────────────────── */
.features{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:0;
  border-bottom:1px solid rgba(196,149,106,0.15);
}
.feature{
  padding:36px 28px;
  border-right:1px solid rgba(196,149,106,0.1);
  display:flex;align-items:flex-start;gap:16px;
  transition:background .2s;
}
.feature:last-child{border-right:none;}
.feature:hover{background:rgba(217,79,0,0.06);}
.feature-icon{
  width:48px;height:48px;border-radius:4px;
  background:rgba(217,79,0,0.15);
  border:1.5px solid rgba(217,79,0,0.3);
  display:flex;align-items:center;justify-content:center;
  font-size:1.3rem;flex-shrink:0;
  color:var(--ember);
}
.feature-title{
  font-size:0.9rem;font-weight:800;
  color:var(--cream);margin-bottom:4px;
  text-transform:uppercase;letter-spacing:0.5px;
}
.feature-desc{font-size:0.8rem;color:#7A5A3A;line-height:1.5;}

/* ── HOW IT WORKS ──────────────────────── */
.how-section{
  padding:90px 6%;
  background:var(--soil);
  position:relative;overflow:hidden;
}
.how-section::before{
  content:'';position:absolute;
  top:-100px;right:-100px;
  width:400px;height:400px;border-radius:50%;
  background:radial-gradient(circle, rgba(217,79,0,0.08) 0%, transparent 70%);
}

.section-eyebrow{
  font-size:0.78rem;font-weight:800;
  color:var(--fire);letter-spacing:3px;
  text-transform:uppercase;margin-bottom:12px;
}
.section-title{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(2rem,4vw,3.2rem);
  letter-spacing:2px;color:var(--cream);
  margin-bottom:56px;
  line-height:1;
}
.section-title em{color:var(--ember);font-style:normal;}

.steps{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:24px;position:relative;
}

/* Connector line -->*/
.steps::before{
  content:'';position:absolute;
  top:36px;left:10%;right:10%;height:2px;
  background:linear-gradient(90deg,
    var(--fire),var(--ember),var(--sun),var(--green));
  z-index:0;
  opacity:0.3;
}

.step{
  position:relative;z-index:1;
  text-align:center;padding:32px 20px;
  background:rgba(43,26,14,0.6);
  border:1px solid rgba(196,149,106,0.1);
  border-radius:6px;
  transition:all .25s;
}
.step:hover{
  background:rgba(217,79,0,0.08);
  border-color:rgba(217,79,0,0.3);
  transform:translateY(-6px);
}
.step-num{
  width:56px;height:56px;border-radius:50%;
  margin:0 auto 20px;
  display:flex;align-items:center;justify-content:center;
  font-family:'Bebas Neue',sans-serif;
  font-size:1.6rem;letter-spacing:1px;
  background:var(--fire);color:white;
  box-shadow:0 4px 20px rgba(217,79,0,0.4);
}
.step h3{
  font-size:1rem;font-weight:800;
  color:var(--cream);margin-bottom:8px;
  text-transform:uppercase;letter-spacing:0.5px;
}
.step p{font-size:0.83rem;color:#7A5A3A;line-height:1.6;}

/* ── CTA BANNER ────────────────────────── */
.cta-section{
  padding:90px 6%;
  background:var(--fire);
  position:relative;overflow:hidden;
  text-align:center;
}
.cta-section::before{
  content:'';position:absolute;inset:0;
  background:repeating-linear-gradient(
    45deg,
    transparent,transparent 30px,
    rgba(0,0,0,0.04) 30px,rgba(0,0,0,0.04) 60px);
}
.cta-section *{position:relative;z-index:1;}
.cta-section h2{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(2.5rem,5vw,4.5rem);
  letter-spacing:3px;color:white;
  margin-bottom:16px;line-height:1;
}
.cta-section p{
  font-size:1.1rem;color:rgba(255,255,255,0.8);
  max-width:520px;margin:0 auto 36px;
  font-weight:500;
}
.btn-cta{
  display:inline-flex;align-items:center;gap:10px;
  padding:17px 44px;border-radius:4px;
  background:var(--earth);color:var(--cream);
  font-family:'Nunito',sans-serif;
  font-size:1rem;font-weight:800;
  text-decoration:none;letter-spacing:1px;
  text-transform:uppercase;
  transition:all .25s;
  box-shadow:0 8px 28px rgba(0,0,0,0.3);
}
.btn-cta:hover{
  background:var(--sun);color:var(--earth);
  transform:translateY(-3px);
}

/* ── FOOTER ────────────────────────────── */
footer{
  background:var(--earth);
  border-top:1px solid rgba(196,149,106,0.1);
  padding:36px 6%;
  display:flex;align-items:center;
  justify-content:space-between;flex-wrap:wrap;gap:16px;
}
.footer-logo{
  font-family:'Bebas Neue',sans-serif;
  font-size:1.5rem;letter-spacing:2px;color:var(--cream);
}
.footer-logo em{color:var(--ember);font-style:normal;}
.footer-links{display:flex;gap:24px;}
.footer-links a{
  color:#5A3A22;font-size:0.82rem;font-weight:700;
  text-decoration:none;text-transform:uppercase;
  letter-spacing:0.5px;transition:color .2s;
}
.footer-links a:hover{color:var(--ember);}
.footer-copy{color:#3D2410;font-size:0.78rem;font-weight:600;}

/* ── RESPONSIVE ────────────────────────── */
@media(max-width:900px){
  .hero{grid-template-columns:1fr;}
  .hero-right{display:none;}
  .features{grid-template-columns:repeat(2,1fr);}
  .steps{grid-template-columns:repeat(2,1fr);}
  .steps::before{display:none;}
  .nav-links{display:none;}
}
@media(max-width:500px){
  .features{grid-template-columns:1fr;}
  .steps{grid-template-columns:1fr;}
  .hero-stats{flex-wrap:wrap;}
  .stat{padding:16px;}
}
</style>
</head>
<body>

<!-- KENTE TOP BAR -->
<div class="kente-bar"></div>

<!-- NAVBAR -->
<nav>
  <div class="logo">KASI<em>BITE</em></div>
  <div class="nav-links">
    <a href="browse.php">Browse Food</a>
    <a href="register.php?role=seller">Sell Food</a>
    <a href="#how">How It Works</a>
  </div>
  <div class="nav-cta">
    <a href="login.php" class="btn-nav-outline">Login</a>
    <a href="register.php" class="btn-nav-solid">Join Free</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-pattern"></div>

  <div class="hero-left">
    <div class="hero-tag">
      <i class="fas fa-fire"></i>
      Mzansi's C2C Street Food Platform
    </div>

    <h1>
      <span class="line-white">FROM THE</span>
      <span class="line-fire">CORNER TO</span>
      <span class="line-gold">YOUR DOOR</span>
    </h1>

    <p class="hero-sub">
      Order <strong>pap ne nyama, amagwinya, mogodu</strong> and more 
      directly from real kasi vendors. No middleman. 
      No restaurant markup. Just <strong>authentic township flavour</strong>.
    </p>

    <div class="hero-actions">
      <a href="browse.php" class="btn-fire">
        <i class="fas fa-utensils"></i> Order Now
      </a>
      <a href="register.php?role=seller" class="btn-ghost">
        <i class="fas fa-store"></i> Start Selling
      </a>
    </div>

    <div class="hero-stats">
      <div class="stat">
        <div class="stat-num">R900B</div>
        <div class="stat-label">Township Economy</div>
      </div>
      <div class="stat">
        <div class="stat-num">C2C</div>
        <div class="stat-label">Direct Vendors</div>
      </div>
      <div class="stat">
        <div class="stat-num">100%</div>
        <div class="stat-label">Locally Built</div>
      </div>
    </div>
  </div>

  <!-- IMAGE COLLAGE -->
  <div class="hero-right">
    <div class="img-collage">

      <div class="floating-badge badge-1">🔥 Hot &amp; Fresh</div>
      <div class="floating-badge badge-2">✅ Verified Vendors</div>

      <div class="img-card img-card-1">
        <img src="https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80"
             alt="Grilled meat shisa nyama"/>
        <div class="img-label">🔥 Shisa Nyama</div>
      </div>

      <div class="img-card img-card-2">
        <img src="https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400&q=80"
             alt="Stew and pap"/>
        <div class="img-label">🍲 Pap ne Stew</div>
      </div>

      <div class="img-card img-card-3">
        <img src="https://images.unsplash.com/photo-1574484284002-952d92456975?w=400&q=80"
             alt="Street food"/>
        <div class="img-label">🌽 Kasi Favourites</div>
      </div>

    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-section">
  <div class="marquee-track">
    <?php
    $items = ['Pap ne Nyama','Amagwinya','Mogodu','Shisa Nyama',
              'Vetkoek','Umngqusho','Bunny Chow','Walkie Talkies',
              'Smiley','Seven Colours','Boerewors','Kota'];
    $full = array_merge($items,$items,$items,$items);
    foreach($full as $item):
    ?>
    <div class="marquee-item">
      <i class="fas fa-circle"></i>
      <?= $item ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- FEATURES -->
<div class="features">
  <div class="feature">
    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
    <div>
      <div class="feature-title">Verified Vendors</div>
      <div class="feature-desc">Every seller is verified by our admin team before going live.</div>
    </div>
  </div>
  <div class="feature">
    <div class="feature-icon"><i class="fas fa-lock"></i></div>
    <div>
      <div class="feature-title">Secure Payments</div>
      <div class="feature-desc">Safe checkout with full order confirmation and receipts.</div>
    </div>
  </div>
  <div class="feature">
    <div class="feature-icon"><i class="fas fa-star"></i></div>
    <div>
      <div class="feature-title">Rated Reviews</div>
      <div class="feature-desc">Real buyer reviews on every listing so you know what to expect.</div>
    </div>
  </div>
  <div class="feature">
    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
    <div>
      <div class="feature-title">Mobile Friendly</div>
      <div class="feature-desc">Works perfectly on any phone — built for township connectivity.</div>
    </div>
  </div>
</div>

<!-- HOW IT WORKS -->
<section class="how-section" id="how">
  <div class="section-eyebrow">Simple Process</div>
  <div class="section-title">HOW <em>KASIBITE</em> WORKS</div>
  <div class="steps">
    <div class="step">
      <div class="step-num">01</div>
      <h3>Create Account</h3>
      <p>Sign up free as a Buyer or register as a Seller to list your kasi food.</p>
    </div>
    <div class="step">
      <div class="step-num">02</div>
      <h3>Browse & Discover</h3>
      <p>Search verified listings of authentic township street food near you.</p>
    </div>
    <div class="step">
      <div class="step-num">03</div>
      <h3>Order & Pay</h3>
      <p>Add to cart, checkout securely and get your order confirmation instantly.</p>
    </div>
    <div class="step">
      <div class="step-num">04</div>
      <h3>Enjoy the Flavour</h3>
      <p>Collect your food and leave a review to help other buyers in the community.</p>
    </div>
  </div>
</section>

<!-- CTA BANNER -->
<section class="cta-section">
  <h2>HUNGRY? ORDER<br/>REAL KASI FOOD</h2>
  <p>Join thousands of township food lovers connecting directly with their favourite street food vendors.</p>
  <a href="register.php" class="btn-cta">
    <i class="fas fa-fire"></i> Get Started — It's Free
  </a>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-logo">KASI<em>BITE</em></div>
  <div class="footer-links">
    <a href="#">About</a>
    <a href="#">How It Works</a>
    <a href="register.php?role=seller">Sell Food</a>
    <a href="#">Contact</a>
  </div>
  <div class="footer-copy">© 2026 KasiBite — Built for the Township Economy</div>
</footer>

<!-- BOTTOM KENTE -->
<div class="kente-bar"></div>

</body>
</html>