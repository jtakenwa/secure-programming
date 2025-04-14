<?php
require_once 'functions.php';
clearCart($_SESSION['user_id']);
$order_number = isset($_GET['order_number']) ? htmlspecialchars($_GET['order_number']) : 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thank You</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
  <div class="container">
    <h1>Thank You for Your Order!</h1>
    
    <p>We appreciate your business and will process your order soon.</p>
    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
  </div>
</body>
</html>
