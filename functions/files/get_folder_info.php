<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

$uploadDir = BASE_PATH . '/uploads/';

// Get folder information
$files = array_diff(scandir($uploadDir), array('..', '.'));
$totalFiles = count($files);
$totalSize = 0;
$fileTypes = [];

foreach ($files as $file) {
    $filePath = $uploadDir . $file;
    
    if (is_file($filePath)) {
        $totalSize += filesize($filePath);
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $fileTypes)) {
            $fileTypes[] = $ext;
        }
    }
}

$response = [
    'totalFiles' => $totalFiles,
    'totalSize' => $totalSize,
    'fileTypes' => $fileTypes
];

echo json_encode($response);
exit;