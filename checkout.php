<?php
//session_start();
require_once 'config.php';
require_once 'functions.php';
require 'vendor/autoload.php'; // Inclure l'autoloader de Composer pour PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['cart']) || $_SESSION['cart'] == null) {
    header('Location: index.php');
    exit();
}

// Connectez-vous à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer l'email de l'utilisateur depuis la base de données
$user_id = $_SESSION['user_id'];
$sql_get_email = "SELECT email FROM users WHERE id = ?";
$stmt_get_email = $conn->prepare($sql_get_email);
$stmt_get_email->bind_param("i", $user_id);
$stmt_get_email->execute();
$result = $stmt_get_email->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_email = $row['email'];
} else {
    echo 'Erreur : Impossible de récupérer l\'email de l\'utilisateur.';
    exit();
}

// Fonction pour générer un numéro de commande unique
function generateOrderNumber() {
    return uniqid('ORD-');
}

// Fonction pour récupérer les détails des produits depuis la base de données
function getProductDetails($product_id) {
    global $conn;
    $sql = "SELECT nom, prix, image_url FROM produits WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}


// Traitement du formulaire de paiement
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipping_method = $_POST['shipping_method'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $zip_code = $_POST['zip_code'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $payment_method = 'card'; // Nous avons seulement le paiement par carte
    $data_protection = isset($_POST['data_protection']) ? 1 : 0;
    //$card_number,$expiry_date,$cvv
    $card_number = isset($_POST['card_number']) ? $_POST['card_number'] : '';
    $expiry_date = isset($_POST['card_expiry']) ? $_POST['card_expiry'] : '';
    $cvv = isset($_POST['card_cvc']) ? $_POST['card_cvc'] : '';

    if (!$data_protection) {
        echo "<script>alert('Vous devez accepter la protection des données.');</script>";
    } else {
        $order_number = generateOrderNumber();
        $subtotal = calculateCartTotal();


        $discountAmount = floatval($_COOKIE['discountAmount']);

        // Ajuster le total selon la méthode d'expédition
        switch ($shipping_method) {
            case 'DHL Express':
                $shipping_cost = 44;
                break;
            case 'DPD':
                $shipping_cost = 19;
                break;
            case 'DHL':
            default:
                $shipping_cost = 0;
                break;
        }

        $total = $subtotal + $shipping_cost - $discountAmount;
        //card_number,card_expiry,card_cvc
        // Insérer la commande dans la base de données
        $sql_insert_order = "INSERT INTO orders (order_number, user_id, total, shipping_method, first_name, last_name, address, zip_code, city, country, payment_method,card_number,card_expiry,card_cvc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?)";
        $stmt_insert_order = $conn->prepare($sql_insert_order);
        $stmt_insert_order->bind_param("sissssssssssss", $order_number, $user_id, $total, $shipping_method, $first_name, $last_name, $address, $zip_code, $city, $country, $payment_method,$card_number,$expiry_date,$cvv);
        $stmt_insert_order->execute();
        $order_id = $stmt_insert_order->insert_id;

        // Insérer les détails de la commande dans la table order_details
        if(isset($_SESSION['cart'])){
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product = getProductDetails($product_id);
                if ($product) {
                    $discount = min(0.08 * floor($quantity / 8), 0.16);
                    $discounted_price = $product['prix'] * (1 - $discount);
                    $total_item_price = $discounted_price * $quantity;
                    $sql_insert_order_details = "INSERT INTO order_details (order_id, product_id, quantity, price, discount) VALUES (?, ?, ?, ?, ?)";
                    $stmt_insert_order_details = $conn->prepare($sql_insert_order_details);
                    $stmt_insert_order_details->bind_param("iiidd", $order_id, $product_id, $quantity, $product['prix'], $discount);
                    $stmt_insert_order_details->execute();
                }
            }
        }

        // Vérifiez si le dossier des factures existe et créez-le si nécessaire
        $invoice_dir =  __DIR__ . '/invoices';
        if (!is_dir($invoice_dir)) {
            mkdir($invoice_dir, 0777, true);
        }

        // Fonction pour envoyer l'email de confirmation de commande
        function sendOrderConfirmationEmail($user_email, $order_number, $order_details, $pdf_path) {
            $mail = new PHPMailer(true);

            try {
                // Configurations du serveur
                $mail->isSMTP();
                $mail->Host       = 'smtp.free.fr'; // Remplacez par votre serveur SMTP
                $mail->SMTPAuth   = true;
                $mail->Username   = 'jaasiel@free.fr'; // Remplacez par votre email SMTP
                $mail->Password   = 'Uec-unT-3c1-Zmq'; // Remplacez par votre mot de passe SMTP
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinataires
                $mail->setFrom('jaasiel@free.fr', "JAAS'Shop");
                $mail->addAddress($user_email); // Ajouter le destinataire

                // Pièces jointes
                $mail->addAttachment($pdf_path); // Ajouter le PDF de la facture

                // Contenu de l'e-mail
                $mail->isHTML(true);
                $mail->Subject = 'Order confirmation #' . $order_number;
                $mail->Body    = $order_details;
                $mail->AltBody = strip_tags($order_details);

                $mail->send();
                echo 'L\'e-mail de confirmation a été envoyé avec succès.';
            } catch (Exception $e) {
                echo "L'e-mail de confirmation n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
            }
        }

        // Génération du PDF de la facture
        require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
        require('fpdf/fpdf.php');  // Inclure FPDF depuis le bon chemin

        //require_once('tcpdf/tcpdf.php'); // Inclure TCPDF depuis le bon chemin

        class PDF extends TCPDF {
            // Définir le contenu du Header
            public function Header() {
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(0, 10, 'Facture', 0, 1, 'C');
                $this->Ln(10);
            }

            // Fonction pour afficher un titre de section
            public function SectionTitle($label) {
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(0, 10, $label, 0, 1, 'L');
                $this->Ln(4);
            }

            // Fonction pour afficher une ligne de produit
            public function ProductLine($product) {
                $this->SetFont('helvetica', '', 10);
                $this->Cell(80, 10, $product['nom'], 1);
                $this->Cell(20, 10, $product['quantity'], 1, 0, 'C');
                $this->Cell(30, 10, '€' . number_format($product['prix'], 2), 1, 0, 'R');
                $this->Cell(30, 10, '' . number_format($product['discount'] * 100, 2) . '%', 1, 0, 'R');
                $this->Cell(30, 10, '€' . number_format($product['total'], 2), 1, 0, 'R');
                $this->Ln(10);
            }
        }

        // Créer une nouvelle instance de PDF
        $pdf = new PDF();

        // Ajouter une page
        $pdf->AddPage();

        // Ajouter le logo dans l'en-tête
        $logo_path = 'logo.png'; // Chemin absolu de l'image du logo
        $pdf->Image($logo_path, 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $pdf->Ln(10);

        // Définir la police et la taille pour le titre principal
        $pdf->SetFont('helvetica', 'B', 14);

        // Afficher le titre de la facture
        $pdf->Cell(0, 10, 'Facture de la commande #' . $order_number, 0, 1, 'C');
        $pdf->Ln(10);

        // Afficher les informations sur le client
        $pdf->SectionTitle('Informations sur le client');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Name: ' . $first_name . ' ' . $last_name, 0, 1);
        $pdf->Cell(0, 10, 'Address: ' . $address . ', ' . $zip_code . ' ' . $city, 0, 1);
        $pdf->Cell(0, 10, 'Country: ' . $country, 0, 1);
        $pdf->Ln(10);

        // Afficher les détails de la commande
        $pdf->SectionTitle('Order details');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(80, 10, 'Product', 1);
        $pdf->Cell(20, 10, 'Quantity', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Unit price', 1, 0, 'R');
        $pdf->Cell(30, 10, 'Discount', 1, 0, 'R');
        $pdf->Cell(30, 10, 'Total', 1, 0, 'R');
        $pdf->Ln(10);

        // Parcourir chaque produit dans le panier
        if(isset($_SESSION['cart'])){
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product = getProductDetails($product_id);
                if ($product) {
                    $discount = min(0.08 * floor($quantity / 8), 0.16);
                    $discounted_price = $product['prix'] * (1 - $discount);
                    $total_item_price = $discounted_price * $quantity;
                    $pdf->ProductLine([
                        'Name' => $product['nom'],
                        'Quantity' => $quantity,
                        'Price' => $product['prix'],
                        'Discount' => $discount,
                        'Total' => $total_item_price
                    ]);
                }
            }
        }

        $pdf->Ln(10);

        // Afficher les totaux
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Subtotal: € ' . number_format($subtotal, 2), 0, 1, 'R');
        $pdf->Cell(0, 10, 'Shipping Cost € ' . number_format($shipping_cost, 2), 0, 1, 'R');
        $pdf->Cell(0, 10, 'Discount: € ' . number_format($discountAmount, 2), 0, 1, 'R');
        $pdf->Cell(0, 10, 'Total: € ' . number_format($total, 2), 0, 1, 'R');

        // Sauvegarder le fichier PDF
        $invoice_path = $invoice_dir . '/facture_' . $order_number . '.pdf';
        $pdf->Output($invoice_path, 'F');

        // Préparer le contenu de l'email de confirmation
        $order_details = '<h1>Order confirmation</h1>';
        $order_details .= '<p>Thank you for your order. here are the details :</p>';
        $order_details .= '<p>Order number : ' . $order_number . '</p>';
        $order_details .= '<p>Discount : € ' . number_format($discountAmount, 2) . '</p>';
        $order_details .= '<p>Total : € ' . number_format($total, 2) . '</p>';
        $order_details .= '<p>Shipping Method : ' . $shipping_method . '</p>';
        $order_details .= '<h2>Product Details :</h2>';
        if(isset($_SESSION['cart'])){
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product = getProductDetails($product_id);
                if ($product) {
                    $order_details .= '<p>' . $product['nom'] . ' x ' . $quantity . ' = € ' . number_format($product['prix'] * $quantity, 2) . '</p>';
                }
            }
        }


        // Envoyer l'email de confirmation
        sendOrderConfirmationEmail($user_email, $order_number, $order_details, $invoice_path);

        // Vider le panier
        unset($_SESSION['cart']);

        header("Location: thank_you.php");

        echo "<script>alert('Your order has been successfully placed.');</script>";
    }

    
}

function calculateCartTotal() {
    global $conn;
    $total = 0;
    if(isset($_SESSION['cart'])){
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $product = getProductDetails($product_id);
            if ($product) {
                $discount = min(0.08 * floor($quantity / 8), 0.16); // 8% pour chaque 8 items, jusqu'à 16%
                $discounted_price = $product['prix'] * (1 - $discount);
                $total += $discounted_price * $quantity;
            }
        }
    }

    return $total;
}




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
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
<div class="container">
    <div class="row">
        <!-- Colonne de gauche (1.5 fois plus grande) -->
        <div class="col-md-8">
            <h1>Checkout</h1>
            <h2>Order Summary</h2>
            <table class="table">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php if(isset($_SESSION['cart'])){foreach ($_SESSION['cart'] as $product_id => $quantity): ?>
                    <?php $product = getProductDetails($product_id); ?>
                    <?php if ($product): ?>
                        <?php
                        // Calculate discount
                        $discount = min(0.08 * floor($quantity / 8), 0.16); // 8% pour chaque 8 items, jusqu'à 16%
                        $discounted_price = $product['prix'] * (1 - $discount);
                        $total_item_price = $discounted_price * $quantity;
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['nom']; ?>" width="50">
                                <?php echo $product['nom']; ?>
                            </td>
                            <td><?php echo $quantity; ?></td>
                            <td>€<?php echo number_format($product['prix'], 2); ?></td>
                            <td><?php echo ($discount * 100) . '%'; ?></td>
                            <td>€<?php echo number_format($total_item_price, 2); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; }?>
                <tr>
                    <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                    <td>€<span id="subtotal"><?php echo number_format(calculateCartTotal(), 2); ?></span></td>
                </tr>
                <tr colspan="4" id="discount_row" class="text-right" style="display: none;">
                    <td colspan="2"><strong>Discount</strong></td>
                    <td id="discount_amount">€ </td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right"><strong>Shipping Cost:</strong></td>
                    <td>€<span id="shipping-cost">0.00</span></td>
                </tr>
                
                


                <tr>
                    <td colspan="4" class="text-right"><strong>Total:</strong></td>
                    <td>€<span id="total"><?php echo number_format(calculateCartTotal(), 2); ?></span></td>
                </tr>
                </tbody>
            </table>

            <h2>Payment Information</h2>
            <div class="form-group">
                <label for="card_number">Card Number:</label>
                <input type="text" class="form-control" id="card_number" name="card_number" required>
            </div>
            <div class="form-group">
                <label for="card_expiry">Expiry Date:</label>
                <input type="text" class="form-control" id="card_expiry" name="card_expiry" required>
            </div>
            <div class="form-group">
                <label for="card_cvc">CVC:</label>
                <input type="text" class="form-control" id="card_cvc" name="card_cvc" required>
            </div>
        </div>

        <!-- Colonne de droite -->
        <div class="col-md-4">
            <h2>Shipping Information</h2>
            <form action="checkout.php" method="post" id="checkout-form">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <div class="form-group">
                    <label for="zip_code">ZIP Code:</label>
                    <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                </div>
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" class="form-control" id="city" name="city" required>
                </div>
                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" class="form-control" id="country" name="country" required>
                </div>
                <h2>Shipping Method</h2>
                <div class="form-group">
                    <label for="shipping_method">Select Shipping Method:</label>
                    <select class="form-control" id="shipping_method" name="shipping_method" required>
                        <option value="DHL">DHL</option>
                        <option value="DHL Express">DHL Express (+44€)</option>
                        <option value="DPD">DPD (+19€)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="coupon_code"><h3>Coupon Code:</h3></label>
                    <input type="text" class="form-control" id="coupon_code" name="coupon_code">
                    <button type="button" class="btn btn-success mt-2" id="apply_coupon">Apply Coupon</button>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="data_protection" name="data_protection" required>
                    <label class="form-check-label" for="data_protection">I agree to the data protection policy</label>
                </div>

                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
        </div>
    </div>
