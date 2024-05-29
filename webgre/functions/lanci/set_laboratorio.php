<?php
// Includi il file di configurazione del database e inizializza la sessione
require_once '../../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ricevi i dati inviati tramite AJAX
    $lancio_id = filter_input(INPUT_POST, 'lancio_id', FILTER_VALIDATE_INT);
    $laboratorio_id = filter_input(INPUT_POST, 'laboratorio_id', FILTER_VALIDATE_INT);

    if ($lancio_id && $laboratorio_id) {
        // Esegui una query SQL per aggiornare il campo id_laboratorio nel record del lancio
        $db = getDbInstance();
        $db->where('lancio', $lancio_id)->update('lanci', array('id_lab' => $laboratorio_id));

        // Puoi anche eseguire ulteriori azioni o fornire una risposta appropriata
        echo 'Laboratorio assegnato con successo!';
    } else {
        echo 'Errore: dati non validi.';
    }
} else {
    // Se non è una richiesta POST, reindirizza o gestisci l'errore di conseguenza
    echo 'Metodo non consentito.';
}