<?php
session_start();
require_once '../../config/config.php';

require_once BASE_PATH . '/vendor/autoload.php';

$db = getDbInstance();

$progressivo = $_POST['progressivo'] ?? null;
$azione = $_POST['azione'] ?? null;

switch ($azione) {
    case 'get':
        getCommento($progressivo, $db);
        break;
    case 'save':
        $data = json_decode($_POST['data'], true);
        saveCommento($progressivo, $data, $db);
        break;
    case 'reset':
        resetCommento($progressivo, $db);
        break;
    default:
        echo 'Azione non valida';
        break;
}

function getCommento($progressivo, $db)
{
    $sql = "SELECT commento FROM exp_documenti WHERE id = :progressivo";
    $stmt = $db->prepare($sql);
    $stmt->execute([':progressivo' => $progressivo]);
    $data = $stmt->fetch();

    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Dati non trovati']);
    }
}

function saveCommento($progressivo, $data, $db)
{
    $fields = [];
    foreach ($data as $key => $value) {
        $fields[] = "$key = :$key";
    }

    $sql = "UPDATE exp_documenti SET " . implode(', ', $fields) . " WHERE id = :progressivo";
    $stmt = $db->prepare($sql);

    $params = $data;
    $params['progressivo'] = $progressivo;

    if ($stmt->execute($params)) {
        echo 'Dati salvati con successo';
    } else {
        echo 'Salvataggio fallito';
    }
}

function resetCommento($progressivo, $db)
{
    $sql = "UPDATE exp_documenti SET commento = '' WHERE id = :progressivo";
    $stmt = $db->prepare($sql);
    if ($stmt->execute(['progressivo' => $progressivo])) {
        echo 'Dati ripristinati con successo';
    } else {
        echo 'Ripristino fallito';
    }
}
?>