</div>
<script>
    var discountAmount =0;
document.getElementById('shipping_method').addEventListener('change', function() {
    var shippingCost = 0;
    switch (this.value) {
        case 'DHL Express':
            shippingCost = 44;
            break;
        case 'DPD':
            shippingCost = 19;
            break;
        case 'DHL':
        default:
            shippingCost = 0;
            break;
    }
    document.getElementById('shipping-cost').textContent = shippingCost.toFixed(2);
    var subtotal = parseFloat(document.getElementById('subtotal').textContent);
    var total = subtotal + shippingCost - discountAmount;
    document.getElementById('total').textContent = total.toFixed(2);
    // Envoyer discountAmount au serveur
    var xhr2 = new XMLHttpRequest();
    xhr2.open('POST', 'checkoutt.php', true); // Nouveau script PHP pour sauvegarder la remise
    xhr2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr2.send('discountAmount=' + discountAmount);
    
});
</script>
<script>
document.getElementById('apply_coupon').addEventListener('click', function() {
    var couponCode = document.getElementById('coupon_code').value;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'apply_coupon.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                var discount = response.discount;
                var subtotal = parseFloat(document.getElementById('subtotal').textContent);
                var shippingCost = parseFloat(document.getElementById('shipping-cost').textContent);
                discountAmount = subtotal * (discount / 100);
                var total = subtotal - discountAmount + shippingCost;

                // Afficher la ligne de remise et mettre à jour le montant
                document.getElementById('discount_row').style.display = 'table-row';
                document.getElementById('discount_amount').textContent = '-' + discountAmount.toFixed(2);
                document.getElementById('total').textContent = total.toFixed(2);

                document.cookie = "discountAmount= " + discountAmount ;
                
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: 'Coupon applied! Discount: ' + discount + '%',
                    showConfirmButton: false,
                    timer: 1500
                });
                
            } else {
                Swal.fire({
                    position: "top-center",
                    icon: "error",
                    title: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        }
    };
    xhr.send('coupon_code=' + couponCode);
});

</script>
<script> </script>




</body>
</html>
