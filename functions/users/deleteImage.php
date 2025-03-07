<?php
require_once '../../config/config.php';
$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['userId'];

// Percorso di base all'immagine dell'utente (modifica in base alla tua struttura)
$imageBasePath = BASE_PATH . "/img/users/{$userId}";

// Estensioni di immagine da verificare
$extensions = ['jpg', 'jpeg', 'png'];

// Flag per tenere traccia del successo dell'eliminazione
$imageDeleted = false;

foreach ($extensions as $ext) {
    $imagePath = "{$imageBasePath}.{$ext}";

    if (file_exists($imagePath)) {
        // Se il file esiste, prova a cancellarlo
        if (unlink($imagePath)) {
            $imageDeleted = true;
            break; // Esci dal ciclo se l'immagine viene eliminata con successo
        }
    }
}

// Risposta JSON
if ($imageDeleted) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Immagine non trovata o errore durante l\'eliminazione']);
}
?>