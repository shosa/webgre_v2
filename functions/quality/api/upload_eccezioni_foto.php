<?php
/**
 * API per caricare foto delle eccezioni
 * Endpoint: /api/upload_eccezione_foto.php
 * Metodo: POST
 * Formato richiesta: multipart/form-data
 * Formato risposta: JSON
 */

// Headers e configurazione come nelle altre API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestisci le richieste OPTIONS (preflight per CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

require_once '../../../config/config.php';

// Inizializza la risposta
$response = ['status' => 'error', 'message' => 'Errore sconosciuto'];

try {
    // Verifica che il metodo di richiesta sia POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo non consentito');
    }
    
    // Verifica che sia stato inviato un file
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Nessun file caricato o errore durante il caricamento');
    }
    
    // Verifica che sia stato specificato il cartellino
    if (!isset($_POST['cartellino']) || empty($_POST['cartellino'])) {
        throw new Exception('Cartellino non specificato');
    }
    
    $cartellino = trim($_POST['cartellino']);
    
    // Crea la directory di upload se non esiste
    $upload_dir = '../../../uploads/hermes/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Genera un nome file unico
    $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $file_name = $cartellino . '_' . uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;
    
    // Sposta il file caricato nella directory di destinazione
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $file_path)) {
        throw new Exception('Impossibile salvare il file');
    }
    
    // Restituisci il percorso del file come risposta
    $response = [
        'status' => 'success',
        'message' => 'File caricato con successo',
        'data' => [
            'file_path' => 'uploads/hermes/' . $file_name
        ]
    ];
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>