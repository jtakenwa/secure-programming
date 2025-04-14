<?php
session_start();
require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les critères de tri
$sort_by = isset($_POST['sort_by']) ? $_POST['sort_by'] : '';

// Construire la requête SQL en fonction du critère de tri
$sql = "SELECT * FROM produits";
switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY prix ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY prix DESC";
        break;
    case 'category':
        $sql .= " ORDER BY categorie";
        break;
    default:
        $sql .= " ORDER BY nom";
        break;
}

// Exécuter la requête
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="col-md-3 mb-4">';
        echo '<div class="card">';
        echo '<a href="#" class="product-link" data-id="' . $row['id'] . '">';
        echo '<img src="' . $row['image_url'] . '" class="card-img-top" alt="' . $row['nom'] . '">';
        echo '</a>';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">' . $row['nom'] . '</h5>';
        echo '<p class="card-text" style="bottom: 85px; position: absolute;">Price: €' . $row['prix'] . '</p>';
        echo '<form class="add-to-cart-form" action="cart.php" method="post">';
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
    echo '<p>No products found.</p>';
}

$conn->close();
?>
