<?php
session_start();
require_once '../config/config.php';


$lancio_id = (int)$_GET['id'];

try {
    $pdo = getDbInstance();
    
    // Carica dati del lancio
    $stmt = $pdo->prepare("
        SELECT l.*, lab.nome_laboratorio
        FROM scm_lanci l
        LEFT JOIN scm_laboratori lab ON l.laboratorio_id = lab.id
        WHERE l.id = ? AND l.laboratorio_id = ?
    ");
    $stmt->execute([$lancio_id, $laboratorio_id]);
    $lancio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lancio) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Lancio non trovato']);
        exit;
    }
    
    // Carica articoli del lancio
    $stmt = $pdo->prepare("
        SELECT * FROM scm_articoli_lancio 
        WHERE lancio_id = ? 
        ORDER BY ordine_articolo
    ");
    $stmt->execute([$lancio_id]);
    $articoli = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Restituisci i dati in formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'lancio' => $lancio,
        'articoli' => $articoli
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico')
    ]);
}
?>