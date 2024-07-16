<?php
require_once '../../config/config.php';

$barcode = filter_input(INPUT_GET, 'barcode', FILTER_UNSAFE_RAW);

if ($barcode) {
    try {
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT test FROM cq_barcodes WHERE code = :barcode";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
        $stmt->execute();
        $barcodeData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($barcodeData) {
            echo json_encode(['success' => true, 'test' => $barcodeData['test']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Codice non trovato']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Errore nel database: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nessun codice fornito']);
}
?>