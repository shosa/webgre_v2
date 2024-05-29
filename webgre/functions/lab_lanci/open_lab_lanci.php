<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$operation = filter_input(INPUT_GET, 'operation', FILTER_UNSAFE_RAW);
($operation == 'edit') ? $edit = true : $edit = false;
($operation == 'plus') ? $plus = true : $plus = false;


// Sanitize if you want
$lancio = filter_input(INPUT_GET, 'lancio', FILTER_VALIDATE_INT);
$db = getDbInstance();

//Handle update request. As the form's action attribute is set to the same script, but 'POST' method, 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


}
if ($plus) {
    $db->where('lancio', $lancio);
    $db->join('basi_modelli', 'lanci.id_modello = basi_modelli.ID', 'LEFT');
    $db->join('var_modelli', 'lanci.id_variante = var_modelli.ID', 'LEFT');
    $db->join('linee', 'lanci.linea = linee.sigla', 'LEFT');
    $dati_lanci = $db->get("lanci", null, 'lanci.data, lanci.taglio, lanci.preparazione, lanci.orlatura, lanci.spedizione, lanci.lancio, var_modelli.nome_completo,lanci.ID, lanci.paia, lanci.avanzamento, lanci.note, linee.descrizione, basi_modelli.path_to_image');

    include_once BASE_PATH . '/includes/header.php';
    ?>
    <style>
        .attesa {
            color: red;
            font-weight: bold;
            animation: blink 1s linear infinite;
        }

        .lanciato {
            color: #1bcc38;
            font-weight: bold;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
    <div id="page-wrapper">
        <div class="row">
            <h2 class="page-header">
                <div class="row"
                    style="background-color:#ededed;border:solid 1pt #ededed;padding:10px;margin:10px;border-radius:10px;">
                    <div class="col-md-3">
                        LANCIO # <b>
                            <?php echo $lancio ?>
                        </b>
                    </div>
                    <div class="col-md-6">
                        LINEA:
                        <b><span>
                                <?php echo htmlspecialchars($dati_lanci[0]['descrizione']); ?>
                            </span></b>

                    </div>
                    <div class="col-md-3">
                        DEL:
                        <b><span>
                                <?php echo htmlspecialchars($dati_lanci[0]['data']); ?>
                            </span></b>

                    </div>
                </div>
            </h2>
        </div>
        <!-- Flash messages -->
        <?php include(BASE_PATH . "/includes/flash_messages.php"); ?>


        <?php
        //Include the common form for add and edit  
        require_once("forms/open_lab_lancio_form.php");
        ?>

    </div>

    <?php
    include_once BASE_PATH . '/includes/footer.php';
}
?>