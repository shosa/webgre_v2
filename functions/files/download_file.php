<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

$uploadDir = BASE_PATH . '/uploads/';

if (!isset($_GET['filename'])) {
    die('Nome file non specificato');
}

$filename = basename($_GET['filename']); // Sanitize filename
$filePath = $uploadDir . $filename;

if (!file_exists($filePath)) {
    die('File non trovato');
}

// Force file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;