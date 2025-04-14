<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];
    $payment_method = $_POST['payment_method'];

    // Enregistrer les informations de livraison dans la base de données
    $sql = "INSERT INTO orders (first_name, last_name, address, zip_code, country, payment_method) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $first_name, $last_name, $address, $zip_code, $country, $payment_method);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    if ($payment_method === 'card') {
        // Traitement du paiement par carte
        $card_number = $_POST['card_number'];
        $card_expiry = $_POST['card_expiry'];
        $card_cvc = $_POST['card_cvc'];

        // Intégration du traitement du paiement par carte
        // Utilisez une bibliothèque comme Stripe ou autre

        // Exemple simplifié (à ne pas utiliser en production)
        if ($card_number && $card_expiry && $card_cvc) {
            // Simuler une réussite de paiement
            $payment_success = true;
        } else {
            $payment_success = false;
        }

        if ($payment_success) {
            echo "<script>alert('Payment successful!'); window.location.href = 'success.php';</script>";
        } else {
            echo "<script>alert('Payment failed. Please try again.'); window.location.href = 'checkout.php';</script>";
        }
    } elseif ($payment_method === 'paypal') {
        // Rediriger vers PayPal pour le paiement
        $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        $return_url = 'http://yourwebsite.com/success.php';
        $cancel_url = 'http://yourwebsite.com/cancel.php';
        $notify_url = 'http://yourwebsite.com/ipn.php';

        echo '<form action="' . $paypal_url . '" method="post" name="paypalForm" id="paypalForm">
                <input type="hidden" name="business" value="your-paypal-email@example.com">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="item_name" value="Order ' . $order_id . '">
                <input type="hidden" name="amount" value="0.01">
                <input type="hidden" name="currency_code" value="USD">
                <input type="hidden" name="return" value="' . $return_url . '">
                <input type="hidden" name="cancel_return" value="' . $cancel_url . '">
                <input type="hidden" name="notify_url" value="' . $notify_url . '">
              </form>
              <script>document.getElementById("paypalForm").submit();</script>';
    }
}
?>
