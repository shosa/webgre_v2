<?php
/**
 * API per salvare i record di controllo qualità HERMES
 * Endpoint: /api/save_hermes_cq.php
 * Metodo: POST
 * Formato richiesta: JSON
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

// Includi configurazione e funzioni
require_once '../../../config/config.php';

// Funzione per registrare attività (adattata per l'API)
function logApiActivity($user_id, $modulo, $azione, $descrizione, $item = '')
{
    try {
        $pdo = getDbInstance();
        $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, modulo, azione, descrizione, item, timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $modulo, $azione, $descrizione, $item]);
        return true;
    } catch (PDOException $e) {
        error_log('Errore nel log attività: ' . $e->getMessage());
        return false;
    }
}

// Inizializza la risposta
$response = ['status' => 'error', 'message' => 'Errore sconosciuto'];

try {
    // Ottieni i dati JSON dalla richiesta
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Errore nel formato JSON della richiesta');
    }

    // Verifica i dati obbligatori per il record principale
    $required_fields = [
        'numero_cartellino',
        'reparto',
        'operatore',
        'tipo_cq',
        'paia_totali',
        'cod_articolo',
        'articolo',
        'linea',
        'note',
        'user_id'
    ];

    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Campo obbligatorio mancante: $field");
        }
    }

    // Verifica se ci sono eccezioni
    $has_exceptions = isset($data['eccezioni']) && is_array($data['eccezioni']) && !empty($data['eccezioni']);

    // Ottieni la connessione al database
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Avvia una transazione
    $pdo->beginTransaction();

    // Inserisci il record principale
    $insert_main = [
        ':numero_cartellino' => $data['numero_cartellino'],
        ':reparto' => $data['reparto'],
        ':data_controllo' => date('Y-m-d H:i:s'), // Timestamp corrente
        ':operatore' => $data['operatore'],
        ':tipo_cq' => $data['tipo_cq'],
        ':paia_totali' => $data['paia_totali'],
        ':cod_articolo' => $data['cod_articolo'],
        ':articolo' => $data['articolo'],
        ':linea' => $data['linea'],
        ':note' => $data['note'],
        ':ha_eccezioni' => $has_exceptions ? 1 : 0
    ];

    $sql_main = "INSERT INTO cq_hermes_records (
        numero_cartellino, reparto, data_controllo, operatore, 
        tipo_cq, paia_totali, cod_articolo, articolo, 
        linea, note, ha_eccezioni
    ) VALUES (
        :numero_cartellino, :reparto, :data_controllo, :operatore, 
        :tipo_cq, :paia_totali, :cod_articolo, :articolo, 
        :linea, :note, :ha_eccezioni
    )";
    
    $stmt_main = $pdo->prepare($sql_main);
    $stmt_main->execute($insert_main);
    
    // Ottieni l'ID del record principale appena inserito
    $main_record_id = $pdo->lastInsertId();
    
    // Se ci sono eccezioni, inseriscile
    $exceptions_count = 0;
    if ($has_exceptions) {
        foreach ($data['eccezioni'] as $exception) {
            // Verifica i campi obbligatori per ciascuna eccezione
            if (!isset($exception['taglia']) || !isset($exception['tipo_difetto'])) {
                continue; // Salta questa eccezione se mancano i campi obbligatori
            }
            
            $insert_exception = [
                ':cartellino_id' => $main_record_id,
                ':taglia' => $exception['taglia'],
                ':tipo_difetto' => $exception['tipo_difetto'],
                ':note_operatore' => isset($exception['note_operatore']) ? $exception['note_operatore'] : null
            ];
            
            $sql_exception = "INSERT INTO cq_hermes_eccezioni (
                cartellino_id, taglia, tipo_difetto, note_operatore
            ) VALUES (
                :cartellino_id, :taglia, :tipo_difetto, :note_operatore
            )";
            
            $stmt_exception = $pdo->prepare($sql_exception);
            $stmt_exception->execute($insert_exception);
            
            $exceptions_count++;
        }
    }
    
    // Registra l'attività
    logApiActivity(
        $data['user_id'],
        'CQ_HERMES',
        'INSERIMENTO',
        'Controllo qualità HERMES',
        'Cartellino ' . $data['numero_cartellino']
    );
    
    // Commit della transazione
    $pdo->commit();
    
    $response = [
        'status' => 'success',
        'message' => 'Controllo qualità HERMES salvato con successo',
        'data' => [
            'cartellino_id' => $main_record_id,
            'numero_cartellino' => $data['numero_cartellino'],
            'eccezioni_salvate' => $exceptions_count
        ]
    ];

} catch (PDOException $e) {
    // Rollback in caso di errore
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log dell'errore (su server)
    error_log("Errore API save_hermes_cq (PDO): " . $e->getMessage());

    // Risposta client (senza dettagli sensibili)
    $response = [
        'status' => 'error',
        'message' => 'Errore durante il salvataggio dei dati nel database'
    ];
} catch (Exception $e) {
    // Rollback in caso di errore
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log dell'errore (su server)
    error_log("Errore API save_hermes_cq: " . $e->getMessage());

    // Risposta client
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>