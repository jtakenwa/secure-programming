<?php
require_once 'config.php';
require_once 'functions.php';
//session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? null;
    $newPassword = $_POST['password'];
    $hashedPassword = hash('sha512', $newPassword);

    if ($token) {
        // Vérifier si le token est valide
        $sql = "SELECT email FROM password_resets WHERE token = ? AND expires > ?";
        $stmt = $conn->prepare($sql);
        $current_time = date('U');
        $stmt->bind_param("si", $token, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        $reset = $result->fetch_assoc();

        if ($reset) {
            $email = $reset['email'];
            
            // Mettre à jour le mot de passe de l'utilisateur
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hashedPassword, $email);
            $stmt->execute();

            // Supprimer le token de réinitialisation
            $sql = "DELETE FROM password_resets WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $_SESSION['message'] = [
                'title' => 'Success!',
                'text' => 'Your password has been reset.',
                'icon' => 'success'
            ];
        } else {
            $_SESSION['message'] = [
                'title' => 'Error!',
                'text' => 'Invalid or expired token.',
                'icon' => 'error'
            ];
        }
    } else {
        $_SESSION['message'] = [
            'title' => 'Error!',
            'text' => 'No token provided.',
            'icon' => 'error'
        ];
    }

    header('Location: reset-password.php');
    exit();
}

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <div class="login-box">
                    <h2 class="text-center">Reset Password</h2>
                    <form id="resetPasswordForm" action="reset-password.php" method="post">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span id="toggle-password" class="toggle-password">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block center-button">Reset Password</button>
                        <a href="index.php" class="btn btn-primary btn-block center-button">Back To Shop</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if ($message): ?>
    <script>
        Swal.fire({
            title: '<?php echo $message['title']; ?>',
            text: '<?php echo $message['text']; ?>',
            icon: '<?php echo $message['icon']; ?>'
        });
    </script>
    <?php endif; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('resetPasswordForm').addEventListener('submit', function(event) {
            var password = document.getElementById('password').value;
            var errorMessage = '';

            // Vérifier la longueur du mot de passe
            if (password.length < 9) {
                errorMessage += 'The password must be at least 9 characters long.\n';
            }

            // Vérifier s'il contient des majuscules
            if (!/[A-Z]/.test(password)) {
                errorMessage += 'The password must contain at least one capital letter.\n';
            }

            // Vérifier s'il contient des minuscules
            if (!/[a-z]/.test(password)) {
                errorMessage += 'The password must contain at least one lowercase letter.\n';
            }

            // Vérifier s'il contient des chiffres
            if (!/[0-9]/.test(password)) {
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
    document.getElementById('toggle-password').addEventListener('click', function() {
        var passwordField = document.getElementById('password');
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
