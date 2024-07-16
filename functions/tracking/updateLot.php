<?php
session_start();
require_once '../../config/config.php';

// Check if user is authenticated
require_once BASE_PATH . '/components/auth_validate.php';

// Include necessary utilities or functions
require_once '../../utils/log_utils.php';

// Check if POST data is set
if (isset($_POST['id']) && isset($_POST['lot'])) {
    $id = $_POST['id'];
    $newLotValue = $_POST['lot'];
    $pdo = getDbInstance();

    // Prepare SQL statement to update the lot value
    $sql = "UPDATE track_links SET lot = :lot WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':lot', $newLotValue, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo 'Lotto aggiornato con successo.';
    } else {
        echo 'Errore durante l\'aggiornamento del lotto.';
    }
} else {
    echo 'Dati non validi.';
}
?>
