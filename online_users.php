<?php
require_once 'config.php';

$sql2 = "SELECT COUNT(*) AS online_count FROM users WHERE is_online = 1";
$result2 = $conn->query($sql2);
$row = $result2->fetch_assoc();
$online_count = $row['online_count'];

header('Content-Type: application/json');
echo json_encode(['online_count' => $online_count]);
?>
