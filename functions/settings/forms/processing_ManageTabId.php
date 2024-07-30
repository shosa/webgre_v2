<?php
session_start();
require_once '../../../config/config.php';

header('Content-Type: application/json');

$conn = getDbInstance();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['value'];

    try {
        $sql = "UPDATE tabid SET ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $response['success'] = true;
        $response['message'] = 'ID aggiornato con successo!';
    } catch (Exception $e) {
        $response['message'] = 'Errore durante l\'aggiornamento dell\'ID: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}
?>
