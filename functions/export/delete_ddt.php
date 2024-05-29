<?php
require_once '../../config/config.php';
$db = getDbInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recordId = filter_input(INPUT_POST, 'id', FILTER_UNSAFE_RAW);

    // Elimina record dalla tabella exp_documenti
    $db->where('id', $recordId);
    $db->delete('exp_documenti');

    // Elimina record dalla tabella exp_dati_lanci_ddt
    $db->where('id_doc', $recordId);
    $db->delete('exp_dati_lanci_ddt');

    // Elimina record dalla tabella exp_dati_articoli
    $db->where('id_documento', $recordId);
    $db->delete('exp_dati_articoli');

    // Elimina record dalla tabella exp_dati_mancanti
    $db->where('id_documento', $recordId);
    $db->delete('exp_dati_mancanti');


    echo "Record eliminato con successo!";
}
?>