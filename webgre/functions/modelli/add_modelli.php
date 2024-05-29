<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$db = getDbInstance();

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] == 'utente') {
    // show permission denied message
    echo 'Permessi insufficenti per visualizzare questa sezione!';
    exit();
}

$lastModello = $db->orderBy('ID', 'DESC')->getOne('basi_modelli', 'ID');
$newID = ($lastModello['ID'] ?? 0) + 1;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_to_store = filter_input_array(INPUT_POST);

    //Check whether the user name already exists ; 
    $db->where('codice', $data_to_store['codice']);
    $db->get('basi_modelli');

    if ($db->count >= 1) {
        $_SESSION['failure'] = "Codice modello già presente!";
        header('location: modelli.php');
        exit();
    }

    //Encrypt password

    //reset db instance
    $db = getDbInstance();
    $last_id = $db->insert('basi_modelli', $data_to_store);

    if ($last_id) {
        $_SESSION['success'] = "Modello aggiunto con successo!";
        header('location: modelli.php');
        exit();
    }

}

$edit = false;


require_once BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">Nuovo Modello</h2>
        </div>
    </div>
    <?php
    include_once(BASE_PATH . '/includes/flash_messages.php');
    ?>
    <form class="well form-horizontal" action=" " method="post" id="contact_form" enctype="multipart/form-data">
        <?php include_once 'forms/new_modelli_form.php'; ?>
    </form>
</div>




<?php include_once BASE_PATH . '/includes/footer.php'; ?>