<?php
//session_start();
require_once 'config.php'; // Assurez-vous de remplacer 'config.php' par le nom du fichier contenant vos informations de connexion à la base de données.
require_once 'functions.php';

if (isset($_SESSION['user_id'])) {
  loadCartFromDatabase($_SESSION['user_id']);
}
// Vérifiez si une requête de recherche a été envoyée
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['query'])) {
    // Récupérer la requête de recherche de l'utilisateur
    $searchQuery = $_GET['query'];

    // Connexion à la base de données
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Échapper les caractères spéciaux pour éviter les attaques par injection SQL
    $escapedQuery = $conn->real_escape_string($searchQuery);

    // Requête SQL pour rechercher dans la base de données
    $sql = "SELECT * FROM produits WHERE nom LIKE '%$escapedQuery%'"; // Recherche par nom de produit (vous pouvez modifier cela selon vos besoins)

    // Exécuter la requête
    $result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .card {
      height: 400px; /* Définir une hauteur fixe pour toutes les cartes */
    }
    .card-img-top {
      object-fit: cover; /* Pour que les images s'ajustent à la hauteur fixe de la carte */
      height: 200px; /* Définir une hauteur fixe pour les images */
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
          <i class="fas fa-shopping-cart"></i> Cart 
            <span id="cartItemCount" class="badge badge-primary">
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

<!-- Carousel -->
<div class="carousel-container">
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" data-interval="5000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="img_carousel/slide1.jpg" class="d-block w-100">
                </div>
                <div class="carousel-item">
                    <img src="img_carousel/slide2.jpg" class="d-block w-100">
                </div>
                <div class="carousel-item">
                    <img src="img_carousel/slide3.jpg" class="d-block w-100">
                </div>
                <div class="carousel-item">
                    <img src="img_carousel/slide4.jpg" class="d-block w-100">
                </div>
                <!-- Ajoutez d'autres images ici -->
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>


  <!-- Content -->
  <div class="container min-vh-100">
    <!-- Product Cards -->
    <!-- Loop through search results and generate product cards dynamically -->
    <div class="container mt-4">
    <h2>Search Results</h2>
    <div class="row">
      <?php
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          echo '<div class="col-md-3 mb-4">';
          echo '<div class="card">';
          echo '<a href="#" class="product-link" data-id="' . $row['id'] . '">';
          echo '<img src="' . $row['image_url'] . '" class="card-img-top" alt="' . $row['nom'] . '">';
          echo '</a>';
          echo '<div class="card-body">';
          echo '<h5 class="card-title">' . $row['nom'] . '</h5>';
          echo '<p class="card-text" style="bottom: 85px; position: absolute;">Price: €' . $row['prix'] . '</p>';
          echo '<form class="add-to-cart-form" action="index.php" method="post">';
          echo '<input type="hidden" name="product_id" value="' . $row['id'] . '">';
          echo '<input type="hidden" name="product_price" value="' . $row['prix'] . '">';
          echo '<div class="form-group d-flex align-items-center">';
          echo '<label for="quantityi-' . $row['id'] . '" class="mr-2 mb-0" style="position: absolute;bottom:70px">Quantity</label>';
          echo '<input type="number" class="form-control" id="quantityi-' . $row['id'] . '" name="quantityi" min="1" value="1" style="width: 60px; bottom: 60px; position: absolute; left:100px">';
          echo '</div>';
          echo '<button type="submit" class="btn btn-primary mt-2" style="position: absolute; bottom:10px;">Add to Cart</button>';
          echo '</form>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
        }
      } else {
        echo '<p>No results found.</p>';
      }
      ?>
    </div>
  </div>

    <!-- Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel">Product Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Le contenu de la modal sera chargé dynamiquement ici -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
  

  <!-- Footer -->
  <footer class="bg-light py-3 mt-auto">
    <div class="container text-center">
      <span>&copy; 2023 Web Shop. All Rights Reserved.</span>
    </div>
  </footer>

    <!-- Bootstrap and jQuery JS -->
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
                        var addedQuantity = parseInt(formData.get('quantityi')) || 1;
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

<?php
    // Fermer la connexion à la base de données
    $conn->close();
} else {
    // Rediriger vers la page d'accueil si aucune requête de recherche n'a été envoyée
    header("Location: index.php");
    exit();
}
?>
