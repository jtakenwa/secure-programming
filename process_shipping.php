<?php
session_start();

// Vérifiez si le formulaire de livraison a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les informations de livraison du formulaire
    $fullName = $_POST['fullName'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postalCode = $_POST['postalCode'];

    // Enregistrez les informations de livraison dans la session ou dans la base de données, selon vos besoins
    $_SESSION['shippingInfo'] = [
        'fullName' => $fullName,
        'address' => $address,
        'city' => $city,
        'postalCode' => $postalCode
    ];

    // Rediriger vers la page de paiement
    header("Location: payment.php");
    exit();
} else {
    // Rediriger vers la page de checkout si le formulaire n'a pas été soumis
    header("Location: checkout.php");
    exit();
}
?>
