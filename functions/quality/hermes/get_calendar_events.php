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

// Default: ultimi 3 mesi
$startDate = date('Y-m-d', strtotime('-3 months'));
$endDate = date('Y-m-d', strtotime('+1 month'));

// Prendi le date dal parametro di query, se fornito
if (isset($_GET['start']) && !empty($_GET['start'])) {
    $startDate = $_GET['start'];
}
if (isset($_GET['end']) && !empty($_GET['end'])) {
    $endDate = $_GET['end'];
}

try {
    // Ottieni il conteggio dei record per ogni giorno nel periodo specificato
    $stmt = $pdo->prepare("
        SELECT 
            DATE(data_controllo) as date,
            COUNT(*) as count
        FROM 
            cq_hermes_records
        WHERE 
            data_controllo BETWEEN :startDate AND :endDate
        GROUP BY 
            DATE(data_controllo)
        ORDER BY 
            date ASC
    ");
    
    $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
    $stmt->execute();
    
    $calendarData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($calendarData);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}