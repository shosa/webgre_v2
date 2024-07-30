<?php
session_start();
require_once '../../../config/config.php';

header('Content-Type: application/json');

$conn = getDbInstance();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'update') {
        $id = $_POST['id'];
        $field = $_POST['field'];
        $value = $_POST['value'];

        try {
            $sql = "UPDATE laboratori SET $field = :value WHERE ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Laboratorio aggiornato con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'aggiornamento del laboratorio: ' . $e->getMessage();
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];

        try {
            $sql = "DELETE FROM laboratori WHERE ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Laboratorio eliminato con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'eliminazione del laboratorio: ' . $e->getMessage();
        }
    } elseif ($action == 'add') {
        $nome = $_POST['nome'];

        try {
            $sql = "INSERT INTO laboratori (Nome) VALUES (:nome)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Laboratorio aggiunto con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'aggiunta del laboratorio: ' . $e->getMessage();
        }
    }

    echo json_encode($response);
    exit();
}
?>
