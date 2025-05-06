<?php
/**
 * Elaborazione mancanti documento
 * 
 * Questo script calcola e registra gli articoli mancanti per un documento.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';

// Verifica che la richiesta sia di tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

// Recupera e valida il progressivo
$progressivo = filter_input(INPUT_POST, 'progressivo', FILTER_VALIDATE_INT);
if (!$progressivo) {
    echo json_encode(['success' => false, 'message' => 'Progressivo non valido']);
    exit();
}

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Inizia una transazione per garantire l'integrità dei dati
    $conn->beginTransaction();

    // Cancella i record esistenti dalla tabella exp_dati_mancanti
    $stmt = $conn->prepare("DELETE FROM exp_dati_mancanti WHERE id_documento = :id_documento");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();

    // Recupera gli articoli con qta_reale minore di qta_originale
    $stmt = $conn->prepare("SELECT id, codice_articolo,descrizione, qta_originale, qta_reale 
                          FROM exp_dati_articoli 
                          WHERE id_documento = :id_documento AND qta_reale < qta_originale");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $articoliMancanti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inserisci i nuovi record per gli articoli mancanti
    $stmt = $conn->prepare("INSERT INTO exp_dati_mancanti (id_documento, codice_articolo, descrizione, qta_mancante) 
                          VALUES (:id_documento, :codice_articolo,:descrizione, :qta_mancante)");

    foreach ($articoliMancanti as $articolo) {
        // Arrotonda la quantità mancante a due cifre decimali
        $qta_mancante = round($articolo['qta_originale'] - $articolo['qta_reale'], 2);

        $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
        $stmt->bindParam(':codice_articolo', $articolo['codice_articolo']);
        $stmt->bindParam(':descrizione', $articolo['descrizione']);
        $stmt->bindParam(':qta_mancante', $qta_mancante);
        $stmt->execute();
    }

    // Commit della transazione
    $conn->commit();


    // Restituisci una risposta di successo
    echo json_encode([
        'success' => true,
        'message' => 'Operazione completata con successo! Mancanti elaborati: ' . count($articoliMancanti)
    ]);

} catch (PDOException $e) {
    // In caso di errore, annulla tutte le modifiche
    if ($conn) {
        $conn->rollBack();
    }

    // Log dell'errore
    error_log("Errore nell'elaborazione mancanti per documento {$progressivo}: " . $e->getMessage());

    // Restituisci una risposta di errore
    echo json_encode([
        'success' => false,
        'message' => 'Errore durante l\'elaborazione dei mancanti: ' . $e->getMessage()
    ]);
}
?>