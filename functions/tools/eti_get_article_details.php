<?php
require_once '../../config/config.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ottieni il codice dell'articolo dalla stringa di query
$artCode = $_GET['art'];

// Preparazione della query SQL per evitare SQL injection
$stmt = $conn->prepare("SELECT cm, barcode, des FROM inv_anagrafiche WHERE art = ?");
$stmt->bind_param("s", $artCode); // 's' specifica che il parametro è una stringa

// Esecuzione della query
$stmt->execute();

// Associazione dei risultati alle variabili
$stmt->bind_result($cm, $barcode, $des);

// Fetch dei risultati
$details = [];
if ($stmt->fetch()) {
    $details = [
        'cm' => $cm,
        'barcode' => $barcode,
        'des' => $des,
    ];
}

// Imposta l'header per output JSON
header('Content-Type: application/json');

// Controlla se $details è vuoto e restituisce un errore se è così
if (empty($details)) {
    echo json_encode(['error' => 'Nessun articolo trovato con il codice specificato']);
} else {
    echo json_encode($details);
}

// Chiusura dello statement e della connessione al database
$stmt->close();
$conn->close();
?>
