<?php
/**
 * API per verificare l'esistenza di un cartellino
 * Endpoint: /api/check_cartellino.php
 * Metodo: POST (più sicuro di GET per dati sensibili)
 * Formato richiesta: JSON o form-data
 * Formato risposta: JSON
 */

// Abilita CORS per consentire richieste da app Android
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestisci le richieste OPTIONS (preflight per CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

// Verifica che il metodo di richiesta sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Metodo non consentito']);
    exit;
}

// Include il file di configurazione del database
require_once '../../../config/config.php';

// Inizializza l'array di risposta
$response = ['status' => 'error', 'message' => 'Errore sconosciuto'];

try {
    // Ottieni il payload JSON dalla richiesta
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Se non è JSON, prova a leggere dal POST standard
    if (json_last_error() !== JSON_ERROR_NONE) {
        $cartellino = filter_input(INPUT_POST, 'cartellino', FILTER_SANITIZE_STRING);
    } else {
        $cartellino = isset($data['cartellino']) ? trim($data['cartellino']) : null;
    }

    // Verifica che il cartellino sia stato fornito
    if (empty($cartellino)) {
        $response = [
            'status' => 'error',
            'message' => 'Parametro cartellino mancante'
        ];
    } else {
        // Connessione al database usando PDO

        $pdo = getDbInstance();
        // Prepara la query per verificare se il cartellino esiste
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM dati WHERE Cartel = :cartellino");
        $stmt->bindParam(':cartellino', $cartellino, PDO::PARAM_STR);
        $stmt->execute();

        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            // Se esiste, recupera anche alcuni dati di base
            $stmt = $pdo->prepare("SELECT id, Cartel, Cliente FROM dati WHERE Cartel = :cartellino LIMIT 1");
            $stmt->bindParam(':cartellino', $cartellino, PDO::PARAM_STR);
            $stmt->execute();
            $cartellino_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $response = [
                'status' => 'success',
                'exists' => true,
                'message' => 'Cartellino trovato',
                'data' => $cartellino_data
            ];
        } else {
            $response = [
                'status' => 'success',
                'exists' => false,
                'message' => 'Cartellino non trovato'
            ];
        }
    }
} catch (PDOException $e) {
    // Log dell'errore (su server)
    error_log("Errore API check_cartellino: " . $e->getMessage());

    // Risposta client (senza dettagli sensibili)
    $response =  $e->getMessage();
} catch (Exception $e) {
    // Log dell'errore (su server)
    error_log("Errore generico API check_cartellino: " . $e->getMessage());

    // Risposta client
    $response = [
        'status' => 'error',
        'message' => 'Si è verificato un errore'
    ];
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>