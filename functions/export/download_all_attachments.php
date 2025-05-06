<?php
/**
 * File: download_all_attachments.php
 * 
 * Crea un file ZIP contenente tutti gli allegati di un DDT e lo invia al browser per il download.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

// Recupera e valida il progressivo
$progressivo = filter_input(INPUT_GET, 'progressivo', FILTER_VALIDATE_INT);
if (!$progressivo) {
    $_SESSION['failure'] = "ID documento non valido";
    header('location: documenti.php');
    exit();
}

// Recupera i file Excel presenti nella directory
$dir = 'src/' . $progressivo;
$files = glob($dir . '/*.xlsx');

// Verifica se ci sono file da inserire nell'archivio
if (empty($files)) {
    $_SESSION['failure'] = "Nessun allegato disponibile per questo DDT";
    header('location: continue_ddt.php?progressivo=' . $progressivo);
    exit();
}

// Nome del file ZIP temporaneo
$zipName = 'Allegati_DDT_' . $progressivo . '_' . date('Ymd_His') . '.zip';
$zipPath = sys_get_temp_dir() . '/' . $zipName;

// Crea un nuovo archivio ZIP
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    $_SESSION['failure'] = "Impossibile creare l'archivio ZIP";
    header('location: continue_ddt.php?progressivo=' . $progressivo);
    exit();
}

// Aggiungi i file all'archivio
foreach ($files as $file) {
    // Il secondo parametro è il nome del file all'interno dell'archivio
    $zip->addFile($file, basename($file));
}

// Chiudi l'archivio ZIP
$zip->close();

// Verifica se il file esiste
if (!file_exists($zipPath)) {
    $_SESSION['failure'] = "Errore nella creazione dell'archivio ZIP";
    header('location: continue_ddt.php?progressivo=' . $progressivo);
    exit();
}

// Invia l'archivio al browser per il download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
header('Pragma: no-cache');
header('Expires: 0');

// Leggi il file e invialo al browser
readfile($zipPath);

// Elimina il file temporaneo
unlink($zipPath);
exit();
?>