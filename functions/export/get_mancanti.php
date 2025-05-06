<?php
/**
 * get_mancanti.php - Recupera tutti i mancanti disponibili
 * 
 * Questo script recupera tutti i mancanti disponibili, raggruppati per documento.
 */
session_start();
require_once '../../config/config.php';

require_once BASE_PATH . '/utils/helpers.php';


// Recupera il progressivo corrente (solo per escluderlo dai risultati)
$progressivo = filter_input(INPUT_POST, 'progressivo', FILTER_VALIDATE_INT);

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Costruisci la query per recuperare tutti i mancanti
    // Ora recuperiamo direttamente il campo descrizione dalla tabella exp_dati_mancanti
    $query = "
        SELECT m.id, m.id_documento, m.codice_articolo, m.qta_mancante, m.descrizione, m.data_creazione,
               d.data AS data_documento
        FROM exp_dati_mancanti m
        LEFT JOIN exp_documenti d ON m.id_documento = d.id
    ";
    
    // Se abbiamo un progressivo, escludiamo i mancanti di quel documento
    $params = [];
    if ($progressivo) {
        $query .= " WHERE m.id_documento != :progressivo";
        $params[':progressivo'] = $progressivo;
    }
    
    // Ordina per documento e codice articolo
    $query .= " ORDER BY m.id_documento DESC, m.codice_articolo ASC";
    
    $stmt = $conn->prepare($query);
    
    // Bind dei parametri
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    
    $stmt->execute();
    $mancanti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatta le date per ogni mancante
    foreach ($mancanti as &$mancante) {
        if (isset($mancante['data_documento'])) {
            $timestamp = strtotime($mancante['data_documento']);
            $mancante['data_documento'] = date('d/m/Y', $timestamp);
        }
    }
    
    // Ritorna i mancanti
    echo json_encode(['success' => true, 'mancanti' => $mancanti]);
} catch (PDOException $e) {
    error_log('Errore durante il recupero dei mancanti: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Errore durante il recupero dei mancanti: ' . $e->getMessage()]);
    exit;
}
?>