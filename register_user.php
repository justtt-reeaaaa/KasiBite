<?php
session_start();
include 'config.php';
require_once 'helpers.php';

$role = $_SESSION['reg_role'] ?? null;

if (!$role || !in_array($role, ['buyer','seller'])) {
    header("Location: register.php");
    exit;
}

$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = $_POST['password'];

if (!password_is_strong($password)) {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Password must be at least 8 characters and include uppercase, lowercase, and a number.'];
    header("Location: register_form.php?role=$role");
    exit;
}

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
$token = bin2hex(random_bytes(24));

$stmt = $pdo->prepare("
    INSERT INTO users
        (full_name, email, password, role, status, email_verified, email_verification_token,
         business_name, phone, location, id_number, verification_details)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
");
$stmt->execute([
    $name,
    $email,
    $hashed,
    $role,
    $status,
    0,
    $token,
    trim($_POST['business_name'] ?? ''),
    trim($_POST['phone'] ?? ''),
    trim($_POST['location'] ?? ''),
    trim($_POST['id_number'] ?? ''),
    trim($_POST['verification_details'] ?? '')
]);

unset($_SESSION['reg_role']);

$_SESSION['verify_email'] = $email;
$_SESSION['verify_token'] = $token;
header("Location: verify_notice.php");
exit;
