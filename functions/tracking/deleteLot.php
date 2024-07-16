<?php
session_start();
require_once '../../config/config.php';

// Check if user is authenticated
require_once BASE_PATH . '/components/auth_validate.php';

// Include necessary utilities or functions
require_once '../../utils/log_utils.php';

// Check if POST data is set
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $pdo = getDbInstance();

    // Prepare SQL statement to delete the lot record
    $sql = "DELETE FROM track_links WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo 'Lotto cancellato con successo.';
    } else {
        echo 'Errore durante la cancellazione del lotto.';
    }
} else {
    echo 'Dati non validi.';
}
?>