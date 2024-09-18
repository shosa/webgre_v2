<?php
// Includi il file di connessione al database o qualsiasi altra dipendenza necessaria
require_once '../../config/config.php';
// Funzione per inviare una notifica
function sendNotification($user_id, $type, $message, $link = '')
{
    try {
        // Connessione al database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Prepara la query SQL per inserire la notifica nel database
        $query = "INSERT INTO notifications (user_id, type, message, link, is_read) VALUES (:user_id, :type, :message, :link, 0)";
        $stmt = $pdo->prepare($query);
        // Esegui la query con i parametri forniti
        $stmt->execute([
            ':user_id' => $user_id,
            ':type' => $type,
            ':message' => $message,
            ':link' => $link
        ]);
        // Ottieni l'ID della notifica appena inserita
        $notificationId = $pdo->lastInsertId();
        // Ritorna l'ID della notifica per eventuali operazioni successive
        return $notificationId;
    } catch (PDOException $e) {
        // Gestisci gli errori in caso di problemi con la connessione al database o l'esecuzione della query
        echo "Errore nell'inserimento della notifica: " . $e->getMessage();
        return false;
    }
}
