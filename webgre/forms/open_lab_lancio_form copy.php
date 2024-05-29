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
                        <input type="hidden" id="riga_id-<?= $row['ID'] ?>" name="riga_id" value="<?= $row['ID'] ?>">
                        <td style="vertical-align: middle; text-align: center;">
                            <?php
                            $imageSrc = empty($row['path_to_image']) ? 'src/img/default.jpg' : $row['path_to_image'];
                            ?>
                            <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Immagine"
                                style="max-width: 80px; max-height: 80px; border: 1px solid lightgrey;">
                        </td>
                        <td style="vertical-align: middle;"><b>
                                <?= $row['nome_completo'] ?>
                            </b></td>
                        <td style="vertical-align: middle; text-align:center">
                            <?= $row['paia'] ?>
                        </td>
                        <td style="vertical-align: bottom; text-align:center;">
                            <?php
                            $progressValue = ($row['taglio'] + $row['preparazione'] + $row['orlatura'] + $row['spedizione']) * 25;
                            echo '<span style="font-size:15pt;"><b>' . $row['avanzamento'] . '</b></span>';
                            ?>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar progress-bar-success" role="progressbar"
                                    style="min-width: 2em; width: <?= $progressValue; ?>%;"
                                    aria-valuenow="<?= $progressValue; ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $progressValue; ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <button class="btn btn-link editNote" data-target="#editNoteModal-<?= $row['ID'] ?>"
                                    data-toggle="modal">
                                    <i class="fad fa-pencil fa-lg"
                                        style="--fa-primary-color: #ffcd42; --fa-secondary-color: #f80d0d;"></i>
                                </button>
                                <span id="noteText-<?= $row['ID'] ?>">
                                    <?= $row['note'] ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
    <?php foreach ($dati_lanci as $row): ?>
        <div class="modal fade" id="editNoteModal-<?= $row['ID'] ?>" tabindex="-1" role="dialog"
            aria-labelledby="editNoteModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="editNoteModalLabel">Modifica Nota</h4>
                    </div>
                    <div class="modal-body">
                        <textarea id="noteTextarea-<?= $row['ID'] ?>" class="form-control"
                            rows="5"><?= $row['note'] ?></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                        <button type="button" class="btn btn-primary saveNote"
                            data-row-id="<?= $row['ID'] ?>">Salva</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</fieldset>

<!-- JavaScript -->
<script>
    $(document).ready(function () {
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