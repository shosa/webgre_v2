<?php
session_start();
require_once '../../../config/config.php';
header('Content-Type: application/json');
$conn = getDbInstance();
$response = ['success' => false, 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $production_senderEmail = $_POST['production_senderEmail'];
    $production_senderPassword = $_POST['production_senderPassword'];
    $production_senderSMTP = $_POST['production_senderSMTP'];
    $production_senderPORT = $_POST['production_senderPORT'];
    $production_recipients = $_POST['production_recipients'];
    $settings = [
        'production_senderEmail' => $production_senderEmail,
        'production_senderPassword' => $production_senderPassword,
        'production_senderSMTP' => $production_senderSMTP,
        'production_senderPORT' => $production_senderPORT,
        'production_recipients' => $production_recipients
    ];
    try {
        foreach ($settings as $item => $value) {
            $sql = "UPDATE settings SET value = :value WHERE item = :item";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':item', $item);
            $stmt->execute();
        }
        $response['success'] = true;
        $response['message'] = 'Impostazioni aggiornate con successo!';
    } catch (Exception $e) {
        $response['message'] = 'Errore durante l\'aggiornamento delle impostazioni: ' . $e->getMessage();
    }
    echo json_encode($response);
    exit();
}
