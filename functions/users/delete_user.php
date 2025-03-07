<?php
require_once '../../config/config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $pdo = getDbInstance();
    $stmt = $pdo->prepare("DELETE FROM activity_log WHERE user_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $stmt2 = $pdo->prepare("DELETE FROM utenti WHERE id = :id");
        $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt2->execute()) {
            echo "Utente eliminato con successo";
        } else {
            echo "Errore durante l'eliminazione dell'utente";
        }
    } else {
        echo "Errore durante l'eliminazione dell'utente";
    }

}
?>