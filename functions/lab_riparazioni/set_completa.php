<?php
session_start();
require_once '../../config/config.php';



// Controlla se l'ID della riparazione è stato fornito
if (isset($_GET['riparazione_id'])) {
    $riparazione_id = $_GET['riparazione_id'];

    // Effettua una query per impostare la colonna COMPLETATA su 1 per la riparazione specificata
    $db = getDbInstance();
    $data = array('COMPLETA' => 1);
    $db->where('IDRIP', $riparazione_id);
    $update = $db->update('riparazioni', $data);

    if ($update) {
        // Aggiornamento riuscito
        $_SESSION['success'] = "La riparazione è stata contrassegnata come completata.";
    } else {
        // Aggiornamento fallito
        $_SESSION['failure'] = "Errore nell'aggiornamento della riparazione.";
    }

    // Reindirizza l'utente alla pagina delle riparazioni
    header('Location: lab_riparazioni.php');
    exit;
} else {
    $_SESSION['failure'] = "ID della riparazione non fornito.";
    // Reindirizza l'utente alla pagina delle riparazioni
    header('Location: lab_riparazioni.php');
    exit;
}
?>