<?php
require_once 'config.php';
require_once 'functions.php';
//session_start();

header('Content-Type: application/json');

// Désactiver l'affichage des erreurs et les enregistrer dans un fichier de log
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php-error.log'); // Assurez-vous que ce chemin est accessible en écriture

$response = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];

        // Vérifier si l'email existe dans la base de données
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $token = bin2hex(random_bytes(50)); // Générer un token sécurisé
            $expires = date('U') + 1800; // Le lien expire dans 30 minutes

            // Supprimer les entrées existantes pour cet email
            $sql = "DELETE FROM password_resets WHERE email = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("s", $email);
            $stmt->execute();

            // Insérer une nouvelle entrée
            $sql = "INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("ssi", $email, $token, $expires);
            $stmt->execute();

            // Créer le lien de réinitialisation
            $reset_link = "http://10.211.55.3/web-shop/reset-password.php?token=" . $token;

            // Envoyer l'email avec le lien de réinitialisation
            if (sendPasswordResetEmail($email, $reset_link)) {
                $response = [
                    'status' => 'success',
                    'message' => 'A password reset link has been sent to your email.'
                ];
            } else {
                $response = [
                    'status' => 'success',
                    'message' => 'A password reset link has been sent to your email.'
                ];
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => 'No account found with that email.'
            ];
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Invalid request method.'
        ];
    }
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ];
    error_log($e->getMessage()); // Enregistrer les erreurs dans le fichier de log
}

echo json_encode($response);
?>
