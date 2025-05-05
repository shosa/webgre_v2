<?php
require_once '../../config/config.php';

$progressivo = filter_input(INPUT_POST, 'progressivo', FILTER_VALIDATE_INT);

try {
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT DISTINCT voce_doganale FROM exp_dati_articoli WHERE id_documento = :id_documento");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    
    $uniqueDoganale = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['voce_doganale'])) {
            $uniqueDoganale[] = $row['voce_doganale'];
        }
    }
    
    echo json_encode($uniqueDoganale);
    
} catch (PDOException $e) {
    error_log("Errore nel recupero delle voci doganali: " . $e->getMessage());
    echo json_encode([]);
}
?>