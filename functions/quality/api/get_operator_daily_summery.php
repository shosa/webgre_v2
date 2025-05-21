<?php
/**
 * API per ottenere il riepilogo giornaliero dei controlli qualità per operatore
 * Endpoint: /api/get_operator_daily_summary.php
 * Metodo: GET
 * Parametri:
 *   - user_id: ID dell'utente/operatore
 *   - date: (opzionale) Data nel formato YYYY-MM-DD, se non specificata usa la data odierna
 * Formato risposta: JSON
 */

// Abilita CORS per consentire richieste da app Android
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestisci le richieste OPTIONS (preflight per CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

// Verifica che il metodo di richiesta sia GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Metodo non consentito']);
    exit;
}

// Includi configurazione e funzioni
require_once '../../../config/config.php';

// Funzione per formattare la data
function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

// Inizializza la risposta
$response = ['status' => 'error', 'message' => 'Errore sconosciuto'];

try {
    // Verifica parametri richiesti
    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        throw new Exception('Parametro user_id mancante o non valido');
    }

    $user_id = $_GET['user_id'];

    // Data di riferimento (oggi se non specificata)
    $date = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // Ottieni la connessione al database
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Conta il totale dei controlli effettuati dall'operatore nella data specificata
    $sql_count = "SELECT COUNT(*) AS total_controls 
                  FROM cq_hermes_records 
                  WHERE operatore = (SELECT user FROM cq_operators WHERE id = :user_id) 
                  AND DATE(data_controllo) = :date";

    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([':user_id' => $user_id, ':date' => $date]);
    $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);

    $total_controls = $count_result ? $count_result['total_controls'] : 0;

    // 2. Recupera la lista dei controlli con relativo numero di eccezioni
    $sql_controls = "SELECT r.id, r.numero_cartellino, r.articolo, r.reparto, 
                    TIME(r.data_controllo) AS ora_controllo, r.tipo_cq,
                    COUNT(e.id) AS numero_eccezioni
                    FROM cq_hermes_records r
                    LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
                    WHERE r.operatore = (SELECT user FROM cq_operators WHERE id = :user_id)
                    AND DATE(r.data_controllo) = :date
                    GROUP BY r.id
                    ORDER BY r.data_controllo DESC";

    $stmt_controls = $pdo->prepare($sql_controls);
    $stmt_controls->execute([':user_id' => $user_id, ':date' => $date]);
    $controls_list = $stmt_controls->fetchAll(PDO::FETCH_ASSOC);

    // 3. Conta il totale delle eccezioni registrate dall'operatore nella data specificata
    $sql_exceptions = "SELECT COUNT(*) AS total_exceptions
                      FROM cq_hermes_eccezioni e
                      JOIN cq_hermes_records r ON e.cartellino_id = r.id
                      WHERE r.operatore = (SELECT user FROM cq_operators WHERE id = :user_id)
                      AND DATE(r.data_controllo) = :date";

    $stmt_exceptions = $pdo->prepare($sql_exceptions);
    $stmt_exceptions->execute([':user_id' => $user_id, ':date' => $date]);
    $exceptions_result = $stmt_exceptions->fetch(PDO::FETCH_ASSOC);

    $total_exceptions = $exceptions_result ? $exceptions_result['total_exceptions'] : 0;

    // 4. Ottieni i tipi di difetti più frequenti
    $sql_top_defects = "SELECT e.tipo_difetto, COUNT(*) AS count
                        FROM cq_hermes_eccezioni e
                        JOIN cq_hermes_records r ON e.cartellino_id = r.id
                        WHERE r.operatore = (SELECT user FROM cq_operators WHERE id = :user_id)
                        AND DATE(r.data_controllo) = :date
                        GROUP BY e.tipo_difetto
                        ORDER BY count DESC
                        LIMIT 5";

    $stmt_top_defects = $pdo->prepare($sql_top_defects);
    $stmt_top_defects->execute([':user_id' => $user_id, ':date' => $date]);
    $top_defects = $stmt_top_defects->fetchAll(PDO::FETCH_ASSOC);

    // 5. Ottieni i reparti con più controlli
    $sql_top_departments = "SELECT reparto, COUNT(*) AS count
                           FROM cq_hermes_records
                           WHERE operatore = (SELECT user FROM cq_operators WHERE id = :user_id)
                           AND DATE(data_controllo) = :date
                           GROUP BY reparto
                           ORDER BY count DESC";

    $stmt_top_departments = $pdo->prepare($sql_top_departments);
    $stmt_top_departments->execute([':user_id' => $user_id, ':date' => $date]);
    $top_departments = $stmt_top_departments->fetchAll(PDO::FETCH_ASSOC);

    // Prepara il risultato
    $response = [
        'status' => 'success',
        'message' => 'Riepilogo giornaliero recuperato con successo',
        'data' => [
            'data' => formatDate($date),
            'total_controls' => $total_controls,
            'total_exceptions' => $total_exceptions,
            'controls_list' => $controls_list,
            'top_defects' => $top_defects,
            'top_departments' => $top_departments
        ]
    ];

} catch (PDOException $e) {
    // Log dell'errore (su server)
    error_log("Errore API get_operator_daily_summary (PDO): " . $e->getMessage());

    // Risposta client (senza dettagli sensibili)
    $response = [
        'status' => 'error',
        'message' => 'Errore durante il recupero dei dati dal database'
    ];
} catch (Exception $e) {
    // Log dell'errore (su server)
    error_log("Errore API get_operator_daily_summary: " . $e->getMessage());

    // Risposta client
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Restituisci la risposta come JSON
echo json_encode($response);
?>