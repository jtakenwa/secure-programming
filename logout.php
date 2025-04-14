<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Mettre à jour le statut de déconnexion
    $update_sql = "UPDATE users SET is_online = 0 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();

    session_destroy();
    header("Location: index.php");
    exit();
}
?>
