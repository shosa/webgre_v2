<?php
require_once '../../../config/config.php';

$logFile = BASE_PATH . '/components/error_log.txt'; // Specifica il percorso del tuo file di log

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Controlla il tipo di azione richiesta
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save') {
            // Salva le modifiche nel file
            $newContent = filter_input(INPUT_POST, 'logContent', FILTER_SANITIZE_STRING);
            file_put_contents($logFile, $newContent);
            echo json_encode(['status' => 'success', 'message' => 'Log salvato con successo.']);
        } elseif ($_POST['action'] === 'clear') {
            // Svuota il contenuto del file
            file_put_contents($logFile, '');
            echo json_encode(['status' => 'success', 'message' => 'Log svuotato con successo.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Azione non riconosciuta.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Richiesta non valida.']);
}
