<?php
session_start();
require_once '../../config/config.php';


// Recupera il progressivo dalla richiesta POST
$progressivo = $_POST['progressivo'];

// Recupera l'istanza del database PDO
$pdo = getDbInstance();

try {
    // Ottieni tutti i codici articolo unici per il progressivo dato
    $stmt = $pdo->prepare('SELECT DISTINCT codice_articolo FROM exp_dati_articoli WHERE id_documento = :progressivo');
    $stmt->bindParam(':progressivo', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $other_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Per ogni codice articolo trovato
    foreach ($other_codes as $code) {
        // Prepara la query per trovare l'articolo con lo stesso codice ma diverso id_documento
        $stmt_find = $pdo->prepare('SELECT voce_doganale, prezzo_unitario FROM exp_dati_articoli 
                                  WHERE codice_articolo = :codice_articolo 
                                  AND id_documento != :progressivo 
                                  LIMIT 1');
        $stmt_find->bindParam(':codice_articolo', $code['codice_articolo'], PDO::PARAM_STR);
        $stmt_find->bindParam(':progressivo', $progressivo, PDO::PARAM_INT);
        $stmt_find->execute();
        $article = $stmt_find->fetch(PDO::FETCH_ASSOC);

        // Se è stato trovato un articolo
        if ($article) {
            // Aggiorna l'articolo corrente con i valori trovati
            $stmt_update = $pdo->prepare('UPDATE exp_dati_articoli 
                                       SET voce_doganale = :voce_doganale, 
                                           prezzo_unitario = :prezzo_unitario 
                                       WHERE codice_articolo = :codice_articolo 
                                       AND id_documento = :progressivo');
            
            $stmt_update->bindParam(':voce_doganale', $article['voce_doganale'], PDO::PARAM_STR);
            $stmt_update->bindParam(':prezzo_unitario', $article['prezzo_unitario'], PDO::PARAM_STR);
            $stmt_update->bindParam(':codice_articolo', $code['codice_articolo'], PDO::PARAM_STR);
            $stmt_update->bindParam(':progressivo', $progressivo, PDO::PARAM_INT);
            $stmt_update->execute();
        }
    }

    // Aggiorna il campo first_boot nella tabella exp_documenti
    $stmt_first_boot = $pdo->prepare('UPDATE exp_documenti SET first_boot = 0 WHERE id = :progressivo');
    $stmt_first_boot->bindParam(':progressivo', $progressivo, PDO::PARAM_INT);
    $stmt_first_boot->execute();

    // Opzionale: Restituisci un messaggio di successo in formato JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Aggiornamento completato con successo']);
    
} catch (PDOException $e) {
    // Gestione degli errori
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Errore: ' . $e->getMessage()]);
}
?>