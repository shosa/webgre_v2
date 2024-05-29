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

// Verifica se l'id da eliminare è stato inviato tramite POST
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Esegui una query per ottenere lo stato del lancio
    $db->where('lancio', $del_id);
    $lancio = $db->getOne('lanci');

    // Verifica se il lancio esiste e se il suo stato è uguale a "IN ATTESA"
    if ($lancio && $lancio['stato'] === 'IN ATTESA') {
        // Puoi procedere con l'eliminazione del lancio
        $db->where('lancio', $del_id);
        $stat = $db->delete('lanci');
        if ($stat) {
            $_SESSION['info'] = "Lancio eliminato correttamente!";
        }
    } else {
        // Il lancio non può essere eliminato se lo stato non è "IN ATTESA"
        $_SESSION['failure'] = "Impossibile eliminare un lancio che non ha stato <b>IN ATTESA</b>";
    }

    header('location: lanci.php');
    exit;
}
?>