<?php
/**
 * add_mancanti_to_ddt.php - Aggiunge i mancanti selezionati al DDT corrente
 * 
 * Questo script aggiunge i mancanti selezionati al DDT corrente e li rimuove dalla tabella dei mancanti.
 */
session_start();
require_once '../../config/config.php';

require_once BASE_PATH . '/utils/helpers.php';


// Verifica che la richiesta sia di tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

// Recupera i dati dalla richiesta
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!isset($data['progressivo']) || !isset($data['mancantiIds']) || empty($data['mancantiIds'])) {
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

$progressivo = (int)$data['progressivo'];
$mancantiIds = $data['mancantiIds'];

// Converti tutti gli ID in integer
foreach ($mancantiIds as &$id) {
    $id = (int)$id;
}

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Inizia una transazione
    $conn->beginTransaction();
    
    // Recupera la data del DDT corrente
    $stmt = $conn->prepare("SELECT data FROM exp_documenti WHERE id = :id");
    $stmt->bindParam(':id', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$documento) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Documento non trovato']);
        exit;
    }
    
    // Formatta la data per il riferimento
    $dataFormattata = date('d/m/Y', strtotime($documento['data']));
    $riferimento = "DDT $progressivo - $dataFormattata";
    
    // Per ogni mancante selezionato
    foreach ($mancantiIds as $mancanteId) {
        // Recupera i dettagli del mancante
        // Ora recuperiamo la descrizione direttamente dalla tabella exp_dati_mancanti
        $stmt = $conn->prepare("
            SELECT m.id_documento, m.codice_articolo, m.qta_mancante, m.descrizione,
                   COALESCE(a.voce_doganale, '') as voce_doganale, 
                   COALESCE(a.um, 'pz') as um, 
                   COALESCE(a.prezzo_unitario, 0.00) as prezzo_unitario
            FROM exp_dati_mancanti m
            LEFT JOIN exp_dati_articoli a ON m.id_documento = a.id_documento AND m.codice_articolo = a.codice_articolo
            WHERE m.id = :id
        ");
        $stmt->bindParam(':id', $mancanteId, PDO::PARAM_INT);
        $stmt->execute();
        $mancante = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$mancante) {
            continue; // Salta questo mancante se non viene trovato
        }
        
        // Ottieni il documento di origine
        $stmt = $conn->prepare("SELECT data FROM exp_documenti WHERE id = :id");
        $stmt->bindParam(':id', $mancante['id_documento'], PDO::PARAM_INT);
        $stmt->execute();
        $docOrigine = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Formatta la data per il riferimento di origine
        $dataOrigine = date('d/m/Y', strtotime($docOrigine['data']));
        $riferimentoOrigine = "DDT {$mancante['id_documento']} - $dataOrigine";
        
        // Aggiungi il mancante come articolo nel DDT corrente
        $stmt = $conn->prepare("
            INSERT INTO exp_dati_articoli 
            (id_documento, codice_articolo, descrizione, voce_doganale, um, qta_originale, qta_reale, prezzo_unitario, is_mancante, rif_mancante) 
            VALUES 
            (:id_documento, :codice_articolo, :descrizione, :voce_doganale, :um, :qta_originale, :qta_reale, :prezzo_unitario, 1, :rif_mancante)
        ");
        
        $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
        $stmt->bindParam(':codice_articolo', $mancante['codice_articolo']);
        $stmt->bindParam(':descrizione', $mancante['descrizione']);
        $stmt->bindParam(':voce_doganale', $mancante['voce_doganale']);
        $stmt->bindParam(':um', $mancante['um']);
        $stmt->bindParam(':qta_originale', $mancante['qta_mancante'], PDO::PARAM_STR);
        $stmt->bindParam(':qta_reale', $mancante['qta_mancante'], PDO::PARAM_STR);
        $stmt->bindParam(':prezzo_unitario', $mancante['prezzo_unitario'], PDO::PARAM_STR);
        $stmt->bindParam(':rif_mancante', $riferimentoOrigine);
        
        $stmt->execute();
        
        // Elimina il mancante dalla tabella exp_dati_mancanti
        $stmt = $conn->prepare("DELETE FROM exp_dati_mancanti WHERE id = :id");
        $stmt->bindParam(':id', $mancanteId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
 
    // Conferma la transazione
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Mancanti aggiunti con successo al DDT'
    ]);
} catch (PDOException $e) {
    // In caso di errore, annulla la transazione
    if ($conn) {
        $conn->rollBack();
    }
    
    error_log('Errore durante l\'aggiunta dei mancanti: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Errore durante l\'aggiunta dei mancanti: ' . $e->getMessage()]);
    exit;
}
?>