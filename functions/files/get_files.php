<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

$uploadDir = BASE_PATH . '/uploads/';

try {
    $files = array_diff(scandir($uploadDir), array('..', '.'));
    
    $filesList = [];
    foreach ($files as $file) {
        $filePath = $uploadDir . $file;
        
        if (is_file($filePath)) {
            $filesList[] = [
                'name' => $file,
                'size' => filesize($filePath),
                'modified' => date('Y-m-d H:i:s', filemtime($filePath))
            ];
        }
    }

    // Sort files by modification time (newest first)
    usort($filesList, function($a, $b) {
        return strtotime($b['modified']) - strtotime($a['modified']);
    });

    echo json_encode($filesList);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;