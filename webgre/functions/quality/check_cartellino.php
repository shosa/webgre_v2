<?php
// Include il file di configurazione del database
require_once '../../config/config.php';

// Ottieni il valore del cartellino dalla richiesta GET
$cartellino = filter_input(INPUT_GET, 'cartellino', FILTER_UNSAFE_RAW);

// Inizializza un array di risposta JSON
$response = array();

// Verifica se il cartellino esiste nella tabella "dati"
$db = getDbInstance();
$db->where('Cartel', $cartellino);
$exists = $db->has('dati');

if ($exists) {
    // Il cartellino esiste
    $response['exists'] = true;
} else {
    // Il cartellino non esiste
    $response['exists'] = false;
}

// Restituisci la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);
?>