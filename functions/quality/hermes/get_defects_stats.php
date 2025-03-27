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
    // Ottieni la distribuzione dei tipi di difetti
    $stmt = $pdo->query("
        SELECT 
            tipo_difetto,
            COUNT(*) as count
        FROM 
            cq_hermes_eccezioni
        GROUP BY 
            tipo_difetto
        ORDER BY 
            count DESC
        LIMIT 7
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepara i dati per il grafico
    $labels = [];
    $counts = [];
    
    foreach ($results as $row) {
        $labels[] = $row['tipo_difetto'];
        $counts[] = (int)$row['count'];
    }
    
    // Invia i risultati
    echo json_encode([
        'labels' => $labels,
        'counts' => $counts
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}