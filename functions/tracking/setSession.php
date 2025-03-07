<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Decodifica i dati JSON ricevuti
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['message'])) {
        $_SESSION["success"] = $data['message'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Parametro "message" mancante']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
}
?>