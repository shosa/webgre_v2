<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'super') {
    // show permission denied message
    echo 'Permessi insufficenti per visualizzare questa sezione!';
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_to_store = filter_input_array(INPUT_POST);
    $db = getDbInstance();
    //Check whether the user name already exists ; 
    $db->where('user_name', $data_to_store['user_name']);
    $db->get('utenti');

    if ($db->count >= 1) {
        $_SESSION['failure'] = "Utente già esistente!";
        header('location: add_admin.php');
        exit();
    }

    //Encrypt password
    $data_to_store['password'] = password_hash($data_to_store['password'], PASSWORD_DEFAULT);
    //reset db instance
    $db = getDbInstance();
    $last_id = $db->insert('utenti', $data_to_store);
    if ($last_id) {

        $_SESSION['success'] = "Utente aggiunto con successo!";
        header('location: admin_users.php');
        exit();
    }

}

$edit = false;


require_once BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Aggiunta utente</h2>
        </div>
    </div>
    <hr>
    <?php
    include_once(BASE_PATH . '/includes/flash_messages.php');
    ?>
    <form class="well form-horizontal" action=" " method="post" id="contact_form" enctype="multipart/form-data">
        <?php include_once 'forms/admin_users_form.php'; ?>
    </form>
</div>




<?php include_once BASE_PATH . '/includes/footer.php'; ?>