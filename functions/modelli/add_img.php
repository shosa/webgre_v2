<?php
// Percorso in cui verrà salvata l'immagine
$targetDirectory = '../../src/img/';

if ($_FILES['immagine']) {
    $file = $_FILES['immagine'];
    $fileName = basename($file['name']);
    $targetPath = $targetDirectory . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // L'immagine è stata caricata con successo
        echo json_encode(['success' => true]);
    } else {
        // Errore durante il caricamento dell'immagine
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>