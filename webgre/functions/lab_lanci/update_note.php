<?php
// Includi il file di configurazione del database o qualsiasi altra operazione necessaria
require_once '../../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ottieni il nuovo valore della nota e l'ID dall'input POST
    $newNote = $_POST['note'];
    $itemId = $_POST['id']; // Ottieni l'ID dall'input POST

    // Esegui l'aggiornamento nel database
    $db = getDbInstance(); // Sostituisci con la tua istanza del database

    // Sostituisci 'nome_tabella' con il nome della tabella in cui è memorizzata la nota
    $data = array('note' => $newNote);

    // Usa l'ID ottenuto dall'input POST per il criterio di aggiornamento
    $db->where('ID', $itemId);

    $update = $db->update('lanci', $data);

    if ($update) {
        echo 'Aggiornamento riuscito';
    } else {
        echo 'Errore nell\'aggiornamento: ' . $db->getLastError();
    }
} else {
    echo 'Metodo di richiesta non valido';
}
?>