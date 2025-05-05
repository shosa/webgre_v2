<?php
/**
 * Recupero dati documento
 * 
 * Questo script gestisce la richiesta AJAX per ottenere i dati del piede documento.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';

// Verifica che la richiesta sia di tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

// Recupera e valida il progressivo
$progressivo = filter_input(INPUT_POST, 'progressivo', FILTER_VALIDATE_INT);
if (!$progressivo) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ID documento non valido']);
    exit();
}

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepara e esegui la query
    $stmt = $conn->prepare("SELECT * FROM exp_piede_documenti WHERE id_documento = :id_documento");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    
    // Verifica se ci sono risultati
    if ($stmt->rowCount() > 0) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true, 
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Nessun dato trovato per questo ID documento'
        ]);
    }
} catch (PDOException $e) {
    // Log dell'errore (preferibilmente in un file di log)
    error_log("Errore nel recupero dati documento: " . $e->getMessage());
    
    // Restituisci risposta di errore
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false, 
        'message' => 'Errore durante il recupero dei dati'
    ]);
}
?>