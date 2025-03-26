<?php
/**
 * API per ottenere opzioni di test, calzate, reparti e altre configurazioni
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
    
    // NUOVA FUNZIONALITÀ: Recupera i reparti HERMES
    $repartiHermesOptions = [];
    $stmt = $db->query("SELECT id, nome_reparto FROM cq_hermes_reparti WHERE attivo = 1 ORDER BY ordine ASC, nome_reparto ASC");
    $repartiHermes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($repartiHermes as $reparto) {
        $repartiHermesOptions[] = [
            'id' => $reparto['id'],
            'nome' => $reparto['nome_reparto']
        ];
    }
    
    // NUOVA FUNZIONALITÀ: Recupera i tipi di difetti HERMES
    $difettiOptions = [];
    $stmt = $db->query("SELECT id, descrizione, categoria FROM cq_hermes_tipi_difetti WHERE attivo = 1 ORDER BY ordine ASC, descrizione ASC");
    $difetti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($difetti as $difetto) {
        $difettiOptions[] = [
            'id' => $difetto['id'],
            'descrizione' => $difetto['descrizione'],
            'categoria' => $difetto['categoria']
        ];
    }
    
    // Usa le calzate standard già recuperate per HERMES
    $taglieHermesOptions = [];
    if (!empty($calzateOptions)) {
        foreach ($calzateOptions as $index => $taglia) {
            $taglieHermesOptions[] = [
                'id' => $index + 1, // Generare un ID incrementale
                'taglia' => $taglia
            ];
        }
    }
    
    // Prepara la risposta
    $response = [
        'status' => 'success',
        'message' => 'Opzioni recuperate con successo',
        'data' => [
            'calzate' => $calzateOptions,
            'tests' => $testOptions,
            'reparti' => $repartiOptions,
            // Aggiungiamo i nuovi dati per HERMES
            'hermes' => [
                'reparti' => $repartiHermesOptions,
                'tipi_difetti' => $difettiOptions,
                'taglie' => $taglieHermesOptions
            ]
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