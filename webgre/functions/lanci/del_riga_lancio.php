<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['riga_id'])) {
        $rigaId = $_POST['riga_id'];

        // Inizializza la connessione al database con mysqlidb
        $db = getDbInstance();

        // Esegui la query per eliminare il record dalla tabella "lanci" usando l'ID
        $db->where('ID', $rigaId);
        if ($db->delete('lanci')) {
            // Record eliminato con successo
            echo "Record eliminato con successo";
        } else {
            // Errore durante l'eliminazione del record
            echo "Errore durante l'eliminazione del record: " . $db->getLastError();
        }
    } else {
        // Parametro "riga_id" non ricevuto correttamente
        echo "Parametro 'riga_id' non ricevuto correttamente.";
    }
} else {
    // Metodo di richiesta non valido
    echo "Metodo di richiesta non valido. Richiesta POST richiesta.";
}
