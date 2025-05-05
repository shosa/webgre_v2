<?php
session_start();
require_once '../../config/config.php';

require_once BASE_PATH . '/vendor/autoload.php';

$db = getDbInstance();

$progressivo = $_POST['progressivo'] ?? null;
$azione = $_POST['azione'] ?? null;

switch ($azione) {
    case 'get':
        getAutorizzazione($progressivo, $db);
        break;
    case 'save':
        $data = json_decode($_POST['data'], true);
        saveAutorizzazione($progressivo, $data, $db);
        break;
    case 'reset':
        resetAutorizzazione($progressivo, $db);
        break;
    default:
        echo 'Azione non valida';
        break;
}

function getAutorizzazione($progressivo, $db)
{
    $sql = "SELECT autorizzazione FROM exp_piede_documenti WHERE id_documento = :progressivo";
    $stmt = $db->prepare($sql);
    $stmt->execute([':progressivo' => $progressivo]);
    $data = $stmt->fetch();

    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Dati non trovati']);
    }
}

function saveAutorizzazione($progressivo, $data, $db)
{
    $fields = [];
    foreach ($data as $key => $value) {
        $fields[] = "$key = :$key";
    }

    $sql = "UPDATE exp_piede_documenti SET " . implode(', ', $fields) . " WHERE id_documento = :progressivo";
    $stmt = $db->prepare($sql);

    $params = $data;
    $params['progressivo'] = $progressivo;

    if ($stmt->execute($params)) {
        echo 'Dati salvati con successo';
    } else {
        echo 'Salvataggio fallito';
    }
}

function resetAutorizzazione($progressivo, $db)
{
    $sql = "UPDATE exp_piede_documenti SET autorizzazione = '' WHERE id_documento = :progressivo";
    $stmt = $db->prepare($sql);
    if ($stmt->execute(['progressivo' => $progressivo])) {
        echo 'Dati ripristinati con successo';
    } else {
        echo 'Ripristino fallito';
    }
}
?>
