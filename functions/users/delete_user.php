<?php
// Include il file di configurazione del database
require_once '../../config/config.php';

// Verifica se è stata inviata una richiesta POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Controlla se è stato inviato l'ID dell'utente da eliminare
    if (isset($_POST['user_id'])) {
        // Connessione al database
        $pdo = getDbInstance();

        // Prepara e esegui la query per eliminare l'utente dal database
        $stmt = $pdo->prepare("DELETE FROM utenti WHERE id = :id");
        $stmt->bindParam(':id', $_POST['user_id']);

        // Esegui la query
        if ($stmt->execute()) {
            // Rispondi con un messaggio di successo
            echo json_encode(array("status" => "success"));
        } else {
            // Rispondi con un messaggio di errore
            echo json_encode(array("status" => "error", "message" => "Impossibile eliminare l'utente."));
        }
    } else {
        // Rispondi con un messaggio di errore se l'ID dell'utente non è stato fornito
        echo json_encode(array("status" => "error", "message" => "ID utente mancante."));
    }
} else {
    // Rispondi con un messaggio di errore se non è stata inviata una richiesta POST
    echo json_encode(array("status" => "error", "message" => "Metodo non consentito."));
}
?>