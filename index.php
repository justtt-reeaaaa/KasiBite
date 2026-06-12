<?php
session_start();
$pageTitle = "Home";
include 'header.php';
?>

<style>
/* HERO */
.hero {
    height: 90vh;
    background: url('https://images.unsplash.com/photo-1604908176997-125f25cc6f3d') center/cover no-repeat;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
    position: relative;
}
.hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
}
.hero-content { position: relative; z-index: 1; padding: 20px; }
.hero h1 { font-size: 42px; font-weight: 700; line-height: 1.2; margin-bottom: 15px; }
.hero h1 span { color: var(--primary); }
.hero p { font-size: 16px; margin-bottom: 25px; color: #ddd; }
.hero-btns { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }

/* BANNER */
.banner {
    background: var(--primary);
    color: white;
    text-align: center;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
}

/* HOW IT WORKS */
.how-section {
    padding: 70px 40px;
    background: #eac8ae;
    text-align: center;
}
.section-title { font-size: 28px; font-weight: 700; margin-bottom: 10px; color: var(--dark); }
.section-sub { color: #666; margin-bottom: 40px; font-size: 14px; }
.steps { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }
.step {
    background: white;
    border-radius: 10px;
    padding: 25px 20px;
    width: 180px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.step .icon { font-size: 32px; margin-bottom: 10px; }
.step h3 { color: var(--primary); font-size: 15px; margin-bottom: 8px; }
.step p { font-size: 13px; color: #666; }

/* FEATURED */
.featured-section { padding: 60px 40px; }
.featured-section .section-title { text-align: center; margin-bottom: 8px; }
.featured-section .section-sub { text-align: center; margin-bottom: 35px; }
.feat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    max-width: 1100px;
    margin: 0 auto;
}
.feat-card {
    background: var(--beige);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: transform 0.2s;
}
.feat-card:hover { transform: translateY(-3px); }
.feat-card img { width: 100%; height: 150px; object-fit: cover; }
.feat-card-body { padding: 12px; }
.feat-card-body h4 { font-size: 14px; margin-bottom: 4px; }
.feat-card-body .price { color: var(--primary); font-weight: 700; font-size: 15px; }

/* WHY KASIBITE */
.why-section {
    background: var(--dark);
    color: white;
    padding: 60px 40px;
    text-align: center;
}
.why-section .section-title { color: white; }
.why-section .section-sub { color: #aaa; }
.why-grid { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-top: 35px; }
.why-item { width: 200px; }
.why-item .icon { font-size: 36px; margin-bottom: 12px; }
.why-item h4 { color: var(--primary); margin-bottom: 8px; font-size: 14px; }
.why-item p { font-size: 13px; color: #aaa; }
</style>

<!-- HERO -->
<div class="hero">
    <div class="hero-content">
        <h1>FROM THE CORNER<br><span>TO YOUR DOOR</span></h1>
        <p>Order real kasi street food directly from township vendors near you.</p>
        <div class="hero-btns">
            <a href="browse.php" class="btn">Explore Food 🍽️</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-dark">Sell Your Food</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BANNER -->
<div class="banner">
    🔥 Order <strong>pap ne nyama, amagwinya, mogodu</strong> and more directly from real kasi vendors — No middleman. No restaurant markup. Just <strong>authentic township flavour.</strong> 🔥
</div>

<!-- HOW IT WORKS -->
<div class="how-section">
    <h2 class="section-title">How KasiBite Works</h2>
    <p class="section-sub">Simple. Local. Authentic.</p>
    <div class="steps">
        <div class="step">
            <div class="icon">📝</div>
            <h3>1. Sign Up</h3>
            <p>Create an account as a buyer or seller.</p>
        </div>
        <div class="step">
            <div class="icon">🔍</div>
            <h3>2. Browse</h3>
            <p>Explore food options from kasi vendors.</p>
        </div>
        <div class="step">
            <div class="icon">🛒</div>
            <h3>3. Order</h3>
            <p>Add items to cart and checkout easily.</p>
        </div>
        <div class="step">
            <div class="icon">🎉</div>
            <h3>4. Enjoy</h3>
            <p>Collect your food and enjoy the flavour!</p>
        </div>
    </div>
</div>

<!-- FEATURED FOOD -->
<?php
require 'config.php';
$featured = $pdo->query("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.status='active' LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

$catImages = [
    'Amagwinya'             => 'https://iol-prod.appspot.com/image/0a866824961ebe8a09ad8875ebac339f70fdbe4e=w700',
    'Pap & Stew'            => 'https://th.bing.com/th/id/R.79bd82699014d8e920c92c40ef5436ff?rik=raN2D5pdTbIujQ&pid=ImgRaw&r=0',
    'Grilled Meat'          => 'https://www.suburbansimplicity.com/wp-content/uploads/2021/06/How-to-keep-meat-moist-on-the-grill.jpg',
    'Breakfast'             => 'https://tse3.mm.bing.net/th/id/OIP.cV8IfMXFn2uqn3YOR4ne0gHaHa?r=0&pid=ImgDetMain',
    'Beverages'             => 'https://th.bing.com/th/id/R.4327e9e3d10634e6af86b81314bacd0d?rik=9VdoAm28zgLKsQ&pid=ImgRaw&r=0',
    'Vetkoek'               => 'https://as2.ftcdn.net/v2/jpg/02/23/81/47/1000_F_223814741_k90kjLiXIFbLXpUtlnlOWyioTUoMt1vU.jpg',
    'Umngqusho'             => 'https://www.thesouthafrican.com/wp-content/uploads/2020/07/087f68fa-umgquasho-samp-and-beans-with-lamb-and-chakalaka.jpg',
    'Snacks & Sides'        => 'https://healy-group.com/wp-content/uploads/AdobeStock_953274304-min-1920x1076.jpeg',
    'Smiley & Walkie Talkies'=> 'https://www.houseofyork.co.za/images/cmsimages/big/news-288-2588-walkie-talkie.jpeg',
    'Bunny Chow'            => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d',
];
$defaultImg = 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d';
?>

<div class="featured-section">
    <h2 class="section-title">Featured Food</h2>
    <p class="section-sub">Fresh from the kasi, ready for you</p>
    <div class="feat-grid">
        <?php foreach($featured as $p):
            $img = $catImages[$p['category_name']] ?? $defaultImg;
        ?>
        <div class="feat-card">
            <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <div class="feat-card-body">
                <h4><?= htmlspecialchars($p['name']) ?></h4>
                <p style="font-size:12px;color:#888;margin-bottom:6px;"><?= htmlspecialchars($p['category_name']) ?></p>
                <div class="price">R<?= number_format($p['price'], 2) ?></div>
                <a href="browse.php" class="btn btn-full" style="margin-top:10px;display:block;text-align:center;padding:8px;">Order Now</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- WHY KASIBITE -->
<div class="why-section">
    <h2 class="section-title">Why KasiBite?</h2>
    <p class="section-sub">Built for the township economy</p>
    <div class="why-grid">
        <div class="why-item">
            <div class="icon">🛡️</div>
            <h4>Verified Sellers</h4>
            <p>Every seller is reviewed and approved by our admin team.</p>
        </div>
        <div class="why-item">
            <div class="icon">💸</div>
            <h4>No Middleman</h4>
            <p>Money goes directly to the vendor. No restaurant cut.</p>
        </div>
        <div class="why-item">
            <div class="icon">📱</div>
            <h4>Mobile Friendly</h4>
            <p>Works on any phone, even on data-light connections.</p>
        </div>
        <div class="why-item">
            <div class="icon">🌍</div>
            <h4>Township First</h4>
            <p>Built specifically for SA kasi street food culture.</p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
