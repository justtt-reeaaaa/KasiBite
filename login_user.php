<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    if (($user['email_verified'] ?? 1) == 0) {
        $_SESSION['verify_email'] = $user['email'];
        $_SESSION['verify_token'] = $user['email_verification_token'];
        $_SESSION['flash'] = ['type'=>'error','msg'=>'Please verify your email before logging in.'];
        header("Location: verify_notice.php");
        exit;
    }

    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['status']    = $user['status'];

    if ($user['role'] === 'admin') {
        header("Location: admin.php");
    } elseif ($user['role'] === 'seller') {
        if ($user['status'] === 'approved') {
            header("Location: seller_dashboard.php");
        } else {
            $msg = 'Your seller account is still pending admin approval.';
            session_destroy();
            session_start();
            $_SESSION['flash'] = ['type'=>'error','msg'=>$msg];
            header("Location: login.php");
        }
    } else {
        header("Location: buyer_dashboard.php");
    }
    exit;
} else {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid email or password.'];
    header("Location: login.php");
    exit;
}
