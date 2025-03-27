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
    // Ottieni tutte le eccezioni con i dettagli del cartellino associato
    $stmt = $pdo->query("
        SELECT e.*, r.numero_cartellino 
        FROM cq_hermes_eccezioni e
        JOIN cq_hermes_records r ON e.cartellino_id = r.id
        ORDER BY e.data_creazione DESC
    ");
    
    $exceptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatta le date per la visualizzazione
    foreach ($exceptions as &$exception) {
        $exception['data_creazione'] = date('d/m/Y H:i', strtotime($exception['data_creazione']));
    }
    
    echo json_encode($exceptions);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}