<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<fieldset style="padding: 10px;">
    <div class="col-md-3">
        <div class="form-group" style="margin-right: 20px;">
            <label for="lancio">Lancio #</label>
            <input type="text" name="ins_lancio" value="" placeholder="Numero del Lancio" class="form-control"
                required="required" id="ins_lancio">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="linee">Linea</label>
            <select name="ins_linea" id="ins_linea" class="form-control">
                <?php
                // Esegui una query per ottenere i valori dalla colonna 'descrizione' della tabella 'linee'
                $linee = $db->get('linee', null, ['descrizione', 'sigla']);
                foreach ($linee as $linea) {
                    echo '<option value="' . $linea['sigla'] . '">' . $linea['descrizione'] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

</fieldset>
<table class="table table-bordered">
    <thead>
        <tr style="background-color:#337ab7; color:white;">
            <th width="75%">Articolo</th>
            <th width="15%">Paia</th>
            <th width="10%">Azioni</th>
        </tr>
    </thead>
    <tbody id="tabella-lanci">
        <!-- Qui verranno aggiunte le righe dei lanci -->
    </tbody>
</table>
<div class="row" style="padding:20px;">
    <div class="col-md-9">
        <div class="form-group" style="margin:10px;">
            <input type="text" id="ins_nome_completo" value="" placeholder="Articolo" class="form-control">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group" style="margin:10px;">
            <input type="number" name="ins_paia" value="" placeholder="Numero di paia" class="form-control"
                id="ins_paia">
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group" style="margin:10px;">
            <input type="hidden" name="ins_id_modello" id="ins_id_modello" value="">
            <input type="hidden" name="ins_id_variante" id="ins_id_variante" value="">
            <button type="button" id="aggiungi-riga" class="btn btn-success"><i
                    class="fas fa-plus"></i></button>
        </div>
    </div>
</div>
<div class="form-group text-center">
    <label></label>
    <button type="submit" class="btn btn-warning">INSERISCI LANCIO <span
            class="fas fa-paper-plane"></span></button>
</div>

<script>
    $(document).ready(function () {
        var modelli = [
            <?php
            $modelli = $db->get('var_modelli');
            foreach ($modelli as $modello) {
                echo '{ label: "' . htmlspecialchars($modello['nome_completo']) . '", value: "' . htmlspecialchars($modello['nome_completo']) . '", modello_id: "' . $modello['id_modello'] . '", articolo_id: "' . $modello['ID'] . '" }, ';
            }
           
            ?>
        ];

        var modelloIdInput = $("#ins_id_modello");
        var varianteInput = $("#ins_id_variante");

        var tabellaLanci = $("#tabella-lanci");

        $("#ins_nome_completo").autocomplete({
            source: modelli,
            minLength: 1,
            select: function (event, ui) {
                modelloIdInput.val(ui.item.modello_id);
                varianteInput.val(ui.item.articolo_id)
            }
        });

        $("#aggiungi-riga").on("click", function () {
            var articolo = $("#ins_nome_completo").val();
            var paia = $("#ins_paia").val();
            var lancio = $("#ins_lancio").val();
            var linea = $("#ins_linea").val();
            var idModello = modelloIdInput.val();
            var idVariante = varianteInput.val();
            var lancioInput = $("#ins_lancio");
            var lineaSelect = $("#ins_linea");

            // Aggiungi una nuova riga solo se è stato selezionato un articolo e specificata una quantità (paia)
            if (idModello && idVariante && paia && lancio !== "") {
                // Aggiungi una nuova riga alla tabella dei lanci
                var newRow = '<tr style="background-color:white;">' +
                    '<td style="vertical-align: middle;">' + articolo +
                    '<input type="hidden" id="id_modello" name="id_modello[]" value="' + idModello + '">' +
                    '<input type="hidden" id="id_variante" name="id_variante[]" value="' + idVariante + '">' +
                    '</td>' +
                    '<td style="vertical-align: middle; font-size:20pt;">' + paia +
                    '<input type="hidden" id="paia" name="paia[]" value="' + paia + '"></td>' +
                    '<td><button type="button" class="btn btn-danger elimina-riga"><i class="fas fa-trash"></i></button></td>' +
                    '<input type="hidden" name="lancio[]" value="' + lancio + '">' +
                    '<input type="hidden" name="linea[]" value="' + linea + '">' +
                    '<input type="hidden" name="stato[]" value="IN ATTESA" placeholder="stato" class="form-control" required="required" id="stato">' +
                    '</tr>';

                tabellaLanci.append(newRow);

                // Resetta i campi di input
                $("#ins_nome_completo").val("");
                $("#ins_paia").val("");
                modelloIdInput.val(""); // Resetta il valore di id_modello
                varianteInput.val(""); // Resetta il valore di id_variante
                lancioInput.prop("readonly", true);
                lineaSelect.prop("readonly", true);
            }
        });

        // Aggiungi un gestore di eventi per eliminare una riga
        tabellaLanci.on("click", ".elimina-riga", function () {
            $(this).closest("tr").remove();
        });

        // Gestisci l'invio del form

    });
</script>