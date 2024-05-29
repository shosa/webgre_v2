<?php
require_once '../../config/config.php';
$db = getDbInstance();
$progressivo = $_POST['progressivo'];

$data = $db->where('id_documento', $progressivo)->getOne('exp_piede_documenti');

if ($data) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false]);
}