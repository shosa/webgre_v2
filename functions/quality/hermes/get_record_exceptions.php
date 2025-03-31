<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/config.php';

require_once BASE_PATH . '/utils/helpers.php';

header('Content-Type: application/json');

$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Verifica se è stato passato l'ID del record
    if (!isset($_GET['record_id']) || empty($_GET['record_id'])) {
        throw new Exception('ID del record non specificato');
    }

    $record_id = intval($_GET['record_id']);

    // Ottieni le eccezioni per uno specifico cartellino
    $stmt = $pdo->prepare("
        SELECT e.*, r.numero_cartellino 
        FROM cq_hermes_eccezioni e
        JOIN cq_hermes_records r ON e.cartellino_id = r.id
        WHERE e.cartellino_id = :record_id
        ORDER BY e.data_creazione DESC
    ");
    
    $stmt->execute(['record_id' => $record_id]);
    $exceptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatta le date per la visualizzazione
    foreach ($exceptions as &$exception) {
        $exception['data_creazione'] = date('d/m/Y H:i', strtotime($exception['data_creazione']));
        
        // Gestisci il path della foto se presente
        if (!empty($exception['fotoPath'])) {
            // Assicurati che il path sia completo o relativo correttamente
            $exception['fotoPath'] = ltrim($exception['fotoPath'], '/');
        }
    }
    
    echo json_encode($exceptions);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore database: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}