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
    // Verifica se il reparto è utilizzato in qualche cartellino
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM cq_hermes_records r
        JOIN cq_hermes_reparti d ON r.reparto = d.nome_reparto
        WHERE d.id = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Impossibile eliminare il reparto perché è utilizzato in ' . $count . ' cartellini.']);
        exit;
    }
    
    // Elimina il reparto
    $stmt = $pdo->prepare("DELETE FROM cq_hermes_reparti WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $rowCount = $stmt->rowCount();
    if ($rowCount === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Reparto non trovato']);
        exit;
    }
    
    echo json_encode(['success' => true, 'message' => 'Reparto eliminato con successo']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}