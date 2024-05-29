<?php
session_start();

require_once '../../config/config.php';
require_once BASE_PATH .'/includes/auth_validate.php';
$del_id = filter_input(INPUT_POST, 'del_id');
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {


    $riparazione_id = $del_id;

    $db = getDbInstance();
    $db->where('IDRIP', $riparazione_id);
    $status = $db->delete('riparazioni');

    if ($status) {
        $_SESSION['info'] = "Riparazione cancellata!";
        header('location: riparazioni.php');
        exit;
    } else {
        $_SESSION['failure'] = "Impossibile eliminare!";
        header('location: riparazioni.php');
        exit;

    }

}