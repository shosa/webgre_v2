<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID non specificato']);
    exit;
}

$id = (int)$_GET['id'];
$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Ottieni il cartellino specificato
    $stmt = $pdo->prepare("SELECT * FROM cq_hermes_records WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        http_response_code(404);
        echo json_encode(['error' => 'Cartellino non trovato']);
        exit;
    }
    
    // Formatta la data per la visualizzazione
    $record['data_controllo'] = date('d/m/Y H:i', strtotime($record['data_controllo']));
    
    echo json_encode($record);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}