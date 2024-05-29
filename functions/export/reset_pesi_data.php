<?php
require_once '../../config/config.php';

$db = getDbInstance();
$data = json_decode(file_get_contents("php://input"), true);

$progressivo = $data['progressivo'];

$db->where('id_documento', $progressivo);
$result = $db->delete('exp_piede_documenti');

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Cancellazione riuscita']);
} else {
    echo json_encode(['success' => false, 'message' => 'Cancellazione fallita']);
}
?>