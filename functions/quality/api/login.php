<?php
/**
 * API per l'autenticazione degli operatori
 * Endpoint: /api/login.php
 * Metodo: POST 
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
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $pin = filter_input(INPUT_POST, 'pin', FILTER_SANITIZE_NUMBER_INT);
    } else {
        $username = isset($data['username']) ? trim($data['username']) : null;
        $pin = isset($data['pin']) ? intval($data['pin']) : null;
    }

    // Verifica che username e pin siano stati forniti
    if (empty($username) || empty($pin)) {
        $response = [
            'status' => 'error',
            'message' => 'Username o PIN mancanti'
        ];
        http_response_code(400);
    } else {
        // Connessione al database usando PDO
        $pdo = getDbInstance();

        // Prepara la query per verificare le credenziali
        $stmt = $pdo->prepare("SELECT id, user, full_name FROM cq_operators WHERE user = :username AND pin = :pin");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':pin', $pin, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $response = [
                'status' => 'success',
                'message' => 'Accesso effettuato con successo',
                'data' => [
                    'user_id' => $user['id'],
                    'username' => $user['user'],
                    'full_name' => $user['full_name']
                ]
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Credenziali non valide'
            ];
            http_response_code(401); // Unauthorized
        }
    }
} catch (PDOException $e) {
    // Log dell'errore (su server)
    error_log("Errore API login: " . $e->getMessage());

    $response = [
        'status' => 'error',
        'message' => 'Errore di autenticazione'
    ];
    http_response_code(500);
} catch (Exception $e) {
    // Log dell'errore (su server)
    error_log("Errore generico API login: " . $e->getMessage());

    $response = [
        'status' => 'error',
        'message' => 'Si è verificato un errore'
    ];
    http_response_code(500);
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>