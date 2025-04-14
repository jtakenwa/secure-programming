<?php
//session_start();
require_once 'config.php';
require_once 'functions.php';

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log($conn->connect_error); // journaliser côté serveur
    die("Une erreur est survenue. Veuillez réessayer plus tard.");

}
if (isset($_SESSION['user_id'])) {
    loadCartFromDatabase($_SESSION['user_id']);
  }


// Handle login alert message
if (isset($_SESSION['alert_message_login'])) {
    $alert_message = $_SESSION['alert_message_login'];
    unset($_SESSION['alert_message_login']);
}

// Fetch products
$sql = "SELECT * FROM produits";
$result = $conn->query($sql);

// Welcome message
if (isset($_GET['welcome']) && $_GET['welcome'] == 1 && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $last_login = $_SESSION['last_login'];
    echo "<script>
    Swal.fire(" . json_encode("Bienvenue, $username!") . ",
             " . json_encode("Votre dernière connexion était le $last_login.") . ",
             'info');
    </script>";

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card {
            height: 400px;
        }
        .card-img-top {
            object-fit: cover;
            height: 200px;
        }
        .carousel-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="Web Shop Logo" width="100" height="30">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="features.php">Features</a>
                    </li>
                </ul>
                <?php if (isset($alert_message)) {
                    if(strpos($alert_message, 'Welcome') === 0){echo "<script>Swal.fire('Login!', '$alert_message', 'success');</script>";}
                    else{echo "<script>Swal.fire('Login!', '$alert_message', 'error');</script>";}
                } ?>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['last_login'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><span id="online-count">0</span> user(s) online</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#">WELCOME TO JAAS'Shop</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <form class="form-inline my-2 my-lg-0 mx-auto" role="search" action="search.php" method="GET">
                    <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="query">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                </form>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart 
                            <span id="cartItemCount" class="badge badge-primary">
                                <?php
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
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo $_SESSION['username']; ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="account.php">My Account</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="change-password.php">Change your password</a>

                                <?php if ($_SESSION['user_id'] == "26"): ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="admin.php">Admin Panel</a>

                                <?php endif?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
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
        <div class="container mt-4">
            <h2>Featured Products</h2>
            <div class="row">
                <?php
                while ($row = $result->fetch_assoc()) {
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
        // Add to Cart functionality
        var addToCartForms = document.querySelectorAll('.add-to-cart-form');
        addToCartForms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(form);
                fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        var cartItemCountElement = document.getElementById('cartItemCount');
                        if (cartItemCountElement) {
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
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        // Update online users count
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
        setInterval(updateOnlineUsers, 5000);
        updateOnlineUsers();

        // Load product details in modal
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



