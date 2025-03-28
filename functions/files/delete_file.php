<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

header('Content-Type: application/json');

// Verifica se la richiesta è di tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

// Verifica se il nome del file è stato specificato
if (!isset($_POST['filename']) || empty($_POST['filename'])) {
    echo json_encode(['error' => 'Nome file non specificato']);
    exit;
}

// Ottieni il percorso della cartella corrente
$currentFolder = isset($_POST['current_folder']) ? trim($_POST['current_folder']) : '';
// Rimuovi eventuali caratteri di nuova riga o spazi problematici
$currentFolder = str_replace(["\r", "\n", "\t"], '', $currentFolder);

// Definisci la directory di upload
$uploadDir = BASE_PATH . "/uploads/";
if (!empty($currentFolder)) {
    $uploadDir .= $currentFolder . '/';
}

// Pulisci il nome del file
$filename = basename($_POST['filename']);

// Se il filename contiene già il percorso completo, estrai solo il nome del file
if (strpos($filename, '/') !== false) {
    $parts = explode('/', $filename);
    $filename = end($parts);
}

// Costruisci il percorso completo del file
$filePath = $uploadDir . $filename;

// Normalizza il percorso per sicurezza
$realFilePath = realpath($filePath);
$realUploadDir = realpath($uploadDir);

// Verifica che il file si trovi all'interno della directory di upload
if ($realFilePath === false || strpos($realFilePath, $realUploadDir) !== 0) {
    echo json_encode(['error' => 'Percorso file non valido']);
    exit;
}

// Verifica che il file esista
if (!file_exists($realFilePath)) {
    echo json_encode(['error' => 'File non trovato']);
    exit;
}

// Verifica se il file è scrivibile
if (!is_writable($realFilePath)) {
    echo json_encode(['error' => 'Permessi insufficienti per eliminare il file']);
    exit;
}

// Prova a eliminare il file
try {
    if (unlink($realFilePath)) {
        echo json_encode(['success' => true, 'message' => 'File eliminato con successo']);
    } else {
        echo json_encode(['error' => 'Impossibile eliminare il file']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;