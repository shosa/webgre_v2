<?php
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && isset($_POST['changePassword'])) {
        $id = $_POST['id'];
        $changePassword = $_POST['changePassword'];

        // Connessione al database
        $pdo = getDbInstance();

        // Hash della password
        $hashedPassword = password_hash($changePassword, PASSWORD_DEFAULT);

        // Preparazione e esecuzione della query
        $stmt = $pdo->prepare("UPDATE utenti SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $id]);

        if ($stmt->rowCount()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Nessun cambiamento effettuato']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Dati mancanti']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Richiesta non valida']);
}
?>