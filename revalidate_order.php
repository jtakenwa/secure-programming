<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Connectez-vous à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer l'ID de l'utilisateur
$user_id = $_SESSION['user_id'];

// Récupérer les informations de la dernière commande
$sql_get_order = "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1";
$stmt_get_order = $conn->prepare($sql_get_order);
$stmt_get_order->bind_param("i", $user_id);
$stmt_get_order->execute();
$result_order = $stmt_get_order->get_result();
if ($result_order->num_rows > 0) {
    $order = $result_order->fetch_assoc();

    // Récupérer les détails de la commande
    $order_id = $order['id'];
    $sql_get_order_details = "SELECT * FROM order_details WHERE order_id = ?";
    $stmt_get_order_details = $conn->prepare($sql_get_order_details);
    $stmt_get_order_details->bind_param("i", $order_id);
    $stmt_get_order_details->execute();
    $result_order_details = $stmt_get_order_details->get_result();

    // Stocker les détails de la commande dans la session
    $_SESSION['cart'] = [];
    while ($row = $result_order_details->fetch_assoc()) {
        $_SESSION['cart'][$row['product_id']] = $row['quantity'];
    }

    // Rediriger vers la page de paiement
    header('Location: checkout.php');
    exit();
} else {
    echo 'No order found for revalidation.';
    exit();
}
?>
