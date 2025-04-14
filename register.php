<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $screen_resolution = $_POST['screen_resolution'];
    $os = $_POST['os'];

    if (registerUser($name, $email,$screen_resolution, $os)) {
        echo "Registration successful. Please verify your email address.";
    } else {
        echo "Registration failed. This email address is already in use. Please use a different email address.";
    }
}
?>
