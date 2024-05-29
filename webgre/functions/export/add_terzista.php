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
    $db->where('ragione_sociale', $data_to_store['ragione_sociale']);
    $db->get('exp_terzisti');

    if ($db->count >= 1) {
        $_SESSION['failure'] = "Terzista già esistente!";
        header('location: add_terzista.php');
        exit();
    }

    //Encrypt password
    //reset db instance
    $db = getDbInstance();
    $last_id = $db->insert('exp_terzisti', $data_to_store);
    if ($last_id) {
        $_SESSION['success'] = "Terzista aggiunto con successo!";
        header('location: terzisti.php');
        exit();
    }
}

$edit = false;
require_once BASE_PATH . '/includes/header.php';
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#ragione_sociale, #indirizzo_1, #indirizzo_2, #indirizzo_3, #nazione').on('input', function () {
            var ragione_sociale = $('#ragione_sociale').val();
            var indirizzo_1 = $('#indirizzo_1').val();
            var indirizzo_2 = $('#indirizzo_2').val();
            var indirizzo_3 = $('#indirizzo_3').val();
            var nazione = $('#nazione').val();

            $('#preview_ragione_sociale').text(ragione_sociale);
            $('#preview_indirizzi').html('<p>' + indirizzo_1 + '</p><p>' + indirizzo_2 + '</p><p>' + indirizzo_3 + '</p><p>' + nazione + '</p>');
        });
    });
</script>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Aggiunta nuovo Terzista</h2>
        </div>
    </div>
    <hr>
    <?php include_once (BASE_PATH . '/includes/flash_messages.php'); ?>
    <div class="row">
        <div class="col-md-7">
            <form class="well form-horizontal" action="" method="post" id="contact_form" enctype="multipart/form-data">
                <!-- Form Inputs -->
                <!-- Ragione Sociale -->
                <div class="form-group">
                    <label class="col-md-12 control-label">Ragione Sociale</label>
                    <div class="col-md-12 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="ragione_sociale" id="ragione_sociale" autocomplete="off"
                                class="form-control">
                        </div>
                    </div>
                </div>
                <!-- Indirizzi -->
                <div class="form-group">
                    <label class="col-md-12 control-label">Indirizzo Riga 1</label>
                    <div class="col-md-12 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="indirizzo_1" id="indirizzo_1" autocomplete="off"
                                class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-12 control-label">Indirizzo Riga 2</label>
                    <div class="col-md-12 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="indirizzo_2" id="indirizzo_2" autocomplete="off"
                                class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-12 control-label">Indirizzo Riga 3</label>
                    <div class="col-md-12 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="indirizzo_3" id="indirizzo_3" autocomplete="off"
                                class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-12 control-label">Nazione</label>
                    <div class="col-md-12 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="nazione" id="nazione" autocomplete="off" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-12 control-label">Consegna</label>
                    <div class="col-md-12 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="consegna" id="consegna" autocomplete="off" class="form-control">
                        </div>
                    </div>
                </div>
                <!-- Button -->
                <div class="form-group">
                    <label class="col-md-12 control-label"></label>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-warning">SALVA <span class="fa fa-send"></span></button>
                    </div>
                </div>
            </form>
        </div>
        <!-- Preview -->
        <div class="col-md-5">
            <h4>Anteprima:</h4>
            <h2 id="preview_ragione_sociale"></h2>
            <div id="preview_indirizzi"></div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/includes/footer.php'; ?>