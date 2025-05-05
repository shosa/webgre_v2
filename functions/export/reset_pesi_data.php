<?php
require_once '../../config/config.php';

$data = json_decode(file_get_contents("php://input"), true);
$progressivo = $data['progressivo'];

// Recupera l'istanza del database (PDO)
$db = getDbInstance();

try {
    $stmt = $db->prepare("DELETE FROM exp_piede_documenti WHERE id_documento = :id_documento");
    $stmt->execute([':id_documento' => $progressivo]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Cancellazione riuscita']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nessun record trovato per la cancellazione']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Cancellazione fallita: ' . htmlspecialchars($e->getMessage())]);
}
