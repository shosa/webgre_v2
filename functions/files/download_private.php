<?php
session_start();
require_once '../../config/config.php';

if (!isset($_GET['token'])) {
    die('Token non specificato');
}

$token = $_GET['token'];

// Validate token
if (!isset($_SESSION['private_links'][$token])) {
    die('Link non valido o scaduto');
}

$linkInfo = $_SESSION['private_links'][$token];

// Check expiration
if (time() > $linkInfo['expiration']) {
    unset($_SESSION['private_links'][$token]);
    die('Link scaduto');
}

$uploadDir = BASE_PATH . '/uploads/';
$filePath = $uploadDir . $linkInfo['filename'];

if (!file_exists($filePath)) {
    die('File non trovato');
}

// For private links, add additional authentication if needed
if ($linkInfo['is_private'] && !isset($_SESSION['user_id'])) {
    die('Accesso non autorizzato');
}

// Force file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $linkInfo['filename'] . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);

// Optional: Remove token after single use
unset($_SESSION['private_links'][$token]);
exit;