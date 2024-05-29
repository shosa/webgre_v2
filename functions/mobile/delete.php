
<?php
require_once '../../config/config.php';
include BASE_PATH . '/includes/mobile_header.php';
// Ottieni l'ID della riparazione da eliminare
$recordId = filter_input(INPUT_POST, 'idrip', FILTER_SANITIZE_NUMBER_INT);

// Verifica se l'ID del record è valido
if ($recordId) {
    $db = getDbInstance();

    // Elimina la riparazione dal database
    $db->where('IDRIP', $recordId);
    $delete = $db->delete('riparazioni');

    if ($delete) {
        // Riparazione eliminata con successo
        echo '<div style="text-align: center; margin-top: 50px;">';
        echo '<i class="fad fa-check-circle fa-5x" style="color: #28a745;"></i>';
        echo '<h2 style="color: #28a745;">Riparazione Eliminata</h2>';
        echo '</div>';
    } else {
        // Errore durante l'eliminazione
        echo '<div style="text-align: center; margin-top: 50px;">';
        echo '<i class="fad fa-exclamation-circle fa-5x" style="color: #dc3545;"></i>';
        echo '<h2 style="color: #dc3545;">Errore durante l\'eliminazione</h2>';
        echo '</div>';
    }
} else {
    // ID non valido
    echo '<div style="text-align: center; margin-top: 50px;">';
    echo '<i class="fad fa-exclamation-triangle fa-5x" style="color: #ffc107;"></i>';
    echo '<h2 style="color: #ffc107;">ID non valido</h2>';
    echo '</div>';
}
?>