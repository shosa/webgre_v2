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
        'numero_cartellino' => $_POST['numero_cartellino'],
        'reparto' => $_POST['reparto'],
        'operatore' => $_POST['operatore'],
        'tipo_cq' => $_POST['tipo_cq'],
        'paia_totali' => (int)$_POST['paia_totali'],
        'cod_articolo' => $_POST['cod_articolo'],
        'articolo' => $_POST['articolo'],
        'linea' => $_POST['linea'],
        'note' => isset($_POST['note']) ? $_POST['note'] : null,
        'ha_eccezioni' => 0 // Default a 0, sarà aggiornato quando si aggiungono eccezioni
    ];
    
    // Determina se è un inserimento o un aggiornamento
    if (isset($_POST['id'])) {
        // Aggiornamento
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("
            UPDATE cq_hermes_records
            SET numero_cartellino = :numero_cartellino,
                reparto = :reparto,
                operatore = :operatore,
                tipo_cq = :tipo_cq,
                paia_totali = :paia_totali,
                cod_articolo = :cod_articolo,
                articolo = :articolo,
                linea = :linea,
                note = :note
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':numero_cartellino', $data['numero_cartellino']);
        $stmt->bindParam(':reparto', $data['reparto']);
        $stmt->bindParam(':operatore', $data['operatore']);
        $stmt->bindParam(':tipo_cq', $data['tipo_cq']);
        $stmt->bindParam(':paia_totali', $data['paia_totali'], PDO::PARAM_INT);
        $stmt->bindParam(':cod_articolo', $data['cod_articolo']);
        $stmt->bindParam(':articolo', $data['articolo']);
        $stmt->bindParam(':linea', $data['linea']);
        $stmt->bindParam(':note', $data['note']);
        $stmt->execute();
        
        $response = ['success' => true, 'message' => 'Cartellino aggiornato con successo', 'id' => $id];
    } else {
        // Inserimento
        $stmt = $pdo->prepare("
            INSERT INTO cq_hermes_records (
                numero_cartellino, reparto, data_controllo, operatore, tipo_cq, 
                paia_totali, cod_articolo, articolo, linea, note, ha_eccezioni
            ) VALUES (
                :numero_cartellino, :reparto, NOW(), :operatore, :tipo_cq, 
                :paia_totali, :cod_articolo, :articolo, :linea, :note, :ha_eccezioni
            )
        ");
        $stmt->bindParam(':numero_cartellino', $data['numero_cartellino']);
        $stmt->bindParam(':reparto', $data['reparto']);
        $stmt->bindParam(':operatore', $data['operatore']);
        $stmt->bindParam(':tipo_cq', $data['tipo_cq']);
        $stmt->bindParam(':paia_totali', $data['paia_totali'], PDO::PARAM_INT);
        $stmt->bindParam(':cod_articolo', $data['cod_articolo']);
        $stmt->bindParam(':articolo', $data['articolo']);
        $stmt->bindParam(':linea', $data['linea']);
        $stmt->bindParam(':note', $data['note']);
        $stmt->bindParam(':ha_eccezioni', $data['ha_eccezioni'], PDO::PARAM_INT);
        $stmt->execute();
        
        $id = $pdo->lastInsertId();
        $response = ['success' => true, 'message' => 'Cartellino aggiunto con successo', 'id' => $id];
    }
    
    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}