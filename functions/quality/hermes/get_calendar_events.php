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

// Default: mese corrente
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Assicurati che il mese sia formattato correttamente (1-12)
if ($currentMonth < 1) $currentMonth = 1;
if ($currentMonth > 12) $currentMonth = 12;

// Calcola il primo e l'ultimo giorno del mese
$startDate = sprintf('%04d-%02d-01', $currentYear, $currentMonth);
$lastDay = date('t', strtotime($startDate)); // ottiene l'ultimo giorno del mese
$endDate = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $lastDay);

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
    
    // Esegui con date per l'inizio e fine del mese corrente
    $dateStart = $startDate . ' 00:00:00';
    $dateEnd = $endDate . ' 23:59:59';
    
    $stmt->bindParam(':startDate', $dateStart, PDO::PARAM_STR);
    $stmt->bindParam(':endDate', $dateEnd, PDO::PARAM_STR);
    $stmt->execute();
    
    $calendarData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($calendarData);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}