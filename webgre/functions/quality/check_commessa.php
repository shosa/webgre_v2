<?php
// Include il file di configurazione del database
require_once '../../config/config.php';

// Ottieni il valore del commessa dalla richiesta GET
$commessa = filter_input(INPUT_GET, 'commessa', FILTER_UNSAFE_RAW);

// Inizializza un array di risposta JSON
$response = array();

try {
    // Ottieni un'istanza del database
    $db = getDbInstance();
    
    // Imposta la condizione di where con il nome del campo racchiuso tra backtick
    $db->where('`Commessa Cli`', $commessa);
    
    // Esegui la query per ottenere il valore del campo 'cartel'
    $result = $db->getOne('dati', 'cartel');

    if ($result) {
        // La commessa esiste, restituisce anche il valore di 'cartel'
        $response['exists'] = true;
        $response['cartellino'] = $result['cartel'];
    } else {
        // La commessa non esiste
        $response['exists'] = false;
    }
} catch (Exception $e) {
    // Gestisci gli errori
    $response['error'] = $e->getMessage();
}

// Restituisci la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
