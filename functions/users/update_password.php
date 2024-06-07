<?php
session_start();
require_once '../../config/config.php';

// Verifica se è stata inviata una richiesta POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Controlla se tutti i campi richiesti sono stati inviati
    if (isset($_POST['id']) && isset($_POST['newPassword'])) {
        // Connessione al database
        $pdo = getDbInstance();

        // Prepara e esegui la query per aggiornare la password dell'utente
        $stmt = $pdo->prepare("UPDATE utenti SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $_POST['newPassword']);
        $stmt->bindParam(':id', $_POST['id']);

        // Esegui la query
        if ($stmt->execute()) {
            // Rispondi con un messaggio di successo
            $_SESSION['warning'] = 'Password aggiornata.';
            echo json_encode(array("status" => "success"));
      
        } else {
            // Rispondi con un messaggio di errore
            $_SESSION['danger'] = 'Errore.';
            echo json_encode(array("status" => "error", "message" => "Impossibile aggiornare la password."));
          
        }
    } else {
        // Rispondi con un messaggio di errore se i parametri non sono stati forniti
        echo json_encode(array("status" => "error", "message" => "Parametri mancanti."));
    }
} else {
    // Rispondi con un messaggio di errore se non è stata inviata una richiesta POST
    echo json_encode(array("status" => "error", "message" => "Metodo non consentito."));
}
?>