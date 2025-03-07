<?php
session_start();
require_once '../../../config/config.php';
header('Content-Type: application/json');
$conn = getDbInstance();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $type = $_POST['type'];
    $message = $_POST['message'];
    $link = isset($_POST['link']) ? $_POST['link'] : '';

    try {
        // Prepara la query SQL per inserire la notifica nel database
        $query = "INSERT INTO notifications (user_id, type, message, link, is_read) VALUES (:user_id, :type, :message, :link, 0)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':link', $link);
        $stmt->execute();

        // Ottieni l'ID della notifica appena inserita
        $notificationId = $conn->lastInsertId();

        $response['success'] = true;
        $response['message'] = 'Notifica inviata con successo!';
        $response['notification_id'] = $notificationId;
    } catch (PDOException $e) {
        $response['message'] = 'Errore durante l\'invio della notifica: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}
