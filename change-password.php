<?php
// Inclure le fichier de connexion à la base de données et les fonctions nécessaires
require_once 'config.php'; // Assure-toi d'ajuster ce chemin selon ta configuration
require_once 'functions.php';

// Initialiser une variable pour stocker le message d'erreur
$error_message = "";

// Vérifier si les données POST ont été envoyées
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier si les champs email, ancien mot de passe et nouveau mot de passe ne sont pas vides
    if (!empty($_POST['email']) && !empty($_POST['old_password']) && !empty($_POST['new_password'])) {
        // Appeler la fonction changePassword avec les données du formulaire
        changePassword($_POST['email'], $_POST['old_password'], $_POST['new_password']);
    } else {
        // Stocker un message d'erreur si des champs sont vides
        $error_message = "Veuillez saisir tous les champs.";
    }
}

if (isset($_SESSION['alert_message_chp'])) {
    $alert_message_chp = $_SESSION['alert_message_chp'];
    //echo "<script>alert('$alert_message');</script>";
    //<script>alert('$alert_message');</script>"
    $error_message1 = $alert_message_chp;
    unset($_SESSION['alert_message_chp']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        .form-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 70%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .toggle-password .fa-eye,
        .toggle-password .fa-eye-slash {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4">
                <div class="change-password-box">
                    <h2 class="text-center">Change Password</h2>
                    <!-- Afficher le message d'erreur -->
                    <?php if(isset($alert_message_chp)){
                        if($alert_message_chp == "Password changed successfully!") {
                            echo "<script>
                            Swal.fire({
                            title: 'Success!',
                            text: '$alert_message_chp',
                            icon: 'success'
                            });
                            </script>";
                        } else {
                            echo "<script>
                            Swal.fire({
                            title: 'Error!',
                            text: '$alert_message_chp',
                            icon: 'error'
                            });
                            </script>";
                        }
                    } ?>
                    <form id="change-password-form" action="change-password.php" method="post">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="old-password">Old Password</label>
                            <input type="password" class="form-control" id="old-password" name="old_password" required>
                            <span id="toggle-password-old" class="toggle-password">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="form-group">
                            <label for="new-password">New Password</label>
                            <input type="password" class="form-control" id="new-password" name="new_password" required>
                            <span id="toggle-password-new" class="toggle-password">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block center-button">Change Password</button>
                        <a href="index.php" class="btn btn-primary btn-block center-button">Back To Shop</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

    <script>
    document.getElementById('change-password-form').addEventListener('submit', function(event) {
        var newPassword = document.getElementById('new-password').value;
        var errorMessage = '';

        // Vérifier la longueur du mot de passe
        if (newPassword.length < 9) {
            errorMessage += 'The password must be at least 9 characters long.\n';
        }

        // Vérifier s'il contient des majuscules
        if (!/[A-Z]/.test(newPassword)) {
            errorMessage += 'The password must contain at least one capital letter.\n';
        }

        // Vérifier s'il contient des minuscules
        if (!/[a-z]/.test(newPassword)) {
            errorMessage += 'The password must contain at least one lowercase letter.\n';
        }

        // Vérifier s'il contient un chiffre
        if (!/[0-9]/.test(newPassword)) {
            errorMessage += 'The password must contain at least one number.\n';
        }

        // Afficher le message d'erreur et empêcher l'envoi du formulaire si des erreurs existent
        if (errorMessage) {
            Swal.fire({
                title: "Retry!",
                text: errorMessage,
                icon: "error"
            });
            event.preventDefault();
        }
    });
    </script>
    <script>
    document.getElementById('toggle-password-old').addEventListener('click', function() {
        var passwordField = document.getElementById('old-password');
        var icon = this.querySelector('i');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    </script>
    <script>
    document.getElementById('toggle-password-new').addEventListener('click', function() {
        var passwordField = document.getElementById('new-password');
        var icon = this.querySelector('i');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    </script>
</body>
</html>
