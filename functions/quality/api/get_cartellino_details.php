<?php
/**
 * API per ottenere i dettagli completi di un cartellino
 * Endpoint: /api/get_cartellino_details.php
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
        // Ottieni l'istanza del database
        $db = getDbInstance();

        // Ottieni le informazioni del cartellino
        $stmt = $db->prepare("SELECT * FROM dati WHERE Cartel = :cartellino");
        $stmt->bindParam(':cartellino', $cartellino, PDO::PARAM_STR);
        $stmt->execute();
        $informazione = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$informazione) {
            $response = [
                'status' => 'error',
                'message' => 'Informazioni cartellino non trovate'
            ];
        } else {
            // Ottieni informazioni sulla linea
            $stmt = $db->prepare("SELECT * FROM linee WHERE sigla = :sigla");
            $stmt->bindParam(':sigla', $informazione['Ln'], PDO::PARAM_STR);
            $stmt->execute();
            $descrizioneLinea = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calcolo del nuovo valore per testid
            $stmt = $db->prepare("SELECT MAX(ID) AS max_id FROM cq_testid");
            $stmt->execute();
            $max_testid = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
            $new_testid = $max_testid + 1;

            // Prepara la data e ora attuali
            $data = date('d/m/Y');
            $orario = date('H:i');

            // Prepara la risposta con tutti i dati necessari
            $response = [
                'status' => 'success',
                'message' => 'Dettagli cartellino trovati',
                'data' => [
                    'cartellino_info' => [
                        'cartellino' => $informazione['Cartel'],
                        'commessa' => $informazione['Commessa Cli'],
                        'codice_articolo' => $informazione['Articolo'],
                        'descrizione_articolo' => $informazione['Descrizione Articolo'],
                        'cliente' => $informazione['Ragione Sociale'],
                        'paia' => $informazione['Tot']
                    ],
                    'linea_info' => [
                        'sigla' => $informazione['Ln'],
                        'descrizione' => $descrizioneLinea['descrizione'] ?? 'N/D'
                    ],
                    'operazione_info' => [
                        'new_testid' => $new_testid,
                        'data' => $data,
                        'orario' => $orario
                    ]
                ]
            ];
        }
    }
} catch (PDOException $e) {
    // Log dell'errore (su server)
    error_log("Errore API get_cartellino_details (PDO): " . $e->getMessage());

    // Risposta client (senza dettagli sensibili)
    $response = [
        'status' => 'error',
        'message' => 'Errore di connessione al database'
    ];
} catch (Exception $e) {
    // Log dell'errore (su server)
    error_log("Errore API get_cartellino_details: " . $e->getMessage());

    // Risposta client
    $response = [
        'status' => 'error',
        'message' => 'Si è verificato un errore'
    ];
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>