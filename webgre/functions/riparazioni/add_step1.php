<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';


$edit = false;

require_once BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Nuova Riparazione</h2>
        </div>

    </div>
    <hr>

    <?php include_once('forms/new_step1_form.php'); ?>

</div>




<?php include_once BASE_PATH . '/includes/footer.php'; ?>