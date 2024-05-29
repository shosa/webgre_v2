<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$del_id = filter_input(INPUT_POST, 'del_id');
$db = getDbInstance();

if ($_SESSION['admin_type'] == 'utente') {
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}

// Verifica se l'id del modello che si desidera eliminare è presente nei lanci
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Esegui una query per verificare se ci sono lanci con l'id_modello uguale a $del_id
    $db->where('id_modello', $del_id);
    $lanci = $db->get('lanci');

    // Se ci sono lanci con questo id_modello, impedisci l'eliminazione
    if ($lanci) {
        $_SESSION['failure'] = "Impossibile eliminare il modello, è presente in lanci di produzione.";
    } else {
        // Non ci sono lanci associati a questo modello, quindi puoi procedere con l'eliminazione
        $db->where('id', $del_id);
        $stat = $db->delete('basi_modelli');
        $db->where('id_modello', $del_id);
        $stat = $db->delete('var_modelli');
        if ($stat) {
            $_SESSION['info'] = "Modello eliminato correttamente!";
        }
    }

    header('location: modelli.php');
    exit;
}
?>