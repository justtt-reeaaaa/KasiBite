<?php
// header.php — include at the top of every page
// Usage: include 'header.php'; — pass $pageTitle before including
if (!isset($pageTitle)) $pageTitle = "KasiBite";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - KasiBite</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e67e22;
            --primary-dark: #ca6f1e;
            --dark: #1e1e1e;
            --light: #f5f5f5;
            --beige: #f3e9de;
            --card-bg: beige;
            --text: #333;
            --muted: #777;
            --success: #27ae60;
            --danger: #c0392b;
            --border: #ddd;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        a { text-decoration: none; color: inherit; }

        /* TOPNAV */
        .topnav {
            background: var(--dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 40px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topnav .logo {
            font-size: 22px;
            font-weight: 700;
            color: white;
        }
        .topnav .logo span { color: var(--primary); }
        .topnav .nav-links { display: flex; align-items: center; gap: 20px; }
        .topnav .nav-links a { color: #ccc; font-size: 14px; transition: color 0.2s; }
        .topnav .nav-links a:hover { color: var(--primary); }
        .topnav .nav-links .btn-nav {
            background: var(--primary);
            color: white;
            padding: 8px 18px;
            border-radius: 5px;
            font-size: 14px;
        }
        .topnav .nav-links .btn-nav:hover { background: var(--primary-dark); color: white; }

        /* FLASH MESSAGES */
        .flash { padding: 12px 20px; margin: 15px 40px; border-radius: 6px; font-size: 14px; }
        .flash.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash.error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .flash.info    { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        /* FOOTER */
        .footer {
            background: var(--dark);
            color: #aaa;
            text-align: center;
            padding: 20px;
            font-size: 13px;
            margin-top: auto;
        }
        .footer span { color: var(--primary); }

        /* BUTTONS */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn:hover { background: var(--primary-dark); }
        .btn-dark { background: var(--dark); }
        .btn-dark:hover { background: #333; }
        .btn-success { background: var(--success); }
        .btn-success:hover { background: #219a52; }
        .btn-danger { background: var(--danger); }
        .btn-danger:hover { background: #a93226; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-full { width: 100%; text-align: center; }

        /* FORM INPUTS */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: var(--text); }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            background: white;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(230,126,34,0.15);
        }

        /* CARD */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-body { padding: 16px; }

        /* PAGE WRAPPER */
        .page-content { flex: 1; }
    </style>
</head>
<body>

<!-- TOPNAV -->
<nav class="topnav">
    <div class="logo">🍴 Kasi<span>Bite</span></div>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="browse.php">Browse Food</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Admin Panel</a>
            <?php elseif ($_SESSION['role'] === 'seller'): ?>
                <a href="seller_dashboard.php">My Shop</a>
            <?php else: ?>
                <a href="buyer_dashboard.php">My Orders</a>
                <a href="cart.php">🛒 Cart</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-nav btn">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-nav btn">Register</a>
        <?php endif; ?>
    </div>
</nav>

<?php
// Show flash messages
if (isset($_SESSION['flash'])):
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
?>
    <div class="flash <?= $f['type'] ?>"><?= htmlspecialchars($f['msg']) ?></div>
<?php endif; ?>

<div class="page-content">
