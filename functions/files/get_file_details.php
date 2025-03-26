<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

// Define the upload directory
$currentFolder = isset($_GET['folder']) ? $_GET['folder'] : '';
$uploadDir = BASE_PATH . '/uploads/' . $currentFolder;

if (!isset($_GET['filename'])) {
    echo json_encode(['error' => 'Nome file non specificato']);
    exit;
}

$filename = basename($_GET['filename']); // Sanitize filename
$filePath = $uploadDir . '/' . $filename;

if (!file_exists($filePath)) {
    echo json_encode(['error' => 'File non trovato']);
    exit;
}

try {
    $details = [
        'size' => filesize($filePath),
        'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
        'permissions' => substr(sprintf('%o', fileperms($filePath)), -4)
    ];

    echo json_encode($details);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;