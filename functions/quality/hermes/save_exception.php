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
    // Gestione del caricamento della foto
    $fotoPath = null;
    if (isset($_FILES['fotoPath']) && $_FILES['fotoPath']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = BASE_PATH . '/uploads/hermes/';
        
        // Crea la directory se non esiste
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['fotoPath']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['fotoPath']['tmp_name'], $uploadFile)) {
            $fotoPath = '/uploads/hermes/' . $fileName;
        }
    }
    
    // Ottieni i dati del form
    $data = [
        'cartellino_id' => (int)$_POST['cartellino_id'],
        'taglia' => $_POST['taglia'],
        'tipo_difetto' => $_POST['tipo_difetto'],
        'note_operatore' => isset($_POST['note_operatore']) ? $_POST['note_operatore'] : null,
        'fotoPath' => $fotoPath
    ];
    
    // Determina se è un inserimento o un aggiornamento
    if (isset($_POST['id'])) {
        // Aggiornamento
        $id = (int)$_POST['id'];
        
        // Se non è stata caricata una nuova foto, mantieni quella esistente
        if ($fotoPath === null) {
            $stmt = $pdo->prepare("
                UPDATE cq_hermes_eccezioni
                SET cartellino_id = :cartellino_id,
                    taglia = :taglia,
                    tipo_difetto = :tipo_difetto,
                    note_operatore = :note_operatore
                WHERE id = :id
            ");
        } else {
            $stmt = $pdo->prepare("
                UPDATE cq_hermes_eccezioni
                SET cartellino_id = :cartellino_id,
                    taglia = :taglia,
                    tipo_difetto = :tipo_difetto,
                    note_operatore = :note_operatore,
                    fotoPath = :fotoPath
                WHERE id = :id
            ");
            $stmt->bindParam(':fotoPath', $data['fotoPath']);
        }
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':cartellino_id', $data['cartellino_id'], PDO::PARAM_INT);
        $stmt->bindParam(':taglia', $data['taglia']);
        $stmt->bindParam(':tipo_difetto', $data['tipo_difetto']);
        $stmt->bindParam(':note_operatore', $data['note_operatore']);
        $stmt->execute();
        
        $response = ['success' => true, 'message' => 'Eccezione aggiornata con successo', 'id' => $id];
    } else {
        // Inserimento
        $stmt = $pdo->prepare("
            INSERT INTO cq_hermes_eccezioni (
                cartellino_id, taglia, tipo_difetto, note_operatore, fotoPath, data_creazione
            ) VALUES (
                :cartellino_id, :taglia, :tipo_difetto, :note_operatore, :fotoPath, NOW()
            )
        ");
        $stmt->bindParam(':cartellino_id', $data['cartellino_id'], PDO::PARAM_INT);
        $stmt->bindParam(':taglia', $data['taglia']);
        $stmt->bindParam(':tipo_difetto', $data['tipo_difetto']);
        $stmt->bindParam(':note_operatore', $data['note_operatore']);
        $stmt->bindParam(':fotoPath', $data['fotoPath']);
        $stmt->execute();
        
        $id = $pdo->lastInsertId();
        
        // Aggiorna il flag ha_eccezioni del cartellino
        $stmt = $pdo->prepare("
            UPDATE cq_hermes_records
            SET ha_eccezioni = 1
            WHERE id = :cartellino_id
        ");
        $stmt->bindParam(':cartellino_id', $data['cartellino_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        $response = ['success' => true, 'message' => 'Eccezione aggiunta con successo', 'id' => $id];
    }
    
    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}