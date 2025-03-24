<?php
/**
 * API per ottenere gli operatori e verificare le credenziali
 * Endpoint: /api/login.php
 * Metodo: POST
 * Formato richiesta: JSON o form-data
 * Formato risposta: JSON
 */

// Headers e configurazione
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestisci le richieste OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metodo non consentito']);
    exit;
}

require_once '../../../config/config.php';

// Inizializza la risposta
$response = ['status' => 'error', 'message' => 'Errore sconosciuto'];

try {
    // Ottieni i dati della richiesta
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
        $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
        $pin = filter_input(INPUT_POST, 'pin', FILTER_SANITIZE_STRING);
    } else {
        $action = isset($data['action']) ? $data['action'] : '';
        $user = isset($data['user']) ? $data['user'] : '';
        $pin = isset($data['pin']) ? $data['pin'] : '';
    }
    
    // Ottieni una connessione al database
    $db = getDbInstance();
    
    // Caso 1: Ottieni la lista di tutti gli utenti
    if ($action === 'get_users') {
        $stmt = $db->query("SELECT id, user, full_name, reparto FROM cq_operators ORDER BY user ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'status' => 'success',
            'message' => 'Utenti recuperati con successo',
            'data' => $users
        ];
    }
    // Caso 2: Verifica le credenziali (username e pin)
    else if ($action === 'login' && !empty($user) && !empty($pin)) {
        $stmt = $db->prepare("SELECT id, user, full_name, reparto FROM cq_operators WHERE user = :user AND pin = :pin LIMIT 1");
        $stmt->bindParam(':user', $user, PDO::PARAM_STR);
        $stmt->bindParam(':pin', $pin, PDO::PARAM_STR);
        $stmt->execute();
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $response = [
                'status' => 'success',
                'message' => 'Login effettuato con successo',
                'data' => $userData
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Credenziali non valide'
            ];
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Parametri mancanti o non validi'
        ];
    }
} catch (PDOException $e) {
    $response = [
        'status' => 'error',
        'message' => 'Errore database: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'Si è verificato un errore: ' . $e->getMessage()
    ];
}

// Invia la risposta JSON
echo json_encode($response);
?>