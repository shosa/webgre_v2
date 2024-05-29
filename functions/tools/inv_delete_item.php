<?php
session_start();

require_once '../../config/config.php';
require_once BASE_PATH .'/includes/auth_validate.php';
$del_id = filter_input(INPUT_POST, 'del_id');
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {


    $item_id = $del_id;

    $db = getDbInstance();
    $db->where('ID', $item_id);
    $status = $db->delete('inv_list');

    if ($status) {
        $_SESSION['info'] = "Riga eliminata!";
        header('location: inv_all_items.php');
        exit;
    } else {
        $_SESSION['failure'] = "Impossibile eliminare!";
        header('location: inv_all_items.php');
        exit;

    }

}