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
    $dati_lanci = $db->get("lanci", null, "
    lanci.ID AS ID,
    lanci.lancio AS lancio,
    lanci.data AS data,
    lanci.linea AS linea,
    lanci.id_modello AS id_modello,
    lanci.id_variante AS id_variante,
    lanci.paia AS paia,
    lanci.id_lab AS id_lab,
    lanci.stato AS stato,
    lanci.taglio AS taglio,
    lanci.preparazione AS preparazione,
    lanci.orlatura AS orlatura,
    lanci.spedizione AS spedizione,
    lanci.avanzamento AS avanzamento,
    lanci.note AS note,
    basi_modelli.path_to_image AS path_to_image,
    basi_modelli.codice AS codice,
    basi_modelli.descrizione AS descrizione,
    basi_modelli.qta_varianti AS qta_varianti,
    var_modelli.cod_variante AS cod_variante,
    var_modelli.desc_variante AS desc_variante,
    var_modelli.nome_completo AS nome_completo,
    var_modelli.path_diba AS path_diba,
    var_modelli.path_pic AS path_pic
");

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
        <div class="well text-center filter-form">
            <h2 class="page-header page-action-links text-left">
                <div class="row"
                    style="background-color:#ededed;border:solid 1pt #ededed;padding:10px;margin:auto;border-radius:10px;">
                    <div class="col-md-3">
                        LANCIO # <b>
                            <?php echo $lancio ?>
                        </b>
                    </div>
                    <div class="col-md-4">
                        STATO:
                        <?php if ($dati_lanci[0]['stato'] === 'IN ATTESA'): ?>
                            <b><span class="stato attesa">
                                    <?php echo htmlspecialchars($dati_lanci[0]['stato']); ?>
                                </span></b>
                        <?php elseif ($dati_lanci[0]['stato'] === 'LANCIATO'): ?>
                            <b><span class="stato lanciato">
                                    <?php echo htmlspecialchars($dati_lanci[0]['stato']); ?>
                                </span></b>
                        <?php else: ?>
                            <b><span class="stato">
                                    <?php echo htmlspecialchars($dati_lanci[0]['stato']); ?>
                                </span></b>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-5">
                        LABORATORIO:
                        <?php
                        if ($dati_lanci[0]['id_lab'] !== NULL) {
                            $db = getDbInstance();
                            $laboratorio = $db->where('ID', $dati_lanci[0]['id_lab'])->getOne('laboratori');
                            if ($laboratorio) {
                                echo '<span style="color:#0080ff; font-weight:bold;">' . htmlspecialchars($laboratorio['Nome']) . '</span>';
                            }
                        } else {
                            echo '<span class="attesa">DA ASSEGNARE</span>';
                        }
                        ?>
                    </div>

                </div>
            </h2>

        </div>
        <hr>
        <!-- Flash messages -->
        <?php include(BASE_PATH . "/includes/flash_messages.php"); ?>

        <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
            <?php
            //Include the common form for add and edit  
            require_once("forms/open_lancio_form.php");
            ?>
        </form>
    </div>

    <?php
    include_once BASE_PATH . '/includes/footer.php';
}
?>