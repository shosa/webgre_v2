<?php
require_once '../../config/config.php';

// Ottieni il codice dell'articolo dalla stringa di query
$artCode = $_GET['art'];

try {
    // Creazione della connessione utilizzando PDO
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparazione della query SQL per evitare SQL injection
    $stmt = $pdo->prepare("SELECT cm, barcode, des FROM inv_anagrafiche WHERE art = :artCode");
    $stmt->bindParam(':artCode', $artCode, PDO::PARAM_STR);

    // Esecuzione della query
    $stmt->execute();

    // Fetch dei risultati
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    // Imposta l'header per output JSON
    header('Content-Type: application/json');

    // Controlla se $details è vuoto e restituisce un errore se è così
    if (!$details) {
        echo json_encode(['error' => 'Nessun articolo trovato con il codice specificato']);
    } else {
        echo json_encode($details);
    }
} catch (PDOException $e) {
    // Gestione degli errori
    echo json_encode(['error' => 'Errore: ' . $e->getMessage()]);
}
?>
