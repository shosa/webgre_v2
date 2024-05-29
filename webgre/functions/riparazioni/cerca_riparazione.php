<?php
// cerca_riparazione.php

require_once '../../config/config.php';

$idrip = filter_input(INPUT_GET, 'idrip', FILTER_UNSAFE_RAW);

$db = getDbInstance();


// Esegui la query per cercare la riparazione
$db->where('IDRIP', $idrip);
$riparazione = $db->getOne('riparazioni');

// Restituisci i risultati come JSON
if ($riparazione) {
    echo json_encode($riparazione);
} else {
    echo json_encode(['error' => 'Riparazione non trovata']);
}
?>