<?php
/**
 * API per ottenere i dettagli completi di un record_id
 * Endpoint: /api/get_record_id_details.php
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
        $record_id = filter_input(INPUT_POST, 'record_id', FILTER_SANITIZE_STRING);
    } else {
        $record_id = isset($data['record_id']) ? trim($data['record_id']) : null;
    }

    // Verifica che il record_id sia stato fornito
    if (empty($record_id)) {
        $response = [
            'status' => 'error',
            'message' => 'Parametro record_id mancante'
        ];
    } else {
        // Ottieni l'istanza del database
        $db = getDbInstance();

        // Ottieni le informazioni del record_id
        $stmt = $db->prepare("SELECT * FROM cq_hermes_records WHERE id = :record_id");
        $stmt->bindParam(':record_id', $record_id, PDO::PARAM_STR);
        $stmt->execute();
        $informazione = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$informazione) {
            $response = [
                'status' => 'error',
                'message' => 'Informazioni record_id non trovate'
            ];
        } else {
            // Prepara la risposta con tutti i dati necessari
            $response = [
                'status' => 'success',
                'message' => 'Dettagli record_id trovati',
                'data' => [
                    'record_id_info' => [
                        'record_id' => $informazione['id'],
                        'cartellino' => $informazione['cartellino'],
                        'reparto' => $informazione['reparto'],
                        'timestamp' => $informazione['data_controllo'],
                        'operatore' => $informazione['operatore'],
                        'tipo' => $informazione['tipo_cq'],
                        'paia' => $informazione['paia_totali'],
                        'codice_articolo' => $informazione['cod_articolo'],
                        'descrizione' => $informazione['articolo'],
                        'linea' => $informazione['linea'],
                        'note' => $informazione['note']
                    ]
                ]
            ];
            
            // Verifica se il record ha eccezioni (ha_eccezioni = 1)
            if (isset($informazione['ha_eccezioni']) && $informazione['ha_eccezioni'] == 1) {
                // Cerca le eccezioni associate a questo record nella tabella CQ_HERMES_ECCEZIONI
                $stmt_eccezioni = $db->prepare("SELECT * FROM CQ_HERMES_ECCEZIONI WHERE cartellino_id = :cartellino_id");
                $stmt_eccezioni->bindParam(':cartellino_id', $informazione['cartellino'], PDO::PARAM_STR);
                $stmt_eccezioni->execute();
                $eccezioni = $stmt_eccezioni->fetchAll(PDO::FETCH_ASSOC);
                
                // Aggiungi le eccezioni alla risposta
                if ($eccezioni && count($eccezioni) > 0) {
                    $response['data']['eccezioni'] = $eccezioni;
                    $response['data']['has_eccezioni'] = true;
                    $response['data']['eccezioni_count'] = count($eccezioni);
                } else {
                    $response['data']['has_eccezioni'] = true;
                    $response['data']['eccezioni_count'] = 0;
                    $response['data']['eccezioni'] = [];
                }
            } else {
                // Non ci sono eccezioni
                $response['data']['has_eccezioni'] = false;
                $response['data']['eccezioni_count'] = 0;
                $response['data']['eccezioni'] = [];
            }
        }
    }
} catch (PDOException $e) {
    // Log dell'errore (su server)
    error_log("Errore API get_record_id_details (PDO): " . $e->getMessage());

    // Risposta client (senza dettagli sensibili)
    $response = [
        'status' => 'error',
        'message' => 'Errore di connessione al database'
    ];
} catch (Exception $e) {
    // Log dell'errore (su server)
    error_log("Errore API get_record_id_details: " . $e->getMessage());

    // Risposta client
    $response = [
        'status' => 'error',
        'message' => 'Si è verificato un errore'
    ];
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>