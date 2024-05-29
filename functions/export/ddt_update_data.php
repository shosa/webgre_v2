<?php
require_once '../../config/config.php';

$id = $_POST['id'];
$field = $_POST['field'];
$value = $_POST['value'];

$db = getDbInstance();

$data = [
    $field => $value
];

$db->where('id', $id)->update('exp_dati_articoli', $data);

echo json_encode(['success' => true]);
?>