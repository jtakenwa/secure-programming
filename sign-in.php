<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (loginUser($email, $password)) {
        header('Location: index.php');
    } else {
        header('Location: index.php');
        $error_message = 'Invalid email or password.';
    }
}

?>