<?php
/**
 * File: completa_ddt.php
 * 
 * Aggiorna lo stato di un DDT a "Chiuso" e imposta first_boot a 0.
 */
session_start();
require_once '../../config/config.php';

require_once BASE_PATH . '/utils/helpers.php';


// Verifica che la richiesta sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

// Recupera e valida il progressivo
$progressivo = filter_input(INPUT_POST, 'progressivo', FILTER_VALIDATE_INT);
if (!$progressivo) {
    echo json_encode(['success' => false, 'message' => 'ID documento non valido']);
    exit();
}

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Aggiorna lo stato del documento a "Chiuso" e first_boot a 0
    $stmt = $conn->prepare("UPDATE exp_documenti SET stato = 'Chiuso', first_boot = 0 WHERE id = :id");
    $stmt->bindParam(':id', $progressivo, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result) {
      

        echo json_encode([
            'success' => true,
            'message' => 'DDT n° ' . $progressivo . ' completato con successo'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Errore nell\'aggiornamento del documento'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore del database: ' . $e->getMessage()
    ]);
}