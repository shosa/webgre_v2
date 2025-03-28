<?php
session_start();
require_once '../../config/config.php';

// Define the upload directory
$currentFolder = isset($_GET['folder']) ? trim($_GET['folder']) : '';
// Rimuovi tutti i caratteri di nuova riga e whitespace problematici
$currentFolder = str_replace(["\r", "\n", "\t"], '', $currentFolder);
$uploadDir = BASE_PATH . "/uploads/" . $currentFolder;

if (!isset($_GET['filename'])) {
    echo json_encode(['error' => 'Nome file non specificato']);
    exit;
}

// Pulizia completa del nome file
$filename = $_GET['filename'];
$filename = str_replace(["\r", "\n", "\t"], '', $filename); // Rimuovi caratteri newline
$filename = trim($filename); // Rimuovi spazi iniziali e finali
$filename = basename($filename); // Sanitize filename

// Costruisci il percorso con DIRECTORY_SEPARATOR per essere sicuri
$filePath = realpath($uploadDir) . DIRECTORY_SEPARATOR . $filename;

// Debug - Stampa i valori attuali
error_log("BASE_PATH: " . BASE_PATH);
error_log("Current Folder: " . $currentFolder);
error_log("Upload Dir: " . $uploadDir);
error_log("Filename: " . $filename);
error_log("File Path: " . $filePath);

// Verifica se la directory esiste
if (!is_dir(realpath($uploadDir))) {
    echo json_encode(['error' => 'Directory non trovata']);
    echo json_encode(['directory' => $uploadDir]);
    exit;
}

// Elenco tutti i file della directory per debug
$files = scandir(realpath($uploadDir));
error_log("Files in directory: " . implode(", ", $files));

if (!file_exists($filePath)) {
    // Cerca il file ignorando la capitalizzazione e spazi extra
    foreach ($files as $file) {
        $normalizedFilename = strtolower(str_replace(' ', '', $filename));
        $normalizedFile = strtolower(str_replace(' ', '', $file));
        
        if ($normalizedFile === $normalizedFilename) {
            $filePath = realpath($uploadDir) . DIRECTORY_SEPARATOR . $file;
            break;
        }
    }
    
    // Se ancora non trovato
    if (!file_exists($filePath)) {
        echo json_encode(['error' => 'File non trovato']);
        echo json_encode(['error' => $filePath]);
        exit;
    }
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