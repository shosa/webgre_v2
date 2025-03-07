<?php
// Ricevi i dati JSON dall'errore JavaScript
$data = file_get_contents('php://input');
$error = json_decode($data, true);

// Crea un messaggio di errore con la pagina e l'orario
$logMessage = "[{$error['time']}] [Page: {$error['page']}] JS Error: {$error['message']} in {$error['source']} on line {$error['lineno']}\nStack trace: {$error['error']}\n";

// Logga l'errore in un file
error_log($logMessage, 3, __DIR__ . '/error_log.txt');
?>