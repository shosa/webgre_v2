<?php
require_once '../../config/config.php';

$db = getDbInstance();

$barcode = filter_input(INPUT_GET, 'barcode', FILTER_UNSAFE_RAW);

if ($barcode) {
    $db->where('code', $barcode);
    $barcodeData = $db->getOne('cq_barcodes', 'test');

    if ($barcodeData) {
        echo json_encode(['success' => true, 'test' => $barcodeData['test']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Codice non trovato']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nessun codice fornito']);
}
?>
