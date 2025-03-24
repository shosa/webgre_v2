<?php
// Include il file di configurazione del database
require_once '../../../config/config.php';
// Ottieni il valore del cartellino dalla richiesta GET
$cartellino = filter_input(INPUT_GET, 'cartellino', FILTER_UNSAFE_RAW);
// Inizializza un array di risposta JSON
$response = array();
try {
    // Connessione al database usando PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Prepara la query per verificare se il cartellino esiste nella tabella "dati"
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM dati WHERE Cartel = :cartellino");
    $stmt->bindParam(':cartellino', $cartellino, PDO::PARAM_STR);
    $stmt->execute();
    $exists = $stmt->fetchColumn() > 0;
    if ($exists) {
        // Il cartellino esiste
        $response['exists'] = true;
    } else {
        // Il cartellino non esiste
        $response['exists'] = false;
    }
} catch (PDOException $e) {
    // Se si verifica un errore, aggiungi il messaggio di errore alla risposta
    $response['error'] = "Errore: " . $e->getMessage();
}
// Restituisci la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
