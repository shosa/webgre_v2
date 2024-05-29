<?php
require_once '../../config/config.php'; // Assicurati che il percorso del tuo file di configurazione sia corretto

// Ottieni l'ID del record dal parametro GET
$recordId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Verifica se l'ID del record è valido
if ($recordId) {
    // Esegui una query per ottenere i dettagli del record dal tuo database
    $db = getDbInstance(); // Sostituisci con il tuo metodo per ottenere l'istanza del database
    $db->where('IDRIP', $recordId);
    $record = $db->getOne('riparazioni');

    if ($record) {
        // Formatta i dettagli del record come desideri (ad esempio, in formato HTML)
        $details = '<table class="table table-bordered">';
        $details .= '<tr><th>ID:</th><td>' . $record['IDRIP'] . '</td></tr>';
        $details .= '<tr><th>LINEA:</th><td>' . $record['LINEA'] . '</td></tr>';
        $details .= '<tr><th>CODICE:</th><td>' . $record['CODICE'] . '</td></tr>';
        $details .= '<tr><th>ARTICOLO:</th><td>' . $record['ARTICOLO'] . '</td></tr>';
        $details .= '<tr><th>QTA:</th><td>' . $record['QTA'] . '</td></tr>';
        $details .= '<tr><th>REPARTO:</th><td>' . $record['REPARTO'] . '</td></tr>';
        $details .= '<tr><th>NOTE:</th><td>' . $record['CAUSALE'] . '</td></tr>';
        $details .= '<tr><th>LABORATORIO:</th><td>' . $record['LABORATORIO'] . '</td></tr>';
        $details .= '<tr><th>UTENTE:</th><td>' . $record['UTENTE'] . '</td></tr>';
        $details .= '<tr><th>DATA:</th><td>' . $record['DATA'] . '</td></tr>';
        $details .= '</table>';
        $details .= '<a href="file_preview.php?riparazione_id=' . $recordId . '"
        style="font-size:20pt; padding:10px; background:orange; border:solid 1pt orange; width: 100%; " class="btn btn-primary"><i
            class="far fa-print fa-lg"></i></a>';
        echo $details; // Restituisci i dettagli al chiamante
    } else {
        echo 'Record non trovato.';
    }
} else {
    echo 'ID del record non valido.';
}