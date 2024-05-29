<?php
// Includi il file di configurazione del database
require_once '../../config/config.php';

// Controlla se l'ID della query è stato inviato correttamente
if (isset($_POST['query_id'])) {
    // Ottieni l'ID della query dalla richiesta AJAX
    $queryId = $_POST['query_id'];

    // Connessione al database utilizzando PDO
    $conn = getDbInstance();

    // Prepara la query per ottenere il testo della query associata all'ID fornito
    $stmt = $conn->prepare("SELECT text_query FROM activity_log WHERE id = :query_id");
    $stmt->bindParam(':query_id', $queryId);
    $stmt->execute();

    // Ottieni il testo della query
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Restituisci il testo della query come risposta alla richiesta AJAX
    if ($result) {
        echo $result['text_query'];
    } else {
        echo "Query non trovata.";
    }
} else {
    echo "ID della query non fornito.";
}
?>