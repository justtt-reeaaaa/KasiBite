<?php
session_start();
require 'config.php';

$token = $_GET['token'] ?? '';
if ($token) {
    $stmt = $pdo->prepare("UPDATE users SET email_verified=1, email_verification_token=NULL WHERE email_verification_token=?");
    $stmt->execute([$token]);
    unset($_SESSION['verify_email'], $_SESSION['verify_token']);
    $_SESSION['flash'] = ['type'=>'success','msg'=>'Email verified. You can now log in.'];
} else {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid email verification link.'];
}
header("Location: login.php");
exit;
