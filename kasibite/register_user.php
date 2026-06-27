<?php
session_start();
include 'config.php';

$role = $_SESSION['reg_role'] ?? null;
if(!$role || !in_array($role,['buyer','seller'])){header("Location:register.php");exit;}

$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = $_POST['password'];
$confirm  = $_POST['confirm_password'];

// Server-side password validation
if(strlen($password) < 8){
    $_SESSION['flash']=['type'=>'error','msg'=>'Password must be at least 8 characters.'];
    header("Location:register_form.php?role=$role"); exit;
}
if($password !== $confirm){
    $_SESSION['flash']=['type'=>'error','msg'=>'Passwords do not match.'];
    header("Location:register_form.php?role=$role"); exit;
}

// Check email duplicate
$check=$pdo->prepare("SELECT user_id FROM users WHERE email=?");
$check->execute([$email]);
if($check->fetch()){
    $_SESSION['flash']=['type'=>'error','msg'=>'That email is already registered.'];
    header("Location:register_form.php?role=$role"); exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$status = ($role==='seller') ? 'pending' : 'approved';

// Seller extra fields
$phone    = trim($_POST['phone'] ?? '');
$sa_id    = trim($_POST['sa_id_number'] ?? '');
$stall    = trim($_POST['stall_address'] ?? '');
$bio      = trim($_POST['bio'] ?? '');
$fsd      = isset($_POST['food_safety_declaration']) ? 1 : 0;

// Validate SA ID (13 digits) for sellers
if($role==='seller'){
    if(!preg_match('/^\d{13}$/', $sa_id)){
        $_SESSION['flash']=['type'=>'error','msg'=>'SA ID number must be exactly 13 digits.'];
        header("Location:register_form.php?role=$role"); exit;
    }
    if(!$phone || !$stall || !$bio){
        $_SESSION['flash']=['type'=>'error','msg'=>'Please complete all seller verification fields.'];
        header("Location:register_form.php?role=$role"); exit;
    }
    if(!$fsd){
        $_SESSION['flash']=['type'=>'error','msg'=>'You must agree to the food safety declaration.'];
        header("Location:register_form.php?role=$role"); exit;
    }
}

$stmt=$pdo->prepare("INSERT INTO users (full_name,email,password,role,status,phone,sa_id_number,stall_address,bio,food_safety_declaration)
                     VALUES (?,?,?,?,?,?,?,?,?,?)");
$stmt->execute([$name,$email,$hashed,$role,$status,$phone,$sa_id,$stall,$bio,$fsd]);

unset($_SESSION['reg_role']);

if($role==='seller'){
    $_SESSION['flash']=['type'=>'info','msg'=>'Seller application submitted! Admin will review your details and approve you shortly.'];
} else {
    $_SESSION['flash']=['type'=>'success','msg'=>'Account created! Please log in.'];
}
header("Location:login.php"); exit;
