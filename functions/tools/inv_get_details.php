<?php

// Sostituisci questi valori con le tue effettive credenziali di database
require_once '../../config/config.php';

try {
    // Crea la connessione PDO
    $conn = getDbInstance();
    // Imposta l'attributo dell'errore di PDO a eccezione


    // Ottieni il codice articolo dalla query string
    $articleCode = $_GET['art'];

    // Prepara e esegui la query SQL per ottenere i dettagli basati sul codice articolo
    $sql = "SELECT art, des FROM inv_anagrafiche WHERE art = :articleCode";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['articleCode' => $articleCode]);

    // Controlla se la query ha avuto successo e recupera il risultato come array associativo
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    // Output dei dettagli come JSON
    header('Content-Type: application/json');
    echo json_encode($details);
} catch (PDOException $e) {
    // Gestisci l'errore
    echo "Errore: " . $e->getMessage();
}
?>