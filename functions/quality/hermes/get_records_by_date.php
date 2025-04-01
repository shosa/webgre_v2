<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/config.php';

require_once BASE_PATH . '/utils/helpers.php';

header('Content-Type: application/json');

// Controllo che la data sia stata inviata
if (!isset($_GET['date']) || empty($_GET['date'])) {
    // Se non c'è una data, restituisce i record di oggi
    $date = date('Y-m-d');
} else {
    $date = $_GET['date'];
}

// Assicurati che la data sia in formato corretto (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato data non valido. Utilizzare YYYY-MM-DD']);
    exit;
}

$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Ottieni tutti i cartellini per la data selezionata
    // Nota: la data di controllo è in formato DATETIME, quindi dobbiamo cercare record 
    // tra l'inizio e la fine della data fornita
    $dateStart = $date . ' 00:00:00';
    $dateEnd = $date . ' 23:59:59';
    
    $stmt = $pdo->prepare("
        SELECT * 
        FROM cq_hermes_records 
        WHERE data_controllo BETWEEN :dateStart AND :dateEnd
        ORDER BY data_controllo DESC
    ");
    
    $stmt->bindParam(':dateStart', $dateStart, PDO::PARAM_STR);
    $stmt->bindParam(':dateEnd', $dateEnd, PDO::PARAM_STR);
    $stmt->execute();
    
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