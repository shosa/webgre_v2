<?php
/**
 * Eliminazione documento DDT
 * 
 * Questo script gestisce l'eliminazione di un documento DDT e tutti i suoi dati correlati.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';


// Verifica che la richiesta sia di tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

// Recupera e valida il parametro ID
$recordId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$recordId) {
    echo json_encode(['success' => false, 'message' => 'ID non valido']);
    exit();
}

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Inizia una transazione per garantire l'integrità dei dati
    $conn->beginTransaction();
    
    // Elimina record dalla tabella exp_dati_mancanti
    $stmt = $conn->prepare("DELETE FROM exp_dati_mancanti WHERE id_documento = :id");
    $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Elimina record dalla tabella exp_dati_articoli
    $stmt = $conn->prepare("DELETE FROM exp_dati_articoli WHERE id_documento = :id");
    $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Elimina record dalla tabella exp_dati_lanci_ddt
    $stmt = $conn->prepare("DELETE FROM exp_dati_lanci_ddt WHERE id_doc = :id");
    $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Elimina record dalla tabella exp_documenti
    $stmt = $conn->prepare("DELETE FROM exp_documenti WHERE id = :id");
    $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Commit della transazione
    $conn->commit();
    

    echo json_encode(['success' => true, 'message' => 'Record eliminato con successo!']);
    
} catch (PDOException $e) {
    // In caso di errore, annulla tutte le modifiche
    if ($conn) {
        $conn->rollBack();
    }
    
    // Log dell'errore
    error_log("Errore nell'eliminazione del documento {$recordId}: " . $e->getMessage());
    
    // Restituisci una risposta di errore
    echo json_encode([
        'success' => false, 
        'message' => 'Errore durante l\'eliminazione del documento: ' . $e->getMessage()
    ]);
}
?>