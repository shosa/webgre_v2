<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID non specificato']);
    exit;
}

$id = (int)$_POST['id'];
$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Ottieni l'ID del cartellino associato prima di eliminare l'eccezione
    $stmt = $pdo->prepare("SELECT cartellino_id FROM cq_hermes_eccezioni WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $eccezione = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$eccezione) {
        http_response_code(404);
        echo json_encode(['error' => 'Eccezione non trovata']);
        exit;
    }
    
    $cartellino_id = $eccezione['cartellino_id'];
    
    // Elimina l'eccezione
    $stmt = $pdo->prepare("DELETE FROM cq_hermes_eccezioni WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Controlla se ci sono ancora eccezioni per questo cartellino
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cq_hermes_eccezioni WHERE cartellino_id = :cartellino_id");
    $stmt->bindParam(':cartellino_id', $cartellino_id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Se non ci sono più eccezioni, aggiorna il flag ha_eccezioni a 0
    if ($count == 0) {
        $stmt = $pdo->prepare("UPDATE cq_hermes_records SET ha_eccezioni = 0 WHERE id = :cartellino_id");
        $stmt->bindParam(':cartellino_id', $cartellino_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Eccezione eliminata con successo',
        'cartellino_id' => $cartellino_id
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}