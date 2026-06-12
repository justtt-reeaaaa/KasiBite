<?php
session_start();
include 'config.php';

$role = $_SESSION['reg_role'] ?? null;

if (!$role || !in_array($role, ['buyer','seller'])) {
    header("Location: register.php");
    exit;
}

$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = $_POST['password'];

// Check if email already exists
$check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'That email is already registered.'];
    header("Location: register_form.php?role=$role");
    exit;
}

// Sellers start as 'pending', buyers as 'approved'
$status = ($role === 'seller') ? 'pending' : 'approved';
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?,?,?,?,?)");
$stmt->execute([$name, $email, $hashed, $role, $status]);

unset($_SESSION['reg_role']);

if ($role === 'seller') {
    $_SESSION['flash'] = ['type'=>'info','msg'=>'Seller account created! Please wait for admin approval before you can log in.'];
    header("Location: login.php");
} else {
    $_SESSION['flash'] = ['type'=>'success','msg'=>'Account created! Please log in.'];
    header("Location: login.php");
}
exit;
