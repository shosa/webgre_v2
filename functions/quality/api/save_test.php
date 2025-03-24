<?php
/**
 * API per salvare i record di controllo qualità
 * Endpoint: /api/save_test.php
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

    // Verifica i dati obbligatori
    $required_fields = [
        'new_testid',
        'reparto',
        'cartellino',
        'commessa',
        'codArticolo',
        'descArticolo',
        'calzata',
        'test',
        'note',
        'esito',
        'data',
        'orario',
        'operatore',
        'siglaLinea',
        'paia',
        'user_id'
    ];

    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Campo obbligatorio mancante: $field");
        }
    }

    // Validazione array
    if (
        !is_array($data['calzata']) || !is_array($data['test']) ||
        !is_array($data['note']) || !is_array($data['esito'])
    ) {
        throw new Exception('I campi calzata, test, note ed esito devono essere array');
    }

    if (
        count($data['calzata']) !== count($data['test']) ||
        count($data['test']) !== count($data['note']) ||
        count($data['note']) !== count($data['esito'])
    ) {
        throw new Exception('Gli array devono avere la stessa lunghezza');
    }

    // Ottieni la connessione al database
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Avvia una transazione
    $pdo->beginTransaction();

    $new_testid = $data['new_testid'];
    $initial_testid = $new_testid;
    $successful_inserts = 0;

    // Esegui un loop su ogni riga inviata
    for ($i = 0; $i < count($data['calzata']); $i++) {
        // Controlla se il valore della calzata non è vuoto
        if (!empty($data['calzata'][$i])) {
            $insert_data = [
                ':testid' => $new_testid,
                ':reparto' => $data['reparto'],
                ':cartellino' => $data['cartellino'],
                ':commessa' => $data['commessa'],
                ':cod_articolo' => $data['codArticolo'],
                ':articolo' => $data['descArticolo'],
                ':calzata' => $data['calzata'][$i],
                ':test' => $data['test'][$i],
                ':note' => $data['note'][$i],
                ':esito' => $data['esito'][$i],
                ':data' => $data['data'],
                ':orario' => $data['orario'],
                ':operatore' => $data['operatore'],
                ':linea' => $data['siglaLinea'],
                ':pa' => $data['paia']
            ];

            // Esegui l'istruzione SQL INSERT
            $sql = "INSERT INTO cq_records (testid, reparto, cartellino, commessa, cod_articolo, articolo, calzata, test, note, esito, data, orario, operatore, linea, pa) 
                    VALUES (:testid, :reparto, :cartellino, :commessa, :cod_articolo, :articolo, :calzata, :test, :note, :esito, :data, :orario, :operatore, :linea, :pa)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($insert_data);

            $successful_inserts++;
            $new_testid++; // Incrementa testid per il prossimo inserimento
        }
    }

    // Se almeno un inserimento è riuscito, aggiorna il record nella tabella cq_testid
    if ($successful_inserts > 0) {
        $update_sql = "UPDATE cq_testid SET ID = :id WHERE ID = :initial_id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([':id' => $new_testid - 1, ':initial_id' => $initial_testid - 1]);

        // Registra l'attività
        logApiActivity(
            $data['user_id'],
            'CQ',
            'FINE',
            'Test #' . $initial_testid,
            'Cartellino ' . $data['cartellino']
        );

        // Commit della transazione
        $pdo->commit();

        $response = [
            'status' => 'success',
            'message' => 'Test salvato con successo',
            'data' => [
                'testid' => $initial_testid,
                'records_saved' => $successful_inserts,
                'new_testid' => $new_testid
            ]
        ];
    } else {
        // Nessun record inserito, rollback
        $pdo->rollBack();
        $response = [
            'status' => 'error',
            'message' => 'Nessun record valido da inserire'
        ];
    }

} catch (PDOException $e) {
    // Rollback in caso di errore
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log dell'errore (su server)
    error_log("Errore API save_test (PDO): " . $e->getMessage());

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
    error_log("Errore API save_test: " . $e->getMessage());

    // Risposta client
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>