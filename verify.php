<?php
require_once 'config.php';

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $verification_code = $_GET['code'];

    $sql = "UPDATE users SET is_verified = 1 WHERE email = ? AND verification_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $verification_code);

    if ($stmt->execute()) {
        echo "<script>alert('Email address verified successfully. You can now change you default password.') ; window.location.href = 'change-password.php';</script>";
        
    } else {
        echo "<script>alert('Error verifying email address. Please try again.') ; window.location.href = 'change-password.php';</script>";
    }
} else {
    echo "<script>alert('Invalid verification link.') ; window.location.href = 'change-password.php;</script>";
}
?>
