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
    // Ottieni i dati degli ultimi 7 giorni
    $stmt = $pdo->query("
        SELECT 
            DATE(r.data_controllo) as date,
            COUNT(DISTINCT r.id) as count_records,
            COUNT(e.id) as count_exceptions
        FROM 
            cq_hermes_records r
            LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
        WHERE 
            r.data_controllo >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY 
            DATE(r.data_controllo)
        ORDER BY 
            DATE(r.data_controllo)
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepara i dati per il grafico
    $dates = [];
    $countRecords = [];
    $countExceptions = [];
    
    // Inizializza gli array con zeri per tutti i 7 giorni
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('d/m', strtotime($date));
        $countRecords[$date] = 0;
        $countExceptions[$date] = 0;
    }
    
    // Popola gli array con i dati effettivi
    foreach ($results as $row) {
        $countRecords[$row['date']] = (int)$row['count_records'];
        $countExceptions[$row['date']] = (int)$row['count_exceptions'];
    }
    
    // Riorganizza gli array mantenendo l'ordine delle date
    $orderedCountRecords = [];
    $orderedCountExceptions = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $orderedCountRecords[] = $countRecords[$date];
        $orderedCountExceptions[] = $countExceptions[$date];
    }
    
    // Invia i risultati
    echo json_encode([
        'labels' => $dates,
        'countRecords' => $orderedCountRecords,
        'countExceptions' => $orderedCountExceptions
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}