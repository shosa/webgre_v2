<?php
require_once '../../config/config.php';
session_start();

// Controlla se è stato caricato un file
if (isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $userId = $_SESSION['user_id']; // Assumendo che l'ID utente sia in sessione

    // Verifica il tipo di immagine
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (in_array($file['type'], $allowed_types)) {
        // Imposta il percorso di destinazione
        $destination = BASE_PATH . '/img/users/' . $userId . '.png'; // Salva come PNG

        // Sposta il file caricato
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore durante il caricamento']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Formato file non supportato']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nessun file inviato']);
}
