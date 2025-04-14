<?php
//session_start();
require_once 'config.php';
require_once 'functions.php';

if (isset($_SESSION['alert_message'])) {
    $alert_message = $_SESSION['alert_message'];
    //echo "<script>alert('$alert_message');</script>";
    unset($_SESSION['alert_message']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>
<body>
    <!-- Container pour centrer le contenu -->
    <div class="container">
        <div class="row">
            <div class="col-md-5 offset-md-4">
                <div class="jaas-logo text-center"></div>
                <div class="container center-content">
                    <div class="register-box login-box">
                        <h2 class="text-center">Create Your JAAS'Shop Account</h2>
                        <form id="register-form">
                            <div class="form-group">
                                <label for="name">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email-register">Email</label>
                                <input type="email" class="form-control" id="email-register" name="email" aria-describedby="emailHelp" required>
                            </div>
                            
                            <input type="hidden" id="screen_resolution" name="screen_resolution">
                            <input type="hidden" id="os" name="os">
                            <button type="submit" class="btn btn-primary btn-block center-button">Create Account</button>
                            <div class="text-center">
                                By creating an account, you agree to JAAS'Shop <a href="#">Conditions of Use</a>.
                            </div>
                            <br>
                            <div class="text-center">
                                <a href="login.php" id="login-link">Already have an account? Sign In</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function getOS() {
                var userAgent = window.navigator.userAgent,
                    platform = window.navigator.platform,
                    macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K'],
                    windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE'],
                    iosPlatforms = ['iPhone', 'iPad', 'iPod'],
                    os = null;

                if (macosPlatforms.indexOf(platform) !== -1) {
                    os = 'Mac OS';
                } else if (iosPlatforms.indexOf(platform) !== -1) {
                    os = 'iOS';
                } else if (windowsPlatforms.indexOf(platform) !== -1) {
                    os = 'Windows';
                } else if (/Android/.test(userAgent)) {
                    os = 'Android';
                } else if (!os && /Linux/.test(platform)) {
                    os = 'Linux';
                }

                return os;
            }

            $('#register-form').on('submit', function(event) {
                event.preventDefault(); // Empêche le comportement par défaut du formulaire

                $('#screen_resolution').val(window.screen.width + 'x' + window.screen.height);
                $('#os').val(getOS());

                var formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: 'register.php',
                    data: formData,
                    success: function(response) {
                        //alert(response); // Affiche la réponse de register.php
                        if(response=="Registration successful. Please verify your email address."){
                            
                            Swal.fire({
                            title: 'Registration!',
                            text: response,
                            icon: 'success'
                            });
                        } 
                        else {
                            Swal.fire({
                            title: 'Registration!',
                            text: response,
                            icon: 'error'
                            });
                        }



                    },
                    error: function() {
                        //alert('Registration failed. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>

