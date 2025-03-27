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
        'descrizione' => $_POST['descrizione'],
        'categoria' => isset($_POST['categoria']) ? $_POST['categoria'] : null,
        'attivo' => isset($_POST['attivo']) ? (int)$_POST['attivo'] : 1,
        'ordine' => isset($_POST['ordine']) ? (int)$_POST['ordine'] : 0
    ];
    
    // Determina se è un inserimento o un aggiornamento
    if (isset($_POST['id'])) {
        // Aggiornamento
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("
            UPDATE cq_hermes_tipi_difetti
            SET descrizione = :descrizione,
                categoria = :categoria,
                attivo = :attivo,
                ordine = :ordine
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':descrizione', $data['descrizione']);
        $stmt->bindParam(':categoria', $data['categoria']);
        $stmt->bindParam(':attivo', $data['attivo'], PDO::PARAM_INT);
        $stmt->bindParam(':ordine', $data['ordine'], PDO::PARAM_INT);
        $stmt->execute();
        
        $response = ['success' => true, 'message' => 'Tipo difetto aggiornato con successo', 'id' => $id];
    } else {
        // Verifica se la descrizione esiste già
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cq_hermes_tipi_difetti WHERE descrizione = :descrizione");
        $stmt->bindParam(':descrizione', $data['descrizione']);
        $stmt->execute();
        $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if ($exists) {
            throw new Exception("La descrizione del tipo difetto esiste già. Scegliere una descrizione diversa.");
        }
        
        // Inserimento
        $stmt = $pdo->prepare("
            INSERT INTO cq_hermes_tipi_difetti (
                descrizione, categoria, attivo, ordine, data_creazione
            ) VALUES (
                :descrizione, :categoria, :attivo, :ordine, NOW()
            )
        ");
        $stmt->bindParam(':descrizione', $data['descrizione']);
        $stmt->bindParam(':categoria', $data['categoria']);
        $stmt->bindParam(':attivo', $data['attivo'], PDO::PARAM_INT);
        $stmt->bindParam(':ordine', $data['ordine'], PDO::PARAM_INT);
        $stmt->execute();
        
        $id = $pdo->lastInsertId();
        $response = ['success' => true, 'message' => 'Tipo difetto aggiunto con successo', 'id' => $id];
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}