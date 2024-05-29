<?php
// Connetti al database e verifica che tutto sia corretto
require_once '../../config/config.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lancio_id = filter_input(INPUT_POST, 'lancio_id', FILTER_VALIDATE_INT);
    $db = getDbInstance();
    $db->where('lancio', $lancio_id);
    $data = array(
        'stato' => 'LANCIATO',
        'data' => date('d/m/Y'),
        'taglio' => 0,
        'preparazione' => 0,
        'orlatura' => 0,
        'spedizione' => 0
    );
    $result = $db->update('lanci', $data);
    if ($result) {
        echo "Successo";
    } else {
        echo "Errore nell'aggiornamento dello stato del lancio: ";
    }
} else {
    echo "Metodo di richiesta non valido.";
}
