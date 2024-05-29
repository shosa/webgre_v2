<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$db = getDbInstance();
//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'super') {
    // show permission denied message
    echo 'Permessi insufficenti per visualizzare questa sezione!';
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_to_store = filter_input_array(INPUT_POST);
    //Check whether the user name already exists ; 
    $db->where('lab', $data_to_store['lab']);
    $db->get('lab_user');

    if ($db->count >= 1) {
        $db->where('user', $data_to_store['user']);
        $db->get('lab_user');
        if ($db->count >= 1) {
            $_SESSION['failure'] = "Associazione già esistente!";
            header('location: lab_user.php');
            exit();
        }
    }
    //reset db instance
    $db = getDbInstance();
    $last_id = $db->insert('lab_user', $data_to_store);
    if ($last_id) {

        $_SESSION['success'] = "Associazione creata!";
        header('location: lab_user.php');
        exit();
    }

}

require_once BASE_PATH . '/includes/header.php';

?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Aggiunta associazione Laboratorio - Utente</h2>
        </div>
    </div>
    <hr>
    <?php
    include_once(BASE_PATH . '/includes/flash_messages.php');
    ?>
    <form class="well form-horizontal" action=" " method="post" id="contact_form" enctype="multipart/form-data">
        <?php include_once 'forms/lab_user_form.php'; ?>
    </form>
</div>




<?php include_once BASE_PATH . '/includes/footer.php'; ?>