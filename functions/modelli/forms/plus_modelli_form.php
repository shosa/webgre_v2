<fieldset>
    <div class="form-group">
        <label for="ID">ID Modello</label>
        <input type="text" name="ID"
            value="<?php echo htmlspecialchars($plus ? $modello['ID'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ID"
            class="form-control" required="required" id="ID" readonly>
    </div>
    <div class="form-group">
        <label for="Codice">Codice Modello</label>
        <input type="text" name="codice"
            value="<?php echo htmlspecialchars($plus ? $modello['codice'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="Codice Modello" class="form-control" required="required" id="codice" readonly>
    </div>
    <div class="form-group">
        <label for="Descrizione">Descrizione</label>
        <input type="text" name="descrizione"
            value="<?php echo htmlspecialchars($plus ? $modello['descrizione'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="Descrizione" class="form-control" required="required" id="descrizione" readonly>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="15%">Codice Variante</th>
                    <th width="60%">Descrizione Variante</th>
                    <th width="10%">DiBa</th>
                    <th width="10%">Schema Taglio</th>
                    <th width="5%">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($varianti)) {

                    if (!empty($varianti)) {
                        foreach ($varianti as $variante) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($variante['cod_variante'], ENT_QUOTES, 'UTF-8') . '</td>';
                            echo '<td>' . htmlspecialchars($variante['desc_variante'], ENT_QUOTES, 'UTF-8') . '</td>';

                            // Verifica e mostra una spunta verde o una X rossa per DiBa
                            echo '<td>';
                            echo '<input style="font-size:8pt;" type="file" name="diba_file_' . $variante['ID'] . '">';
                            if (!empty($variante['path_diba'])) {
                                echo '<span class="label label-success">FILE PRESENTE</span>';
                            } else {
                                echo '<span class="label label-danger">NON CARICATO</span>';
                            }
                            echo '</td>';

                            // Verifica e mostra una spunta verde o una X rossa per Schema Taglio
                            echo '<td>';
                            echo '<input style="font-size:8pt;" type="file" name="schema_taglio_file_' . $variante['ID'] . '">';
                            if (!empty($variante['path_pic'])) {
                                echo '<span class="label label-success">FILE PRESENTE</span>';
                            } else {
                                echo '<span class="label label-danger">NON CARICATO</span>';
                            }
                            echo '</td>';

                            echo '<td>';
                            echo '<a href="#" data-toggle="modal" data-target="#confirm-delete-' . $variante['ID'] . '" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>';
                            echo '</td>';
                            echo '</tr>';

                            if (isset($_FILES['diba_file_' . $variante['ID']])) {
                                // Esegui il caricamento del file DiBa
                                $fileDiba = $_FILES['diba_file_' . $variante['ID']];
                                if (!empty($fileDiba['name'])) {
                                    // Verifica se un file è stato selezionato
                                    $uploadDirDiba = '../../src/diba/';
                                    $originalFileExtensionDiba = pathinfo($fileDiba['name'], PATHINFO_EXTENSION);
                                    $fileNameDiba = $modello['codice'] . $variante['cod_variante'] . '.' . $originalFileExtensionDiba;
                                    $uploadPathDiba = $uploadDirDiba . $fileNameDiba;
                                    move_uploaded_file($fileDiba['tmp_name'], $uploadPathDiba);
                                    $updatePathDiba = "UPDATE var_modelli SET path_diba = '" . $uploadPathDiba . "' WHERE ID = " . $variante['ID'];
                                    $result = $db->rawQuery($updatePathDiba);

                                }
                            }

                            if (isset($_FILES['schema_taglio_file_' . $variante['ID']])) {
                                // Esegui il caricamento del file Schema Taglio
                                $fileSchemaTaglio = $_FILES['schema_taglio_file_' . $variante['ID']];
                                if (!empty($fileSchemaTaglio['name'])) {
                                    // Verifica se un file è stato selezionato
                                    $uploadDirSchemaTaglio = '../../src/pics/';
                                    $originalFileExtensionSchemaTaglio = pathinfo($fileSchemaTaglio['name'], PATHINFO_EXTENSION);
                                    $fileNameSchemaTaglio = $modello['codice'] . $variante['cod_variante'] . '.' . $originalFileExtensionSchemaTaglio;
                                    $uploadPathSchemaTaglio = $uploadDirSchemaTaglio . $fileNameSchemaTaglio;
                                    move_uploaded_file($fileSchemaTaglio['tmp_name'], $uploadPathSchemaTaglio);
                                    $updatePathSchemaTaglio = "UPDATE var_modelli SET path_pic = '" . $uploadPathSchemaTaglio . "' WHERE ID = " . $variante['ID'];
                                    $result = $db->rawQuery($updatePathSchemaTaglio);

                                }
                            }
                        }
                    }
                } else {
                    echo 'Nessuna variante trovata.';
                }
                ?>
                <tr>
                    <td><input type="text" name="nuovo_codice_variante" class="form-control"
                            placeholder="Codice Variante"></td>
                    <td><input type="text" name="nuova_desc_variante" class="form-control"
                            placeholder="Descrizione Variante"></td>
                    <td></td>
                    <td></td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm" id="aggiungiVariante"><i class="fas fa-plus"></i></button>
                    </td>
                </tr>
            </tbody>

            <?php
            if (!empty($varianti)) {
                foreach ($varianti as $variante) {
                    echo '<div class="modal fade" id="confirm-delete-' . $variante['ID'] . '" role="dialog">
                <div class="modal-dialog">
                    <form action="delete_variante.php" method="POST">
                        <!-- Modal content -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Conferma</h4>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="variante_id" value="' . $variante['ID'] . '">
                                <input type="hidden" name="descrizione" value="' . htmlspecialchars($modello['descrizione'], ENT_QUOTES, 'UTF-8') . '">
                                <input type="hidden" name="articolo_id" value="' . $modello['ID'] . '">
                                <input type="hidden" name="desc_variante" value="' . $variante['desc_variante'] . '">
                                <p>Sicuro di voler procedere all\'eliminazione?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-default pull-left">Si</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>';
                }
            }
            ?>

        </table>
    </div>
    <br>
    <div class="form-group text-center">
        <span style="margin:50px;background-color:#ededed; border-radius: 10px; padding:20px; width:100%">
            <b>IMPORTANTE:</b>
            Tutte le modifiche effettuate sulla pagina, quali: aggiunte di varianti o caricamento dei
            file , vanno confermate con il pulsante di salvataggio sotto.</span>
    </div>
    <br>
    <div class="form-group text-center">
        <button type="submit" class="btn btn-warning">SALVA <i class="fad fa-save"></i></button>
    </div>
</fieldset>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#aggiungiVariante").click(function () {
            var nuovoCodiceVariante = $("input[name='nuovo_codice_variante']").val();
            var nuovaDescVariante = $("input[name='nuova_desc_variante']").val();
            var idModello = $("input[name='ID']").val();

            // Invia i dati al server tramite AJAX
            $.ajax({
                type: "POST",
                url: "add_variante.php", // Sostituisci con l'URL del tuo endpoint sul server
                data: {
                    id_modello: idModello,
                    cod_variante: nuovoCodiceVariante,
                    desc_variante: nuovaDescVariante
                },
                success: function (response) {
                    location.reload();
                }
            });
        });
    });
</script>