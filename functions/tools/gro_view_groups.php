<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
include BASE_PATH . '/includes/header-nomenu.php';
// Include manualmente PhpSpreadsheet
$db = getDbInstance();
// Ottieni i gruppi distinti
$gruppi = $db->groupBy('Gruppo')->get('temp_dati_gruppi', null, ['Gruppo']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.11/cropper.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.11/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<style>
    /* Imposta il massimo della larghezza e dell'altezza per l'elemento Cropper */
    .modal-lg .cropper-container {
        max-width: 100%;
        max-height: calc(100vh - 200px);
        /* Altezza massima del modale - margine superiore e inferiore */
    }
</style>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 class="page-header page-action-links text-left">Visualizza Gruppi</h1>
                <div>
                    <!-- Pulsante con icona di bidone -->
                    <!-- Pulsante con icona di esporta -->
                    <button style="font-size:30pt;" class="btn btn-success" onclick="apriGroupExport()">
                        <i class="fad fa-file-pdf"></i> Esporta PDF
                    </button>

                    <script>
                        function apriGroupExport() {
                            window.location.href = 'gro_group_export.php';
                        }
                    </script>
                </div>
            </div>
            <hr>
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h3>Aggiungi un'immagine:</h3>

                    <form action="#" method="post" enctype="multipart/form-data" id="uploadForm">
                        <input type="file" name="fileToUpload" class="btn btn-warning" id="fileToUpload"
                            onchange="showImagePreview(this)">
                    </form>

                    <button style="visibility: hidden; pointer-events: none;" class="btn btn-primary"
                        id="uploadCroppedImage">Conferma</button>
                </div>

                <div class="col-md-3">
                    <p id="uploadStatusIcon" class="text-danger" style="display: none; font-size: 20pt;"><b>NON
                            CARICATA</b></p>
                    <p id="uploadSuccessIcon" class="text-success" style="display: none; font-size: 20pt;"><b>OK</b></p>
                </div>
            </div>

            <?php foreach ($gruppi as $index => $gruppo): ?>
                <?php
                // Ottieni le righe del gruppo corrente
                $db->where('Gruppo', $gruppo['Gruppo']);
                $righeGruppo = $db->get('temp_dati_gruppi');
                ?>
                <div class="accordion" id="accordion<?php echo $gruppo['Gruppo']; ?>">
                    <div class="card mb-3">
                        <div class="card-header bg-light" id="heading<?php echo $gruppo['Gruppo']; ?>">
                            <h2 class="mb-0">
                                <button style="width:100%;text-align:left;" class="btn btn-link" type="button"
                                    data-toggle="collapse" data-target="#collapse<?php echo $gruppo['Gruppo']; ?>"
                                    aria-expanded="true" aria-controls="collapse<?php echo $gruppo['Gruppo']; ?>">
                                    <span class="text-primary h2">
                                        <?php echo "#" . $righeGruppo[0]['lancio'] . "-" . $gruppo['Gruppo'] . " / " .
                                            $totaleQta = array_sum(array_column($righeGruppo, 'qta')) . " PA";
                                        ?>
                                    </span>
                                    <i class="float-right fas fa-chevron-down"></i>
                                </button>
                            </h2>
                        </div>

                        <div id="collapse<?php echo $gruppo['Gruppo']; ?>" class="collapse"
                            aria-labelledby="heading<?php echo $gruppo['Gruppo']; ?>"
                            data-parent="#accordion<?php echo $gruppo['Gruppo']; ?>">
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr style="background-color:black; color:white;">
                                            <th>Cartellino</th>
                                            <th>Commessa</th>
                                            <th>P01</th>
                                            <th>P02</th>
                                            <th>P03</th>
                                            <th>P04</th>
                                            <th>P05</th>
                                            <th>P06</th>
                                            <th>P07</th>
                                            <th>P08</th>
                                            <th>P09</th>
                                            <th>P10</th>
                                            <th>P11</th>
                                            <th>P12</th>
                                            <th>P13</th>
                                            <th>P14</th>
                                            <th>P15</th>
                                            <th>P16</th>
                                            <th>P17</th>
                                            <th>P18</th>
                                            <th>P19</th>
                                            <th>P20</th>
                                            <th>Qta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($righeGruppo as $riga): ?>
                                            <tr>
                                                <td>
                                                    <?php echo $riga['cartellino']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $riga['commessa']; ?>
                                                </td>
                                                <?php for ($i = 1; $i <= 20; $i++): ?>
                                                    <td>
                                                        <?php
                                                        $campo = 'P' . sprintf('%02d', $i);
                                                        echo ($riga[$campo] != 0) ? $riga[$campo] : '';
                                                        ?>
                                                    </td>
                                                <?php endfor; ?>
                                                <td>
                                                    <?php echo $riga['qta']; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td colspan="22"><strong>Totale Qta:</strong></td>
                                            <td><b>
                                                    <?php
                                                    $totaleQta = array_sum(array_column($righeGruppo, 'qta'));
                                                    echo $totaleQta;
                                                    ?>
                                                </b></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($index < count($gruppi) - 1): ?>
                    <hr>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="modal fade" id="imageCropModal" tabindex="-1" role="dialog" aria-labelledby="imageCropModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="imageCropModalLabel">Ritaglia l'immagine</h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <img id="imageToCrop" src="#" alt="Immagine da ritagliare">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i style="font-size:20pt;"
                                class="fas fa-times-circle"></i></button>
                        <button class="btn btn-success" id="cropImage"><i style="font-size:20pt;"
                                class="fas fa-check"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>
    <!-- //Main container -->
    <?php include BASE_PATH . '/includes/footer.php'; ?>
    <script>
        $(document).ready(function () {
            $('#uploadStatusIcon').show(); // Mostra l'icona della "X" rossa
            var $imageToCrop = $('#imageToCrop');
            var cropper;

            $('#imageCropModal').on('shown.bs.modal', function () {
                cropper = new Cropper($imageToCrop[0], {
                    aspectRatio: NaN,
                    viewMode: 1,
                });
            });

            $('#imageCropModal').on('hidden.bs.modal', function () {
                cropper.destroy();
                cropper = null;
            });

            $('#cropImage').click(function () {
                if (cropper) {
                    var croppedDataUrl = cropper.getCroppedCanvas().toDataURL();

                    $.ajax({
                        url: 'gro_upload.php',
                        method: 'POST',
                        data: { image: croppedDataUrl },
                        success: function (data) {
                            alert(data);
                            $('#uploadStatusIcon').hide();
                            $('#uploadSuccessIcon').show();
                        },
                        error: function () {
                            alert('Errore durante il salvataggio dell\'immagine.');
                            $('#uploadStatusIcon').hide();
                            $('#uploadSuccessIcon').hide();
                        }
                    });

                    $imageToCrop.attr('src', croppedDataUrl);
                    $('#imageCropModal').modal('hide');
                    $('#uploadStatusIcon').show();
                    $('#uploadSuccessIcon').hide();
                } else {
                    console.error('Cropper non è inizializzato correttamente.');
                }
            });
        });

        function showImagePreview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#imageToCrop').attr('src', e.target.result);
                    $('#imageCropModal').modal('show');
                    $('#uploadStatusIcon').show(); // Mostra l'icona della "X" rossa
                    $('#uploadSuccessIcon').hide();
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

    </script>
</div>