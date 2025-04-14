<?php
//session_start();
require_once 'config.php';
require_once 'functions.php';

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_SESSION['user_id'])) {
  loadCartFromDatabase($_SESSION['user_id']);
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['order_id'])) {
    echo "No order ID provided.";
    exit();
}

$order_id = $_GET['order_id'];

// Récupérer les détails de la commande
$sql_order = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();

if (!$order) {
    echo "Order not found or you do not have permission to view this order.";
    exit();
}

// Récupérer les articles de la commande
$sql_items = "SELECT oi.*, p.nom, p.prix, p.image_url FROM order_details oi 
              JOIN produits p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

$pdf_path = 'invoices/facture_' . $order['order_number'] . '.pdf'; // Chemin où le PDF est stocké

// Traiter la revalidation de la commande
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['revalidate_order'])) {
    // Créer une nouvelle commande avec les mêmes détails
    $new_order_number = uniqid('ORD-');
    $sql_new_order = "INSERT INTO orders (user_id, order_number, total, created_at, shipping_method, address, first_name, last_name, city, zip_code, country) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
    $stmt_new_order = $conn->prepare($sql_new_order);
    $stmt_new_order->bind_param("isdsssssss", $_SESSION['user_id'], $new_order_number, $order['total'], $order['shipping_method'], $order['address'], $order['first_name'], $order['last_name'], $order['city'], $order['zip_code'], $order['country']);
    $stmt_new_order->execute();
    $new_order_id = $stmt_new_order->insert_id;

    // Dupliquer les articles de la commande
    $result_items->data_seek(0); // Reset result set pointer to the start
    while ($item = $result_items->fetch_assoc()) {
        $sql_new_item = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_new_item = $conn->prepare($sql_new_item);
        $stmt_new_item->bind_param("iiid", $new_order_id, $item['product_id'], $item['quantity'], $item['prix']);
        $stmt_new_item->execute();
    }

    $_SESSION['alert_message'] = "Order has been revalidated successfully.";
    $_SESSION['alert_type'] = "success";

    // Rediriger vers la page des détails de la nouvelle commande
    header("Location: order_details.php?order_id=" . $new_order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand" href="#">
      <img src="logo.png" alt="Web Shop Logo" width="100" height="30">
    </a>

    <!-- Navbar Toggler -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mr-auto">
        <!-- Home -->
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <!-- Features -->
        <li class="nav-item">
          <a class="nav-link" href="features.php">Features</a>
        </li>
      </ul>

      <!-- USER ONLINE -->
      
      <?php if(isset($alert_message))
      echo "<script>
      Swal.fire({
        title: 'Login!',
        text: '$alert_message',
        icon: 'success'
      });
      </script>"
      ?>

      <!-- User Authentication Links -->
      <ul class="navbar-nav">
        <?php if(isset($_SESSION['last_login'])): ?>
          <!-- Account Dropdown -->
          <li class="nav-item dropdown">
          <a class="nav-link" href="#"><span id="online-count">0</span> user(s) online</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="#">WELCOME TO JAAS'Shop</a>
          </li>
        <?php endif; ?>
      </ul>
      
      <!-- Search Bar -->
      <form class="form-inline my-2 my-lg-0 mx-auto" role="search" action="search.php" method="GET">
        <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="query">
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
      </form>

      
      <!-- Cart -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="cart.php">
            Cart <span id="cartItemCount" class="badge badge-primary">
              <?php
                // Calculer le nombre total de produits dans le panier
                $totalItemsInCart = 0;
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $quantity) {
                        $totalItemsInCart += $quantity;
                    }
                }
                echo $totalItemsInCart;
              ?>
            </span>
          </a>
        </li>
      </ul>


      <!-- User Authentication Links -->
      <ul class="navbar-nav">
        <?php if(isset($_SESSION['username'])): ?>
          <!-- Account Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?php echo $_SESSION['username']; ?>
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="account.php">My Account</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="change-password.php">change your password</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="logout.php">Logout</a>
            </div>
          </li>
          
        <?php else: ?>
          <!-- Sign In / Sign Up Links -->
          <li class="nav-item">
            <a class="nav-link" href="login.php">Sign In/Sign Up</a>
          </li>
        <?php endif; ?>
        
      </ul>
    </div>
  </div>
