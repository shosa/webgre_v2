<?php
/**
 * Processo di importazione dati Excel
 * 
 * Questo script elabora i file Excel temporanei, estraendone i dati e inserendoli nel database.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';

require_once BASE_PATH . '/vendor/autoload.php';

// Verifica che la richiesta sia di tipo GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

// Recupera e valida il progressivo
$progressivo = filter_input(INPUT_GET, 'progressivo', FILTER_VALIDATE_INT);
if (!$progressivo) {
    echo json_encode(['success' => false, 'message' => 'Progressivo non valido']);
    exit();
}

// Definizione delle directory
$dir = 'temp/';
$destDir = 'src/';

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Inizia una transazione per garantire l'integrità dei dati
    $conn->beginTransaction();
    
    // Elimina i dati articoli esistenti
    $stmt = $conn->prepare("DELETE FROM exp_dati_articoli WHERE id_documento = :id_documento");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    
    // Crea la cartella di destinazione se non esiste
    if (!file_exists($destDir . $progressivo)) {
        mkdir($destDir . $progressivo, 0777, true);
    }
    
    // Ottieni la lista dei file nella directory temporanea
    $files = scandir($dir);
    $files = array_diff($files, array('.', '..'));
    
    foreach ($files as $file) {
        $filePath = $dir . $file;
        $destFilePath = $destDir . $progressivo . '/' . $file;

        // Sposta il file nella cartella di destinazione
        rename($filePath, $destFilePath);

        // Leggi il file Excel
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($destFilePath);
        $spreadsheet = $reader->load($destFilePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Estrai i dati del lancio
        $lancio = $worksheet->getCell('B2')->getValue();
        $articolo = $worksheet->getCell('B1')->getValue();
        $paia = $worksheet->getCell('B3')->getValue();

        // Inserisci i dati del lancio
        $stmt = $conn->prepare("INSERT INTO exp_dati_lanci_ddt (id_doc, lancio, articolo, paia) VALUES (:id_doc, :lancio, :articolo, :paia)");
        $stmt->bindParam(':id_doc', $progressivo, PDO::PARAM_INT);
        $stmt->bindParam(':lancio', $lancio);
        $stmt->bindParam(':articolo', $articolo);
        $stmt->bindParam(':paia', $paia, PDO::PARAM_INT);
        $stmt->execute();

        // Estrai i dati degli articoli
        $rows = $worksheet->toArray();
        $rows = array_slice($rows, 6); // Salta le prime 6 righe di intestazione

        $stmt = $conn->prepare("INSERT INTO exp_dati_articoli (id_documento, codice_articolo, descrizione, um, qta_originale, qta_reale) 
                              VALUES (:id_documento, :codice_articolo, :descrizione, :um, :qta_originale, :qta_reale)");

        foreach ($rows as $row) {
            // Salta la riga con 'ORLATURA' nella cella A
            if ($row[0] === 'ORLATURA') {
                continue;
            }
            if ($row[0] === 'AUTORIZZAZIONE:') {
                continue;
            }
            // Verifica che ci siano dati validi
            if (empty($row[1])) {
                continue;
            }

            $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
            $stmt->bindParam(':codice_articolo', $row[1]);
            $stmt->bindParam(':descrizione', $row[2]);
            $stmt->bindParam(':um', $row[3]);
            $stmt->bindParam(':qta_originale', $row[5]);
            $stmt->bindParam(':qta_reale', $row[5]);
            $stmt->execute();
        }
    }
    
    // Rimuovi i duplicati e aggiorna le quantità
    // Prima ottieni i dati aggregati per codice articolo
    $stmt = $conn->prepare("SELECT codice_articolo, descrizione, um, voce_doganale, 
                            ROUND(SUM(qta_originale), 2) as qta_originale, 
                            ROUND(SUM(qta_reale), 2) as qta_reale
                            FROM exp_dati_articoli
                            WHERE id_documento = :id_documento
                            GROUP BY codice_articolo, descrizione, um, voce_doganale");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $aggregatedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Elimina tutti i record esistenti
    $stmt = $conn->prepare("DELETE FROM exp_dati_articoli WHERE id_documento = :id_documento");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    
    // Reinserisci i dati aggregati
    $stmt = $conn->prepare("INSERT INTO exp_dati_articoli 
                          (id_documento, codice_articolo, descrizione, voce_doganale, um, qta_originale, qta_reale) 
                          VALUES (:id_documento, :codice_articolo, :descrizione, :voce_doganale, :um, :qta_originale, :qta_reale)");
    
    foreach ($aggregatedItems as $item) {
        $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
        $stmt->bindParam(':codice_articolo', $item['codice_articolo']);
        $stmt->bindParam(':descrizione', $item['descrizione']);
        $stmt->bindParam(':voce_doganale', $item['voce_doganale']);
        $stmt->bindParam(':um', $item['um']);
        $stmt->bindParam(':qta_originale', $item['qta_originale']);
        $stmt->bindParam(':qta_reale', $item['qta_reale']);
        $stmt->execute();
    }
    
    // Commit della transazione
    $conn->commit();

    
    // Restituisci una risposta di successo
    echo json_encode(['success' => true, 'message' => 'DDT generati con successo']);
    
} catch (PDOException $e) {
    // In caso di errore, annulla tutte le modifiche
    if ($conn) {
        $conn->rollBack();
    }
    
    // Log dell'errore
    error_log("Errore nell'elaborazione dei file Excel per documento {$progressivo}: " . $e->getMessage());
    
    // Restituisci una risposta di errore
    echo json_encode([
        'success' => false, 
        'message' => 'Errore durante l\'elaborazione dei file Excel: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Gestisci altre eccezioni (es. errori PhpSpreadsheet)
    if ($conn) {
        $conn->rollBack();
    }
    
    // Log dell'errore
    error_log("Errore nell'elaborazione dei file Excel per documento {$progressivo}: " . $e->getMessage());
    
    // Restituisci una risposta di errore
    echo json_encode([
        'success' => false, 
        'message' => 'Errore durante l\'elaborazione dei file Excel: ' . $e->getMessage()
    ]);
}
?>