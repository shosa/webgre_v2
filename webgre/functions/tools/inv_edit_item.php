<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
include BASE_PATH . '/includes/header-nomenu.php';

// Sanitize if you want
$inventario_id = filter_input(INPUT_GET, 'inventario_id', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation', FILTER_UNSAFE_RAW);
($operation == 'edit') ? $edit = true : $edit = false;
$db = getDbInstance();

//Handle update request. As the form's action attribute is set to the same script, but 'POST' method, 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Get customer id form query string parameter.
    $inventario_id = filter_input(INPUT_GET, 'inventario_id', FILTER_UNSAFE_RAW);

    //Get input data
    $data_to_update = filter_input_array(INPUT_POST);
    $db = getDbInstance();
    $db->where('ID', $inventario_id);
    $stat = $db->update('inv_list', $data_to_update);

    if ($stat) {
        $_SESSION['success'] = "Riga aggiornata correttamente!";
        //Redirect to the listing page,
        header('location: inv_all_items.php');
        //Important! Don't execute the rest put the exit/die. 
        exit();
    }
}


//If edit variable is set, we are performing the update operation.
if ($edit) {
    $db->where('ID', $inventario_id);
    //Get data to pre-populate the form.
    $inventario = $db->getOne("inv_list");
}
?>


<?php
include_once BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">

   
    <!-- Flash messages -->
    <?php
    include(BASE_PATH . '/includes/flash_messages.php')
        ?>

    <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">

        <?php
        //Include the common form for add and edit  
        require_once('forms/edit_item_form.php');
        ?>
    </form>
</div>