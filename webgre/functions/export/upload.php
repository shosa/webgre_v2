<?php
$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['file'];

    if ($file['error'] === 0) {
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];

        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Errore durante il caricamento del file']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Si è verificato un errore durante il caricamento del file']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
}
?>