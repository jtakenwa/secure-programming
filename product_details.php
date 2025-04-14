<?php
require_once 'config.php'; // Assurez-vous de remplacer 'config.php' par le nom du fichier contenant vos informations de connexion à la base de données.

if (!isset($_GET['id'])) {
    echo "Invalid product ID.";
    exit();
}

$product_id = intval($_GET['id']);
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id'])) {
    loadCartFromDatabase($_SESSION['user_id']);
  }

$sql = "SELECT * FROM produits WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "Product not found.";
    exit();
}
$stmt->close();
$conn->close();
?>

<div class="row">
    <div class="col-md-6">
        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['nom']); ?>">
    </div>
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($product['nom']); ?></h1>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <p>Price: €<?php echo htmlspecialchars($product['prix']); ?></p>
        <p>Stock: <?php echo htmlspecialchars($product['quantite_stock']); ?></p>
        <form id="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <div class="form-group d-flex align-items-center">
                <label for="quantityi-<?php echo $product['id']; ?>" class="mr-2 mb-0">Quantity</label>
                <input type="number" class="form-control" id="quantityi-<?php echo $product['id']; ?>" name="quantityi" min="1" value="1" style="width: 60px;">
            </div>
            <button type="submit" class="btn btn-primary mt-2">Add to Cart</button>
        </form>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#add-to-cart-form').on('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(this);

        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); // Afficher la réponse du serveur pour le debug
            // Mettre à jour le compteur du panier dans la navbar
            var cartItemCount = parent.document.getElementById('cartItemCount');
            var currentCount = parseInt(cartItemCount.textContent) || 0;
            var addedQuantity = parseInt(formData.get('quantityi')) || 1;
            cartItemCount.textContent = currentCount + addedQuantity;

            // Afficher une alerte de succès
            Swal.fire({
                          position: "top-center",
                          icon: "success",
                          title: "The product is added to your basket",
                          showConfirmButton: false,
                          timer: 1500
                        });
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>