</nav>
<div class="container">
    <h1>Order Details</h1>
    <h2>Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
    <p>Date: <?php echo htmlspecialchars($order['created_at']); ?></p>
    <p>Shipping Method: <?php echo htmlspecialchars($order['shipping_method']); ?></p>
    <p>Shipping Address: <?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></p>
    <h3>Items</h3>
    <table class="table">
        <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total Price</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($item = $result_items->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['nom']); ?>" width="50">
                    <?php echo htmlspecialchars($item['nom']); ?>
                </td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td>€<?php echo number_format($item['prix'], 2); ?></td>
                <td>€<?php echo number_format($item['prix'] * $item['quantity'], 2); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <p><strong>Total: €<?php echo number_format($order['total'], 2); ?></strong></p>
    
    <!-- Bouton de téléchargement du PDF -->
    <?php if (file_exists($pdf_path)): ?>
        <a href="<?php echo htmlspecialchars($pdf_path); ?>" class="btn btn-primary" download>Télécharger la facture PDF</a>
    <?php else: ?>
        <p class="text-danger">La facture PDF n'est pas disponible.</p>
    <?php endif; ?>
    
    <!-- Formulaire pour revalider la commande -->
    <form action="revalidate_order.php" method="post">
        <input type="hidden" name="revalidate_order" value="1">
        <button type="submit" class="btn btn-success">Revalidate Order</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
      // Sélectionner tous les formulaires d'ajout au panier
    var addToCartForms = document.querySelectorAll('.add-to-cart-form');

    // Parcourir chaque formulaire et ajouter un gestionnaire d'événement pour la soumission
    addToCartForms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            // Empêcher le comportement par défaut du formulaire (rechargement de la page)
            event.preventDefault();
            
            // Récupérer les données du formulaire
            var formData = new FormData(form);
            
            // Envoyer une requête AJAX au serveur pour ajouter le produit au panier
            fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Gérer la réponse du serveur ici (par exemple, mettre à jour le badge du panier)
                if (response.ok) {
                    // Mettre à jour le badge du panier sur la page actuelle
                    var cartItemCountElement = document.getElementById('cartItemCount');
                    if (cartItemCountElement) {
                        // Incrémenter le nombre d'articles dans le panier
                        var currentCount = parseInt(cartItemCountElement.innerText);
                        var addedQuantity = parseInt(formData.get('quantity')) || 1;
                        cartItemCountElement.innerText = currentCount + addedQuantity;
                        Swal.fire({
                          position: "top-center",
                          icon: "success",
                          title: "The product is added to your basket",
                          showConfirmButton: false,
                          timer: 1500
                        });
                        
                        //alert("le produit est bien ajouter a votre panier");
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
  </script>
  <script>
        function updateOnlineUsers() {
            $.ajax({
                url: 'online_users.php',
                method: 'GET',
                success: function(data) {
                    $('#online-count').text(data.online_count);
                },
                error: function() {
                    console.error('Failed to fetch online users count');
                }
            });
        }

        // Update the online users count every 5 seconds
        setInterval(updateOnlineUsers, 5000);

        // Initial update
        updateOnlineUsers();
    </script>

<script>
$(document).ready(function() {
    $('.product-link').on('click', function(event) {
        event.preventDefault();
        var productId = $(this).data('id');
        $.ajax({
            url: 'product_details.php',
            type: 'GET',
            data: { id: productId },
            success: function(response) {
                $('#productModal .modal-body').html(response);
                $('#productModal').modal('show');
            },
            error: function() {
                alert('Failed to load product details.');
            }
        });
    });
});
</script>
</body>
</html>