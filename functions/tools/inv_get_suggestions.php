<?php

require_once '../../config/config.php';

try {
    // Crea la connessione
    $conn = getDbInstance();
    // Imposta l'attributo dell'errore di PDO a eccezione

    // Ottieni il termine di ricerca dalla query string
    $searchTerm = $_GET['q'];

    // Prepara e esegui la query SQL
    $sql = "SELECT DISTINCT art, des FROM inv_anagrafiche WHERE art LIKE :searchTerm OR des LIKE :searchTerm";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['searchTerm' => '%' . $searchTerm . '%']);

    // Recupera i risultati in un array
    $suggestions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'art' => $row['art'],
            'des' => $row['des'],
        ];
    }

    // Output dei suggerimenti come JSON
    header('Content-Type: application/json');
    echo json_encode($suggestions);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>