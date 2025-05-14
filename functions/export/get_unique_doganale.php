<?php
require_once '../../config/config.php';

$progressivo = filter_input(INPUT_POST, 'progressivo', FILTER_VALIDATE_INT);

try {
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query corretta: aggiunto um al GROUP BY
    $stmt = $conn->prepare("
        SELECT 
            voce_doganale, 
            SUM(qta_reale) as totale_quantita,
            um
        FROM 
            exp_dati_articoli 
        WHERE 
            id_documento = :id_documento 
        GROUP BY 
            voce_doganale, um  
        HAVING 
            voce_doganale IS NOT NULL AND voce_doganale != ''
    ");
    
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[] = [
            'voce_doganale' => $row['voce_doganale'],
            'totale_quantita' => $row['totale_quantita'],
            'um' => $row['um']
        ];
    }
    
    echo json_encode($result);
    
} catch (PDOException $e) {
    error_log("Errore nel recupero delle voci doganali: " . $e->getMessage());
    echo json_encode([]);
}
?>