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
    // Ottieni i dati del form
    $data = [
        'nome_reparto' => $_POST['nome_reparto'],
        'attivo' => isset($_POST['attivo']) ? (int)$_POST['attivo'] : 1,
        'ordine' => isset($_POST['ordine']) ? (int)$_POST['ordine'] : 0
    ];
    
    // Determina se è un inserimento o un aggiornamento
    if (isset($_POST['id'])) {
        // Aggiornamento
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("
            UPDATE cq_hermes_reparti
            SET nome_reparto = :nome_reparto,
                attivo = :attivo,
                ordine = :ordine
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nome_reparto', $data['nome_reparto']);
        $stmt->bindParam(':attivo', $data['attivo'], PDO::PARAM_INT);
        $stmt->bindParam(':ordine', $data['ordine'], PDO::PARAM_INT);
        $stmt->execute();
        
        $response = ['success' => true, 'message' => 'Reparto aggiornato con successo', 'id' => $id];
    } else {
        // Verifica se il nome reparto esiste già
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cq_hermes_reparti WHERE nome_reparto = :nome_reparto");
        $stmt->bindParam(':nome_reparto', $data['nome_reparto']);
        $stmt->execute();
        $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if ($exists) {
            throw new Exception("Il nome del reparto esiste già. Scegliere un nome diverso.");
        }
        
        // Inserimento
        $stmt = $pdo->prepare("
            INSERT INTO cq_hermes_reparti (
                nome_reparto, attivo, ordine, data_creazione
            ) VALUES (
                :nome_reparto, :attivo, :ordine, NOW()
            )
        ");
        $stmt->bindParam(':nome_reparto', $data['nome_reparto']);
        $stmt->bindParam(':attivo', $data['attivo'], PDO::PARAM_INT);
        $stmt->bindParam(':ordine', $data['ordine'], PDO::PARAM_INT);
        $stmt->execute();
        
        $id = $pdo->lastInsertId();
        $response = ['success' => true, 'message' => 'Reparto aggiunto con successo', 'id' => $id];
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}