<?php
// cerca_riparazione.php

require_once '../../config/config.php';

$idrip = filter_input(INPUT_GET, 'idrip', FILTER_UNSAFE_RAW);

// Ottieni l'istanza del database usando PDO
$db = getDbInstance();

try {
    // Crea la query per cercare la riparazione
    $stmt = $db->prepare("SELECT * FROM riparazioni WHERE IDRIP = :idrip");
    $stmt->bindParam(':idrip', $idrip);
    $stmt->execute();

    // Ottieni il risultato della query come array associativo
    $riparazione = $stmt->fetch(PDO::FETCH_ASSOC);

    // Restituisci i risultati come JSON
    if ($riparazione) {
        echo json_encode($riparazione);
    } else {
        echo json_encode(['error' => 'Riparazione non trovata']);
    }
} catch (PDOException $e) {
    // Gestisci eventuali eccezioni
    echo json_encode(['error' => 'Errore nel recupero dei dati: ' . $e->getMessage()]);
}
?>