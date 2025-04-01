<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';

header('Content-Type: application/json');

// Controllo che l'ID cartellino sia stato inviato
if (!isset($_GET['record_id']) || empty($_GET['record_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID cartellino non specificato']);
    exit;
}

$recordId = (int) $_GET['record_id'];
$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Ottieni tutte le eccezioni per il cartellino specificato
    $stmt = $pdo->prepare("
        SELECT * 
        FROM cq_hermes_eccezioni 
        WHERE cartellino_id = :recordId
        ORDER BY data_creazione DESC
    ");

    $stmt->bindParam(':recordId', $recordId, PDO::PARAM_INT);
    $stmt->execute();

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