<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';


// Sanitize if you want
$id_modello = filter_input(INPUT_GET, 'id_modello', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation', FILTER_UNSAFE_RAW);
($operation == 'edit') ? $edit = true : $edit = false;
($operation == 'plus') ? $plus = true : $plus = false;
$db = getDbInstance();

//Handle update request. As the form's action attribute is set to the same script, but 'POST' method, 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($edit) {
        //Get customer id form query string parameter.
        $id_modello = filter_input(INPUT_GET, 'id_modello', FILTER_UNSAFE_RAW);

        //Get input data
        $data_to_update = filter_input_array(INPUT_POST);
        $db = getDbInstance();
        $db->where('ID', $id_modello);
        $stat = $db->update('basi_modelli', $data_to_update);

        if ($stat) {
            $_SESSION['success'] = "Modello aggiornato correttamente!";
            //Redirect to the listing page,
            header('location: modelli.php');
            //Important! Don't execute the rest put the exit/die. 
            exit();
        }
    }

}


//If edit variable is set, we are performing the update operation.
if ($edit) {
    $db->where('ID', $id_modello);
    //Get data to pre-populate the form.
    $modello = $db->getOne("basi_modelli");
    include_once BASE_PATH . '/includes/header.php';
    ?>
    <div id="page-wrapper">
        <div class="row">
            <h2 class="page-header page-action-links text-left">Modifica Modello</h2>
        </div> <hr>
        <!-- Flash messages -->
        <?php include(BASE_PATH . "/includes/flash_messages.php"); ?>

        <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
            <?php
            //Include the common form for add and edit  
            require_once("forms/edit_modelli_form.php");
            ?>
        </form>
    </div>
    <hr>
    <?php
    include_once BASE_PATH . '/includes/footer.php';
}
if ($plus) {
    $db->where('ID', $id_modello);
    //Get data to pre-populate the form.
    $modello = $db->getOne("basi_modelli");
    $varianti = $db->where('id_modello', $id_modello)->get("var_modelli");
    include_once BASE_PATH . '/includes/header.php';
    ?>
    <div id="page-wrapper">
        <div class="row">
            <h2 class="page-header page-action-links text-left">Gestisci Varianti
                <b>
                    <?php echo $modello['descrizione'] ?>
                </b>
            </h2>
        </div> <hr>
        <!-- Flash messages -->
        <?php include(BASE_PATH . "/includes/flash_messages.php"); ?>

        <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
            <?php
            //Include the common form for add and edit  
            require_once("forms/plus_modelli_form.php");
            ?>
        </form>
    </div>
   
    <?php
    include_once BASE_PATH . '/includes/footer.php';
}
?>