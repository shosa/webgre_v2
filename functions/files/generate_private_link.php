<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

$uploadDir = BASE_PATH . '/uploads/';

if (!isset($_POST['filename'])) {
    echo json_encode(['error' => 'Nome file non specificato']);
    exit;
}

$filename = $currentFolder ? $currentFolder . '/' . basename($_GET['filename']) : basename($_GET['filename']);
$filePath = BASE_PATH . '/uploads/' . $filename;
$isPrivate = isset($_POST['is_private']) && $_POST['is_private'] === 'true';

try {
    $filePath = $uploadDir . $filename;

    if (!file_exists($filePath)) {
        throw new Exception('File non trovato');
    }

    // Generate a unique, time-limited token
    $token = bin2hex(random_bytes(16));
    $expirationTime = time() + (24 * 60 * 60); // 24 hours

    // Store token in session or database
    // For simplicity, we'll use $_SESSION, but a database would be more robust
    if (!isset($_SESSION['private_links'])) {
        $_SESSION['private_links'] = [];
    }

    $_SESSION['private_links'][$token] = [
        'filename' => $filename,
        'expiration' => $expirationTime,
        'is_private' => $isPrivate
    ];

    // Generate the private link
    $privateLink = BASE_URL . '/download_private.php?token=' . $token;

    echo json_encode(['link' => $privateLink]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;