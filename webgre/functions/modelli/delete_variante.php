<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$del_id = filter_input(INPUT_POST, 'variante_id');
$articolo = filter_input(INPUT_POST, 'descrizione');
$desc_variante = filter_input(INPUT_POST, 'desc_variante');
$id_articolo = filter_input(INPUT_POST, 'articolo_id');
$db = getDbInstance();

if ($_SESSION['admin_type'] != 'super') {
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}

// Verifica se ci sono record nella tabella "lanci" con id_modello e id_variante corrispondenti
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $db->where('id_modello', $id_articolo);
    $db->where('id_variante', $del_id);
    $existingLanci = $db->get('lanci');

    // Se esistono record nei lanci, mostra un errore
    if ($existingLanci) {
        $_SESSION['failure'] = "Impossibile eliminare la variante perché utilizzata nei lanci.";
    } else {
        // Non ci sono record associati nella tabella lanci, quindi procedi con l'eliminazione
        $db->where('id', $del_id);
        $stat = $db->delete('var_modelli');
        if ($stat) {
            $_SESSION['info'] = "Variante " . $del_id . " / " . $desc_variante . " eliminata correttamente dall'articolo " . $articolo;

        }
    }
    header('location: edit_modello.php?id_modello=' . $id_articolo . '&operation=plus');

}

?>