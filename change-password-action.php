<?php
// Inclure le fichier de connexion à la base de données et les fonctions nécessaires
require_once 'config.php'; // Assure-toi d'ajuster ce chemin selon ta configuration

// Vérifier si les données POST ont été envoyées
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier si les champs email, ancien mot de passe et nouveau mot de passe ne sont pas vides
    if (!empty($_POST['email']) && !empty($_POST['old_password']) && !empty($_POST['new_password'])) {
        // Appeler la fonction changePassword avec les données du formulaire
        changePassword($_POST['email'], $_POST['old_password'], $_POST['new_password']);
    } else {
        // Afficher un message d'erreur si des champs sont vides
        echo "Veuillez saisir tous les champs.";
    }
} else {
    // Redirection si la requête n'est pas de type POST
    header("Location: change-password.html");
    exit();
}
?>
