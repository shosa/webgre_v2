<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';

header('Content-Type: application/json');

$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Ottieni tutti i cartellini ordinati per data di controllo decrescente
    $stmt = $pdo->query("
        SELECT * 
        FROM cq_hermes_records 
        ORDER BY data_controllo DESC
    ");
    
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatta le date per la visualizzazione
    foreach ($records as &$record) {
        $record['data_controllo'] = date('d/m/Y H:i', strtotime($record['data_controllo']));
    }
    
    echo json_encode($records);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}