<fieldset>
    <input type="hidden" id="lancio_id" name="lancio_id" value="<?php echo $dati_lanci[0]['lancio']; ?>">

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr style="background-color:#337ab7; color:white;">
                    <th width="5%">Immagine</th>
                    <th width="20%">Articolo</th>
                    <th width="5%">Paia</th>
                    <th width="20%">Avanzamento</th>
                    <th width="30%" style="text-align:center;"><i class="fad fa-comments-alt"></i></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($dati_lanci as $row): ?>
                    <tr>
                        <input type="hidden" id="riga_id-<?php echo $row['ID'] ?>" name="riga_id"
                            value="<?php echo $row['ID'] ?>">
                        <td style="vertical-align: middle; text-align: center;">
                            <?php
                            $imageSrc = empty($row['path_to_image']) ? '../../src/img/default.jpg' : '../../' . $row['path_to_image'];
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc) ?>" alt="Immagine"
                                style="width: 80px; height: 80px; border: 1px solid lightgrey;">
                        </td>
                        <td style="vertical-align: middle;"><b>
                                <?php echo $row['nome_completo'] ?>
                            </b></td>
                        <td style="vertical-align: middle; text-align:center">
                            <?php echo $row['paia'] ?>
                        </td>
                        <td style="vertical-align: middle; text-align:center; position: relative;">
                            <button class="btn btn-link editAvanzamento" style="position: absolute; top: 0; left: 0;"
                                data-target="#editAvanzamentoModal-<?php echo $row['ID'] ?> " data-toggle="modal">
                                <i class="fad fa-tasks-alt fa-flip-vertical fa-lg"
                                    style="--fa-primary-color: #14bd36; --fa-secondary-color: #0daf43;"></i>
                            </button>

                            <?php
                            echo '<span style="font-size:15pt;"><i>' . $row['avanzamento'] . '</i></span>';
                            ?>

                        </td>

                        <td>
                            <div>
                                <button class="btn btn-link editNote" data-target="#editNoteModal-<?php echo $row['ID'] ?> "
                                    data-toggle="modal">
                                    <i class="fad fa-pencil fa-lg"
                                        style="--fa-primary-color: #ffcd42; --fa-secondary-color: #f80d0d;"></i>
                                </button>
                                <span id="noteText-<?php echo $row['ID'] ?>">
                                    <?php echo $row['note'] ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                    <td colspan="5">
                        <?php $progressValue = ($row['taglio'] + $row['preparazione'] + $row['orlatura'] + $row['spedizione']) * 25; ?>
                        <div class="progress" style="height: 100%; border: solid 1pt black;">
                            <div class="progress-bar progress-bar-info" role="progressbar"
                                style="font-size:10pt; min-width: 2em; width: <?php echo $progressValue; ?>%;"
                                aria-valuenow="<?php echo $progressValue; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $progressValue; ?>%
                            </div>
                        </div>
                    </td>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
    <?php foreach ($dati_lanci as $row): ?>
        <div class="modal fade" id="editNoteModal-<?php echo $row['ID'] ?>" tabindex="-1" role="dialog"
            aria-labelledby="editNoteModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="editNoteModalLabel">Modifica Nota</h4>
                    </div>
                    <div class="modal-body">
                        <textarea id="noteTextarea-<?php echo $row['ID'] ?>" class="form-control"
                            rows="5"><?php echo $row['note'] ?></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                        <button type="button" class="btn btn-primary saveNote"
                            data-row-id="<?php echo $row['ID'] ?>">Salva</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php foreach ($dati_lanci as $row): ?>

        <div class="modal fade" id="avanzamentoModal-<?php echo $row['ID'] ?>" tabindex="-1" role="dialog"
            aria-labelledby="avanzamentoModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="avanzamentoModalLabel">Avanzamento</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped table-bordered table-condensed" style="border:none;">
                            <tbody>
                                <table class="table table-striped table-bordered table-condensed" style="margin-top: 5%">
                                    <tbody>
                                        <tr>
                                            <span style="font-size:20pt;font-weight:bold;">LANCIO #
                                                <?php echo $row['lancio'] ?>
                                            </span></br>
                                            <span style="font-size:12pt;font-weight:bold;display:flex;"
                                                class="label label-warning">
                                                <?php echo htmlspecialchars($dati_lanci[0]['descrizione']); ?>
                                            </span>
                                            <td style="vertical-align: middle; text-align: center;">
                                                <?php
                                                $imageSrc = empty($row['path_to_image']) ? '../../src/img/default.jpg' : '../../' . $row['path_to_image'];
                                                ?>
                                                <img src="<?php echo htmlspecialchars($imageSrc) ?>" alt="Immagine"
                                                    style="width: 80px; height: 80px; border: 1px solid lightgrey;">
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <?php echo $row['nome_completo'] ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center; vertical-align:middle;"><span
                                                    class="label label-danger">AVANZAMENTO PER TUTTO IL LOTTO DI PAIA</span>
                                            </td>
                                            <td style="font-size:15pt; vertical-align: middle; text-align:center">
                                                <?php echo $row['paia'] ?>
                                            </td>
                                    </tbody>
                                </table>
                            </tbody>
                        </table>
                        <style>
                            .btn-group.full-width {
                                width: 100%;
                            }

                            .btn-group.full-width .btn {
                                width: 25%;
                            }
                        </style>
                        <p>Premi sul reparto per avanzare</p>
                        <div class="btn-group full-width" role="group">
                            <button type="button" class="btn btn-primary avanzamento-button" data-action="taglio"
                                data-item-id="<?php echo $row['ID'] ?>" <?php if ($row['taglio'] == 1) {
                                       echo 'disabled style="background-color: #ededed; border-color: #45a049;color:#45a049;font-weight:bold;"';
                                   } ?>>TAGLIO
                            </button>
                            <button type="button" class="btn btn-primary avanzamento-button" data-action="preparazione"
                                data-item-id="<?php echo $row['ID'] ?>" <?php if ($row['preparazione'] == 1) {
                                       echo 'disabled style="background-color: #ededed; border-color: #45a049;color:#45a049;font-weight:bold;"';
                                   } ?>>PREPARAZIONE
                            </button>
                            <button type="button" class="btn btn-primary avanzamento-button" data-action="orlatura"
                                data-item-id="<?php echo $row['ID'] ?>" <?php if ($row['orlatura'] == 1) {
                                       echo 'disabled style="background-color: #ededed; border-color: #45a049;color:#45a049;font-weight:bold;"';
                                   } ?>>ORLATURA
                            </button>
                            <button type="button" class="btn btn-primary avanzamento-button" data-action="spedizione"
                                data-item-id="<?php echo $row['ID'] ?>" <?php if ($row['spedizione'] == 1) {
                                       echo 'disabled style="background-color: #ededed; border-color: #45a049;color:#45a049;font-weight:bold;"';
                                   } ?>>SPEDIZIONE
                            </button>
                        </div>
                        <br>

                    </div>

                    <div class=" modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                        <!-- Pulsante "Salva" rimosso (lasciare vuoto) -->
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</fieldset>
<div class="modal fade" id="confermaAvanzamentoModal" tabindex="-1000" role="dialog"
    aria-labelledby="confermaAvanzamentoModalLabel">
    <div class="modal-dialog" role="document" style="border: solid 5pt red;
    border-radius: 10px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="confermaAvanzamentoModalLabel">Conferma Avanzamento</h4>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler avanzare questa fase?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary confermaAvanzamento">Conferma</button>
            </div>
        </div>
    </div>
