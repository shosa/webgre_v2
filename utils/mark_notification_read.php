<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati dalla richiesta
    $data = json_decode(file_get_contents('php://input'), true);
    $notificationId = $data['id'];

    // Connessione al database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Aggiorna la notifica come letta
    $query = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $notificationId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
    $success = $stmt->execute();

    // Risposta JSON
    echo json_encode(['success' => $success]);
}
?>