<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coupon_code = $_POST['coupon_code'];
    
    // Connectez-vous à la base de données
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed']));
    }
    
    // Vérifier si le coupon existe et est valide
    $sql = "SELECT * FROM coupons WHERE code = ? AND expiry_date >= CURDATE() AND (usage_limit IS NULL OR times_used < usage_limit)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $coupon_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $coupon = $result->fetch_assoc();
        $discount = $coupon['discount_percentage'];
        
        // Augmenter le compteur d'utilisation du coupon
        $update_sql = "UPDATE coupons SET times_used = times_used + 1 WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $coupon['id']);
        $update_stmt->execute();
        
        echo json_encode(['success' => true, 'discount' => $discount]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon']);
    }
    
    $stmt->close();
    $conn->close();
}
?>