</div>
<!-- JavaScript -->
<script>
    $(document).ready(function () {
        $('button.editAvanzamento').click(function () {
            // Ottieni l'ID dell'elemento specifico dal pulsante
            var itemId = $(this).data('target').split('-')[1];

            // Qui puoi personalizzare il contenuto del modale avanzamento
            // Ad esempio, puoi utilizzare AJAX per ottenere i dati dell'avanzamento dal server e aggiornare il contenuto del modale.
            // Una volta ottenuti i dati, puoi inserirli nel modale come desideri.

            // Successivamente, apri il modale avanzamento
            $('#avanzamentoModal-' + itemId).modal('show');
        });
        $('.avanzamento-button').click(function () {
            var itemId = $(this).data('item-id');
            var avanzamentoType = $(this).data('action');
            var confermaModal = $('#confermaAvanzamentoModal');

            confermaModal.modal('show');

            confermaModal.find('.confermaAvanzamento').click(function () {
                confermaModal.modal('hide');
                // Qui puoi aggiornare il database con il valore 1 per l'azione specifica
                $.ajax({
                    url: 'update_avanzamento.php', // Sostituisci con l'URL reale per l'aggiornamento dell'avanzamento
                    type: 'POST',
                    data: {
                        id: itemId,
                        avanzamentoType: avanzamentoType
                    },
                    success: function (response) {
                        location.reload();
                    }
                });
            });
        });
        // Gestisci il clic sul pulsante di modifica
        $('button.editNote').click(function () {
            // Ottieni l'ID dell'elemento specifico dal pulsante
            var itemId = $(this).data('target').split('-')[1];

            // Ottieni il testo della nota dalla riga corrispondente
            var noteText = $('#noteText-' + itemId).text();

            // Imposta il testo nel textarea del modale
            $('#noteTextarea-' + itemId).val(noteText);
        });


        // Gestisci il clic sul pulsante "Salva"
        $('button.saveNote').click(function () {
            // Ottieni l'ID dell'elemento specifico
            var itemId = $(this).data('row-id');

            // Ottieni il valore modificato dal textarea
            var editedNote = $('#noteTextarea-' + itemId).val();

            // Aggiorna il valore nella pagina
            $('#noteText-' + itemId).text(editedNote);

            // Chiudi il modale corretto
            $('#editNoteModal-' + itemId).modal('hide');

            // Esegui l'aggiornamento nel database (qui devi implementare la parte di AJAX)
            // Esempio di AJAX (da personalizzare):
            $.ajax({
                url: 'update_note.php',
                type: 'POST',
                data: {
                    note: editedNote,
                    id: itemId
                },
                success: function (response) {
                    // Gestisci la risposta o aggiorna la tabella nel caso di successo
                }
            });
        });
    });

</script>