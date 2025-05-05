<?php
/**
 * Aggiornamento dati articolo DDT
 * 
 * Questo script gestisce la richiesta AJAX di aggiornamento di un singolo campo
 * di un articolo nel database.
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

// Recupera e valida i parametri
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$field = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_SPECIAL_CHARS);
$value = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_SPECIAL_CHARS);

// Verifica che tutti i parametri siano presenti
if (!$id || !$field) {
    echo json_encode(['success' => false, 'message' => 'Parametri mancanti o non validi']);
    exit();
}

// Lista dei campi che possono essere aggiornati
$allowed_fields = ['descrizione', 'voce_doganale', 'qta_reale', 'prezzo_unitario'];

// Verifica che il campo sia tra quelli consentiti
if (!in_array($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'message' => 'Campo non valido']);
    exit();
}

// Formattazione corretta per i campi numerici
if (in_array($field, ['qta_reale', 'prezzo_unitario'])) {
    // Sostituisce la virgola con il punto per i numeri decimali
    $value = str_replace(',', '.', $value);
    
    // Verifica che il valore sia numerico
    if (!is_numeric($value)) {
        echo json_encode(['success' => false, 'message' => 'Il valore deve essere un numero valido']);
        exit();
    }
}

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Preparazione della query di aggiornamento
    $sql = "UPDATE exp_dati_articoli SET {$field} = :value WHERE id = :id";
    $stmt = $conn->prepare($sql);
    
    // Binding dei parametri
    $stmt->bindParam(':value', $value);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    // Esecuzione della query
    $stmt->execute();
    
   
    
    // Restituisci una risposta di successo
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    // Log dell'errore
    error_log("Errore nell'aggiornamento del campo {$field}: " . $e->getMessage());
    
    // Restituisci una risposta di errore
    echo json_encode([
        'success' => false, 
        'message' => 'Errore durante l\'aggiornamento del campo: ' . $e->getMessage()
    ]);
}
?>