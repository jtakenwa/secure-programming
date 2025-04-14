<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4">
                <div class="login-box">
                    <h2 class="text-center">Forgot Password</h2>
                    <form id="forgotPasswordForm">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block center-button">Send Reset Link</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#forgotPasswordForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: 'send-reset-link.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    console.log(response); // Afficher la réponse dans la console pour déboguer
                    Swal.fire({
                        title: response.status === 'success' ? 'Success!' : 'Error!',
                        text: response.message,
                        icon: response.status
                    }).then(function() {
                        if (response.status === 'success') {
                            window.location = 'login.php';
                        }
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(textStatus, errorThrown); // Afficher les erreurs dans la console
                    Swal.fire({
                        title: 'Success!',
                        text: 'A password reset link has been sent to your email.',
                        icon: 'success'
                    }).then(function() {
                            window.location = 'login.php';
                        
                    });
                }
            });
        });
    });
</script>

</body>
</html>
