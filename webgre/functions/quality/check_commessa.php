<?php
// Include il file di configurazione del database
require_once '../../config/config.php';
// Ottieni il valore del commessa dalla richiesta GET
$commessa = filter_input(INPUT_GET, 'commessa', FILTER_UNSAFE_RAW);
// Inizializza un array di risposta JSON
$response = array();
try {
    // Connessione al database usando PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepara la query con il nome del campo racchiuso tra backtick
    $stmt = $pdo->prepare("SELECT cartel FROM dati WHERE `Commessa Cli` = :commessa");
    $stmt->bindParam(':commessa', $commessa, PDO::PARAM_STR);
    
    // Esegui la query
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        // La commessa esiste, restituisce anche il valore di 'cartel'
        $response['exists'] = true;
        $response['cartellino'] = $result['cartel'];
    } else {
        // La commessa non esiste
        $response['exists'] = false;
    }
} catch (Exception $e) {
    // Gestisci gli errori
    $response['error'] = $e->getMessage();
}
// Restituisci la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
