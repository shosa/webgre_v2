<?php
require_once 'config/config.php';

function createNotification($user_id, $type, $message, $dato)
{
    try {
        $pdo = getDbInstance();

        // Prepara la query per inserire la notifica nel database
        $link = BASE_URL . '/functions/riparazioni/file_preview.php?riparazione_id=' . $dato;
        $query = "INSERT INTO notifications (user_id, type, message, timestamp, link, is_read) 
                  VALUES (:user_id, :type, :message, NOW(), :link, 0)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':link', $link);
        $stmt->execute();

        // Logga il successo
        error_log("Notifica creata con successo per l'utente $user_id");
    } catch (PDOException $e) {
        // Gestione degli errori
        error_log("Errore durante l'inserimento della notifica: " . $e->getMessage());
        die(); // Termina lo script in caso di errore critico
    }
}

function checkRepairNotifications()
{
    try {
        $pdo = getDbInstance();

        // Calcola la data di 10 giorni fa nel formato corretto per MySQL
        $ten_days_ago = date('Y-m-d', strtotime('-10 days'));

        // Query per selezionare le riparazioni scadute per l'utente corrente
        $query = "SELECT IDRIP, DATA FROM riparazioni WHERE utente = :username AND STR_TO_DATE(DATA, '%d/%m/%Y') < :ten_days_ago";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $_SESSION['username']);
        $stmt->bindParam(':ten_days_ago', $ten_days_ago);
        $stmt->execute();
        $riparazioni_scadute = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Se ci sono riparazioni scadute, creare le notifiche
        if ($riparazioni_scadute) {
            foreach ($riparazioni_scadute as $riparazione) {
                $idrip = $riparazione['IDRIP'];
                $message = "Riparazione $idrip scaduta da almeno 10 giorni.";
                createNotification($_SESSION['user_id'], 'warning', $message, $idrip);
            }

            // Logga il successo
            error_log("Notifiche create con successo per l'utente " . $_SESSION['user_id']);
        } else {
            // Logga che non ci sono riparazioni scadute
            error_log("Nessuna riparazione scaduta trovata per l'utente " . $_SESSION['username']);
        }
    } catch (PDOException $e) {
        // Gestione degli errori
        error_log("Errore durante il controllo delle riparazioni scadute: " . $e->getMessage());
        die(); // Termina lo script in caso di errore critico
    }
}

// Chiamata alla funzione per controllare le notifiche delle riparazioni scadute
checkRepairNotifications();
?>