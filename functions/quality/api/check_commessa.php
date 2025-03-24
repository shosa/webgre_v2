<?php
/**
 * API per verificare l'esistenza di una commessa
 * Endpoint: /api/check_commessa.php
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
        $commessa = filter_input(INPUT_POST, 'commessa', FILTER_SANITIZE_STRING);
    } else {
        $commessa = isset($data['commessa']) ? trim($data['commessa']) : null;
    }

    // Verifica che la commessa sia stata fornita
    if (empty($commessa)) {
        $response = [
            'status' => 'error',
            'message' => 'Parametro commessa mancante'
        ];
    } else {
        // Connessione al database usando PDO
        $pdo = getDbInstance();

        // Prepara la query per verificare se la commessa esiste
        $stmt = $pdo->prepare("SELECT cartel FROM dati WHERE `Commessa Cli` = :commessa LIMIT 1");
        $stmt->bindParam(':commessa', $commessa, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response = [
                'status' => 'success',
                'exists' => true,
                'message' => 'Commessa trovata',
                'data' => [
                    'cartellino' => $result['cartel'],
                    'id' => $result['id'],
                    'cliente' => $result['Cliente']
                ]
            ];
        } else {
            $response = [
                'status' => 'success',
                'exists' => false,
                'message' => 'Commessa non trovata'
            ];
        }
    }
} catch (PDOException $e) {
    // Log dell'errore (su server)
    error_log("Errore API check_commessa: " . $e->getMessage());

    // Risposta client (senza dettagli sensibili)
    $response = [
        'status' => 'error',
        'message' => 'Errore di connessione al database'
    ];
} catch (Exception $e) {
    // Log dell'errore (su server)
    error_log("Errore generico API check_commessa: " . $e->getMessage());

    // Risposta client
    $response = [
        'status' => 'error',
        'message' => 'Si è verificato un errore'
    ];
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>