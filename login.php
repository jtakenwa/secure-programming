<?php
//session_start();
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $csrf_token = $_POST['csrf_token'];

    // CSRF token validation
    if ($csrf_token !== $_SESSION['csrf_token']) {
        $_SESSION['alert_message_login'] = 'CSRF token validation failed';
        header("Location: sign-in.php");
        exit();
    }

    $sql = "SELECT id, email, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            // Redirect to a logged-in page
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['alert_message_login'] = 'Invalid email or password';
        }
    } else {
        $_SESSION['alert_message_login'] = 'Invalid email or password';
    }
    header("Location: sign-in.php");
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['alert_message_login'])) {
    $alert_message_login = $_SESSION['alert_message_login'];
    unset($_SESSION['alert_message_login']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
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
                <div class="jaas-logo text-center"></div>
                <div class="login-box">
                    <h2 class="text-center">Sign-In</h2>
                    <form id="loginForm" action="sign-in.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span id="toggle-password" class="toggle-password">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <?php if (isset($alert_message_login)): ?>
                            <script>
                                Swal.fire({
                                    title: 'Login Error!',
                                    text: '<?= $alert_message_login ?>',
                                    icon: 'error'
                                });
                            </script>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary btn-block center-button">Sign In</button>
                        <div class="text-center">
                            <a href="sign-up.php" id="register-link">Create your account</a>
                        </div>
                        <div class="text-center">
                            <a href="forgot-password.php" id="forgot-password-link">Forgot your password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var passwordField = document.getElementById('password');
            var password = passwordField.value;
            var hashedPassword = CryptoJS.SHA512(passwordField.value).toString();

            if (!validatePassword(password)) {
                Swal.fire({
                                    title: 'Login Error!',
                                    text: 'The password must contain at least 9 characters, including at least one uppercase letter, one lowercase letter.',
                                    icon: 'error'
                                });
            
            return;
        }
            passwordField.value = hashedPassword;
            this.submit();
        });
        function validatePassword(password) {
        var minLength = 9;
        var hasUpperCase = /[A-Z]/.test(password);
        var hasLowerCase = /[a-z]/.test(password);
        var hasDigit = /\d/.test(password);

        
        
        return password.length >= minLength && hasUpperCase && hasLowerCase && hasDigit;
    }
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
