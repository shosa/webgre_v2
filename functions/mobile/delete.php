<?php
require_once '../../config/config.php';
require_once '../../utils/log_utils.php';
include BASE_PATH . '/components/header.php';

// Ottieni l'ID della riparazione da eliminare
$recordId = filter_input(INPUT_POST, 'idrip', FILTER_SANITIZE_NUMBER_INT);
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$browser_info = get_browser($user_agent, true);

$browser_name = $browser_info['browser'];
$platform = $browser_info['platform'];
// Verifica se l'ID del record è valido
if ($recordId) {
    try {
        // Connessione al database utilizzando PDO
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query per eliminare la riparazione
        $sql = "DELETE FROM riparazioni WHERE IDRIP = :idrip";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idrip', $recordId, PDO::PARAM_INT);
        $stmt->execute();
        logActivity('9999', 'MOBILE', 'ELIMINA', 'Rimossa Cedola ', '#' . $recordId, $browser_name . ' / ' . $platform);
        // Verifica se la riparazione è stata eliminata con successo
        if ($stmt->rowCount() > 0) {
            // Riparazione eliminata con successo
            echo '<div style="text-align: center; margin-top: 50px;">';
            echo '<i class="fad fa-check-circle fa-5x" style="color: #28a745;"></i>';
            echo '<h2 style="color: #28a745;">Riparazione Eliminata</h2>';
            echo '</div>';
        } else {
            // Nessuna riga eliminata (ID non trovato)
            echo '<div style="text-align: center; margin-top: 50px;">';
            echo '<i class="fad fa-exclamation-circle fa-5x" style="color: #dc3545;"></i>';
            echo '<h2 style="color: #dc3545;">Errore durante l\'eliminazione</h2>';
            echo '</div>';
        }
    } catch (PDOException $e) {
        // Errore durante la connessione al database o l'esecuzione della query
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