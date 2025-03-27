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
    // Totale cartellini
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cq_hermes_records");
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Cartellini oggi
    $stmt = $pdo->query("SELECT COUNT(*) as today FROM cq_hermes_records WHERE DATE(data_controllo) = CURDATE()");
    $today_records = $stmt->fetch(PDO::FETCH_ASSOC)['today'];
    
    // Totale eccezioni
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cq_hermes_eccezioni");
    $total_exceptions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Eccezioni oggi
    $stmt = $pdo->query("
        SELECT COUNT(*) as today 
        FROM cq_hermes_eccezioni e
        JOIN cq_hermes_records r ON e.cartellino_id = r.id
        WHERE DATE(r.data_controllo) = CURDATE()
    ");
    $today_exceptions = $stmt->fetch(PDO::FETCH_ASSOC)['today'];
    
    // Invia i risultati
    echo json_encode([
        'total_records' => $total_records,
        'today_records' => $today_records,
        'total_exceptions' => $total_exceptions,
        'today_exceptions' => $today_exceptions
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}