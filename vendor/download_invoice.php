<?php
// download_invoice.php

if (!isset($_GET['file'])) {
    die('Aucun fichier spécifié.');
}

$file = $_GET['file'];
$file_path = 'invoices/' . basename($file);

if (!file_exists($file_path)) {
    die('Le fichier n\'existe pas.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();
?>
