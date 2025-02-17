<?php
require_once '../../config/config.php';
$db = getDbInstance();

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_category':
            $sigla = $_POST['sigla'];
            $descrizione = $_POST['descrizione'];

            $query = "INSERT INTO att_category (sigla, descrizione) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$sigla, $descrizione]);

            header('Location: configure');
            break;

        case 'edit_category':
            $id = $_POST['id'];
            $sigla = $_POST['sigla'];
            $descrizione = $_POST['descrizione'];

            $query = "UPDATE att_category SET sigla = ?, descrizione = ? WHERE ID = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$sigla, $descrizione, $id]);

            header('Location: configure');
            break;

        case 'delete_category':
            $id = $_GET['id'];

            $query = "DELETE FROM att_category WHERE ID = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);

            header('Location: configure');
            break;

        default:
            header('Location: configure');
            break;
    }
} else {
    header('Location: configure');
}
