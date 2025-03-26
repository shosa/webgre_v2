<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

$currentFolder = isset($_GET['folder']) ? $_GET['folder'] : '';
$uploadDir = BASE_PATH . '/uploads/' . $currentFolder;

try {
    // Assicurati che il percorso sia sicuro e non esca dalla directory base
    $realUploadDir = realpath(BASE_PATH . '/uploads/');
    $realCurrentDir = realpath($uploadDir);

    if (strpos($realCurrentDir, $realUploadDir) !== 0) {
        throw new Exception('Percorso non valido');
    }

    $items = array_diff(scandir($uploadDir), array('..', '.'));
    
    $filesList = [];
    $foldersList = [];

    foreach ($items as $item) {
        $itemPath = $uploadDir . '/' . $item;
        
        if (is_dir($itemPath)) {
            $foldersList[] = [
                'name' => $item,
                'type' => 'folder'
            ];
        } elseif (is_file($itemPath)) {
            $filesList[] = [
                'name' => $item,
                'size' => filesize($itemPath),
                'modified' => date('Y-m-d H:i:s', filemtime($itemPath))
            ];
        }
    }

    // Ordina prima le cartelle, poi i file per data di modifica
    usort($filesList, function($a, $b) {
        return strtotime($b['modified']) - strtotime($a['modified']);
    });

    $result = [
        'folders' => $foldersList,
        'files' => $filesList,
        'current_path' => $currentFolder
    ];

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;