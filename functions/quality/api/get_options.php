<?php
/**
 * API per ottenere opzioni di test e calzate
 * Endpoint: /api/get_options.php
 * Metodo: POST
 * Formato richiesta: JSON o form-data
 * Formato risposta: JSON
 */

// Headers e configurazione come nelle altre API

require_once '../../../config/config.php';

// Inizializza l'array di risposta
$response = ['status' => 'error', 'message' => 'Errore sconosciuto'];

try {
    // Ottieni il cartellino dalla richiesta
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $cartellino = filter_input(INPUT_POST, 'cartellino', FILTER_SANITIZE_STRING);
    } else {
        $cartellino = isset($data['cartellino']) ? trim($data['cartellino']) : null;
    }
    
    // Connessione al database
    $db = getDbInstance();
    
    // Recupera l'ID numerate dal cartellino
    $stmt = $db->prepare("SELECT Nu FROM dati WHERE Cartel = :cartellino");
    $stmt->bindParam(':cartellino', $cartellino, PDO::PARAM_STR);
    $stmt->execute();
    $datiResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $calzateOptions = [];
    if ($datiResult && !empty($datiResult['Nu'])) {
        // Recupera le calzate da id_numerate
        $stmt = $db->prepare("SELECT * FROM id_numerate WHERE id = :id");
        $stmt->bindParam(':id', $datiResult['Nu'], PDO::PARAM_STR);
        $stmt->execute();
        $idNumerate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($idNumerate) {
            for ($j = 1; $j <= 20; $j++) {
                $field = 'N' . str_pad($j, 2, '0', STR_PAD_LEFT);
                if (!empty($idNumerate[$field])) {
                    $calzateOptions[] = $idNumerate[$field];
                }
            }
        }
    }
    
    // Recupera tutti i test disponibili
    $testOptions = [];
    $stmt = $db->query("SELECT test FROM cq_barcodes ORDER BY test ASC");
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tests as $test) {
        $testOptions[] = $test['test'];
    }
    
    // Recupera i reparti
    $repartiOptions = [];
    $stmt = $db->query("SELECT Nome FROM reparti ORDER BY Nome ASC");
    $reparti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($reparti as $reparto) {
        $repartiOptions[] = $reparto['Nome'];
    }
    
    // Prepara la risposta
    $response = [
        'status' => 'success',
        'message' => 'Opzioni recuperate con successo',
        'data' => [
            'calzate' => $calzateOptions,
            'tests' => $testOptions,
            'reparti' => $repartiOptions
        ]
    ];
    
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

// Restituisci la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);
?>