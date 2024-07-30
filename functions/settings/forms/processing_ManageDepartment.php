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
            $sql = "UPDATE reparti SET $field = :value WHERE ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Reparto aggiornato con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'aggiornamento del reparto: ' . $e->getMessage();
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];

        try {
            $sql = "DELETE FROM reparti WHERE ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Reparto eliminato con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'eliminazione del reparto: ' . $e->getMessage();
        }
    } elseif ($action == 'add') {
        $nome = $_POST['nome'];

        try {
            $sql = "INSERT INTO reparti (Nome) VALUES (:nome)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Reparto aggiunto con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'aggiunta del reparto: ' . $e->getMessage();
        }
    }

    echo json_encode($response);
    exit();
}
?>
