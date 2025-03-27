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
    // Ottieni tutti i reparti ordinati per ordine
    $stmt = $pdo->query("
        SELECT * 
        FROM cq_hermes_reparti
        ORDER BY ordine, nome_reparto
    ");
    
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatta le date per la visualizzazione
    foreach ($departments as &$department) {
        $department['data_creazione'] = date('d/m/Y H:i', strtotime($department['data_creazione']));
    }
    
    echo json_encode($departments);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}