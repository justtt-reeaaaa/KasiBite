<?php
// validate.php — shared validation helpers

/**
 * Validates password strength.
 * Returns null if valid, or an error message string if invalid.
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    return null; // valid
}

/**
 * Handles a single image upload. Returns the relative path to store in DB,
 * or null if no file was uploaded, or false on error.
 */
function handleImageUpload($fileInputName, $uploadDir) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no file selected — not an error, just optional
    }
    if ($_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES[$fileInputName]['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        return false;
    }

    // Limit to 3MB
    if ($_FILES[$fileInputName]['size'] > 3 * 1024 * 1024) {
        return false;
    }

    $ext = pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $destination = rtrim($uploadDir, '/') . '/' . $filename;

    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $destination)) {
        return 'uploads/' . $filename; // relative path stored in DB
    }
    return false;
}
