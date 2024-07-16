<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Verifica che i dati siano stati inviati tramite POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupero dei dati inviati tramite POST
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        // Se non è stato possibile decodificare i dati JSON
        $_SESSION['danger'] = "Dati non validi inviati.";
        echo json_encode(['success' => false, 'message' => 'Dati non validi inviati.']);
        exit();
    }

    $type_id = $data['type_id'];
    $lotNumbers = $data['lotNumbers'];
    $cartelli = $data['cartelli'];

    // Connessione al database con PDO
    $pdo = getDbInstance();

    // Preparazione della query per l'inserimento dei dati
    $stmt = $pdo->prepare("INSERT INTO track_links (cartel, type_id, lot) VALUES (:cartel, :type_id, :lot)");

    try {
        $pdo->beginTransaction();

        // Array per memorizzare le query eseguite
        $queries = [];

        foreach ($cartelli as $cartel) {
            foreach ($lotNumbers as $lot) {
                // Esecuzione della query per ogni combinazione di cartello e lotto
                $stmt->execute([
                    ':cartel' => $cartel,
                    ':type_id' => $type_id,
                    ':lot' => $lot
                ]);

                // Aggiungi la query eseguita all'array
                $queries[] = $stmt->queryString;
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Associazione salvata con successo.";

        // Invia una risposta JSON con le query eseguite
        echo json_encode([
            'success' => true,
            'message' => 'Associazione salvata con successo.',
            'queries' => $queries
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['danger'] = "Si è verificato un errore durante il salvataggio: " . $e->getMessage();

        // Invia una risposta JSON con l'errore
        echo json_encode([
            'success' => false,
            'message' => 'Si è verificato un errore durante il salvataggio.',
            'error' => $e->getMessage()
        ]);
    }
} else {
    $_SESSION['danger'] = "Metodo non consentito per l'accesso a questa pagina.";

    // Invia una risposta JSON se il metodo non è consentito
    echo json_encode([
        'success' => false,
        'message' => 'Metodo non consentito per l\'accesso a questa pagina.'
    ]);
}

exit();
?>