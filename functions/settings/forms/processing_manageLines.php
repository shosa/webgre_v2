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
            $sql = "UPDATE linee SET $field = :value WHERE ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Linea aggiornata con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'aggiornamento della linea: ' . $e->getMessage();
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];

        try {
            $sql = "DELETE FROM linee WHERE ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Linea eliminata con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'eliminazione della linea: ' . $e->getMessage();
        }
    } elseif ($action == 'add') {
        $sigla = $_POST['sigla'];
        $descrizione = $_POST['descrizione'];

        try {
            $sql = "INSERT INTO linee (sigla, descrizione) VALUES (:sigla, :descrizione)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':sigla', $sigla);
            $stmt->bindParam(':descrizione', $descrizione);
            $stmt->execute();
            $response['success'] = true;
            $response['message'] = 'Linea aggiunta con successo!';
        } catch (Exception $e) {
            $response['message'] = 'Errore durante l\'aggiunta della linea: ' . $e->getMessage();
        }
    }

    echo json_encode($response);
    exit();
}
?>