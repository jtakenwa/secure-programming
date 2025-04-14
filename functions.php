<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require 'vendor/autoload.php'; // Assurez-vous d'inclure le chemin correct vers l'autoloader de PHP Mailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// use TCPDF;
require '/var/www/html/web-shop/vendor/phpmailer/phpmailer/src/Exception.php';
require '/var/www/html/web-shop/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

session_start();

function generateStrongPassword($length = 9) {
    $upperCase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowerCase = 'abcdefghijklmnopqrstuvwxyz';
    $digits = '0123456789';
    $specialChars = '!@#$%&?';

    $allChars = $upperCase . $lowerCase . $digits . $specialChars;
    $password = '';
    
    // Ensure the password contains at least one of each required character type
    $password .= $upperCase[random_int(0, strlen($upperCase) - 1)];
    $password .= $lowerCase[random_int(0, strlen($lowerCase) - 1)];
    $password .= $digits[random_int(0, strlen($digits) - 1)];
    $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];

    // Fill the rest of the password length with random characters
    for ($i = 4; $i < $length; $i++) {
        $password .= $allChars[random_int(0, strlen($allChars) - 1)];
    }

    // Shuffle the password to avoid predictable patterns
    $password = str_shuffle($password);

    return $password;
}

function registerUser($name, $email, $screen_resolution, $os)
{
    global $conn;

    $sql_check_email = "SELECT * FROM users WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();

    if ($result_check_email->num_rows > 0) {
        $_SESSION['alert_message'] = 'This email address is already in use. Please use a different email address.';
        
        return false;
    } else {
        $verification_code = bin2hex(random_bytes(16));
        $password = generateStrongPassword();
        $hashed_password = hash('sha512', $password);

        $sql_insert_user = "INSERT INTO users (name, email, password, verification_code,screen_resolution, os) VALUES (?, ?, ?, ?,?,?)";
        $stmt_insert_user = $conn->prepare($sql_insert_user);
        $stmt_insert_user->bind_param("ssssss", $name, $email, $hashed_password, $verification_code, $screen_resolution, $os);
        $result_insert_user = $stmt_insert_user->execute();

        if ($result_insert_user) {
            sendVerificationEmail($email, $verification_code, $password);
            return true;
        } else {
            return false;
        }
    }
}

function loginUser($email, $password)
{
    global $conn;
    $hashed_password = $password;

    $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['is_verified'] == 1) {
            if ($user['pwschg']==0){
                $_SESSION['alert_message_login'] = 'Please change your default password.';
                header("Location: change-password.php");
                exit();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['last_login'] = $user['last_login'];
            $_SESSION['cart'] = getCartItems($user['id']);

            $current_date = date('Y-m-d H:i:s');
            $sql_update_last_login = "UPDATE users SET last_login = ? WHERE email = ?";
            $stmt_update_last_login = $conn->prepare($sql_update_last_login);
            $stmt_update_last_login->bind_param("ss", $current_date, $email);
            $stmt_update_last_login->execute();

            // Mettre à jour le statut de connexion
            $update_sql = "UPDATE users SET is_online = 1 WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();


            $_SESSION['new_login'] = true;
            $_SESSION['alert_message_login'] = "Welcome Mr/Mrs {$_SESSION['username']}! You were last online on {$_SESSION['last_login']}.";

            header("Location: index.php");
            exit();
        } else {
            $_SESSION['alert_message_login'] = 'Please verify your email address before logging in.';
        }
    } else {
        $_SESSION['alert_message_login'] = 'Invalid email or password.';
        header("Location: login.php");
        exit();
    }
}

function sendVerificationEmail($email, $verification_code, $password)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.free.fr';
        $mail->SMTPAuth = true;
        $mail->Username = 'jaasiel@free.fr';
        $mail->Password = 'Uec-unT-3c1-Zmq';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('jaasiel@free.fr', 'JAAS\'Shop');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your email address';
        $mail->Body = " <html>
        <head>
            <style>
                .password-container {
                    display: inline-block;
                    position: relative;
                    cursor: pointer;
                }
                .password-container .password {
                    border: 1px solid #ccc;
                    padding: 7px;
                    border-radius: 40px;
                    background: #f9f9f9;
                    color: transparent;
                    text-shadow: 0 0 5px rgba(0,0,0,0.5);
                    user-select: none;
                }
                .password-container:hover .password {
                    color: black;
                    text-shadow: none;
                }
                .tooltip {
                    visibility: hidden;
                    background-color: black;
                    color: #fff;
                    text-align: center;
                    border-radius: 5px;
                    padding: 5px;
                    position: absolute;
                    z-index: 1;
                    bottom: 125%; 
                    left: 50%; 
                    margin-left: -60px;
                    opacity: 0;
                    transition: opacity 0.3s;
                }
                .tooltip::after {
                    content: '';
                    position: absolute;
                    top: 100%; 
                    left: 50%;
                    margin-left: -5px;
                    border-width: 5px;
                    border-style: solid;
                    border-color: black transparent transparent transparent;
                }
                .password-container:active .tooltip {
                    visibility: visible;
                    opacity: 1;
                }
            </style>
        </head>
        <body>
            <p>Your verification code is : $verification_code</p>
            <p>
                Your default password is : 
                <div class='password-container' onclick='copyPassword()'>
                    <span class='password' id='password'>$password</span>
                </div>
            </p>
            <p>Please click the following link to verify your email address: 
                <a href='http://10.211.55.3/web-shop/verify.php?email=$email&code=$verification_code'>Verify Email</a>
            </p>
            <script>
                function copyPassword() {
                    var password = document.getElementById('password');
                    var tooltip = document.getElementById('tooltip');
                    var range = document.createRange();
                    range.selectNode(password);
                    window.getSelection().removeAllRanges(); 
                    window.getSelection().addRange(range); 
                    document.execCommand('copy');
                    window.getSelection().removeAllRanges(); 
                    tooltip.style.visibility = 'visible';
                    tooltip.style.opacity = '1';
                    setTimeout(function() {
                        tooltip.style.visibility = 'hidden';
                        tooltip.style.opacity = '0';
                    }, 2000);
                }
            </script>
        </body>
        </html>";

        $mail->send();
    } catch (Exception $e) {
        $_SESSION['alert_message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
// 10.211.55.3
function changePassword($email, $oldPassword, $newPassword)
{
    global $conn;
    $hashed_old_password = hash('sha512', $oldPassword);
    $hashed_new_password = hash('sha512', $newPassword);

    $sql_check_old_password = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt_check_old_password = $conn->prepare($sql_check_old_password);
    $stmt_check_old_password->bind_param("ss", $email, $hashed_old_password);
    $stmt_check_old_password->execute();
    $result_check_old_password = $stmt_check_old_password->get_result();

    if ($result_check_old_password->num_rows > 0) {
        $sql_update_password = "UPDATE users SET  password = ? , pwschg = 1 WHERE email = ?";
        $stmt_update_password = $conn->prepare($sql_update_password);
        $stmt_update_password->bind_param("ss", $hashed_new_password, $email);
        $result_update_password = $stmt_update_password->execute();


        

        if ($result_update_password) {
            $_SESSION['alert_message_chp'] = 'Password changed successfully!';
        } else {
            $_SESSION['alert_message_chp'] = 'Failed to change password.';
        }
    } else {
        $_SESSION['alert_message_chp'] = 'Ancien mot de passe incorrect.';
    }
}


// Fonction pour sauvegarder le panier dans la base de données
function saveCartToDatabase($user_id) {
    global $conn;
    // Supprimer l'ancien panier de l'utilisateur
    $sql_delete_cart = "DELETE FROM carts WHERE user_id = ?";
    $stmt_delete_cart = $conn->prepare($sql_delete_cart);
    $stmt_delete_cart->bind_param("i", $user_id);
    $stmt_delete_cart->execute();

    // Insérer le nouveau panier
    $sql_insert_cart = "INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt_insert_cart = $conn->prepare($sql_insert_cart);
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt_insert_cart->bind_param("iii", $user_id, $product_id, $quantity);
        $stmt_insert_cart->execute();
    }
}

function sendPasswordResetEmail($email, $reset_link) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.free.fr';
        $mail->SMTPAuth = true;
        $mail->Username = 'jaasiel@free.fr'; // Remplacez par votre nom d'utilisateur SMTP
        $mail->Password = 'Uec-unT-3c1-Zmq'; // Remplacez par votre mot de passe SMTP
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('jaasiel@free.fr', 'JAAS\'Shop');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "<html>
        <head>
            <style>
                .reset-container {
                    padding: 10px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    background-color: #f9f9f9;
                    font-family: Arial, sans-serif;
                }
                .reset-link {
                    display: inline-block;
                    padding: 10px 20px;
                    margin: 10px 0;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                }
                .reset-link:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class='reset-container'>
                <p>You requested a password reset. Click the link below to reset your password:</p>
                <p><a class='reset-link' href='$reset_link'>Reset Password</a></p>
                <p>If you did not request a password reset, please ignore this email.</p>
            </div>
        </body>
        </html>";

        $mail->send();
    } catch (Exception $e) {
        $_SESSION['alert_message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function addToCart($user_id, $product_id, $quantity) {
    global $conn;
    $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = quantity + ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
    $stmt->execute();
    $stmt->close();
}

function getCartItems($user_id) {
    global $conn;
    $sql = "SELECT product_id, quantity FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = [];
    while ($row = $result->fetch_assoc()) {
        $cart_items[$row['product_id']] = $row['quantity'];
    }
    $stmt->close();
    return $cart_items;
}

function updateCartQuantity($user_id, $product_id, $quantity) {
    global $conn;
    $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
}

function removeFromCart($user_id, $product_id) {
    global $conn;
    $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
}
// Fonction pour charger le panier de l'utilisateur depuis la base de données
function loadCartFromDatabase($user_id) {
    global $conn;
    $sql = "SELECT product_id, quantity FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['cart'] = [];
    while ($row = $result->fetch_assoc()) {
        $_SESSION['cart'][$row['product_id']] = $row['quantity'];
    }
    $stmt->close();
}
// functions.php

function clearCart($user_id) {
    global $conn;
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    } else {
        return false;
    }
}

function updateProductStock($product_id, $quantity_ordered) {
    global $conn;
    $sql = "UPDATE produits SET quantite_stock = quantite_stock - ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity_ordered, $product_id);
    $stmt->execute();
}

function isValidCoupon($code) {
    global $conn;
    $sql = "SELECT * FROM coupons WHERE code = ? AND expiry_date > NOW() AND used = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function applyCoupon($code, $order_id) {
    global $conn;
    $coupon = isValidCoupon($code);
    if ($coupon) {
        $sql = "UPDATE coupons SET used = 1 WHERE code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();

        // Enregistrer le coupon utilisé dans la commande
        $sql = "UPDATE orders SET coupon_code = ?, discount_percentage = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdi", $code, $coupon['discount_percentage'], $order_id);
        $stmt->execute();

        return $coupon['discount_percentage'];
    }
    return 0;
}



?>
