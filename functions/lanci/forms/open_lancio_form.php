<style>
    .selected {
        background-color: #dff2ff;
    }

    .custom-button {
        width: 100%;
        height: 100px;
        /* Imposta l'altezza desiderata per i pulsanti */
        font-size: 20px;
        /* Imposta la dimensione del testo desiderata */
        margin: 10px;
        /* Aggiunge spazio tra i pulsanti */
        border-radius: 15px;
        font-size: 18pt;
        border: none;
    }

    .custom-button:disabled {
        background-color: grey !important;
    }

    .custom-button .btn-icon {
        float: left;
        margin-right: 10px;
        /* Aggiunge spazio tra l'icona e il testo */
    }

    .custom-button .btn-text {
        float: center;
    }

    .verdina {
        background-color: #dbfbdb !important;
    }
</style>

<fieldset>
    <input type="hidden" id="lancio_id" name="lancio_id" value="<?php echo $dati_lanci[0]['lancio']; ?>">

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr style="background-color:#337ab7; color:white;">
                    <th width="2%"></th>
                    <th width="5%">Immagine</th>
                    <th width="20%">Modello</th>
                    <th width="25%">Variante</th>
                    <th width="10%">Paia</th>
                    <th width="35%">Avanzamento</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($dati_lanci as $row) {
                    // Aggiungi la classe "verdina" se il campo "spedizione" è uguale a 1
                    $rowClass = ($row['spedizione'] == 1) ? 'verdina' : '';
                    echo '<tr class="' . $rowClass . '">';
                    echo '<input type="hidden" id="riga_id" name="riga_id" value="' . $row['ID'] . '">';
                    echo '<td style="vertical-align: middle; text-align: center;">';
                    // Aggiungi il pulsante con l'icona a forma di cestino rosso solo se "spedizione" è uguale a 1
                    if ($row['spedizione'] == 1) {
                        // Aggiungi un elemento <a> con l'icona a forma di cestino
                        echo '<a href="#" class="custom-button open-modal" data-toggle="modal" data-target="#confirmRicezioneModal" data-riga-id="' . $row['ID'] . '">';
                        echo '<i class="fad fa-shipping-fast" style="--fa-primary-color: #2c7cba; --fa-secondary-color: #f98f15; --fa-secondary-opacity: 1;"></i>';
                        echo '</a>';
                    }
                    echo '</td>';
                    echo '<td style="vertical-align: middle; text-align: center;">';

                    $imageSrc = empty($row['path_to_image']) ? '../../src/img/default.jpg' : '../../' . $row['path_to_image'];
                    echo '<img src="' . htmlspecialchars($imageSrc) . '" alt="Immagine" style="width: 80px; height: 80px; border: 1px solid lightgrey;">';

                    echo '</td>';
                    echo '<td style="vertical-align: middle;">' . $row['descrizione'] . '</td>';
                    echo '<td style="vertical-align: middle;">' . $row['desc_variante'] . '</td>';
                    echo '<td style="vertical-align: middle; text-align:center;">' . $row['paia'] . '</td>';
                    echo '<td style="vertical-align: middle; text-align:center;">' . $row['avanzamento'] . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <!-- Primo pulsante -->
                <button type="button" class="btn btn-info custom-button" id="btnAssegnaLaboratorio">
                    <div class="btn-icon"><i class="fad fa-digging fa-lg"></i></div>
                    <div class="btn-text">
                        <?php
                        if ($dati_lanci[0]['id_lab'] !== null) {
                            echo 'CAMBIA LAVORANTE';
                        } else {
                            echo 'ASSEGNA LAVORANTE';
                        }
                        ?>
                    </div>
                </button>
            </div>
            <div class="col-md-4">
                <!-- Secondo pulsante -->
                <button type="button" class="btn btn-warning custom-button" id="btnLancia">
                    <div class="btn-icon"><i class="fad fa-paper-plane fa-lg"></i></div>
                    <div class="btn-text">LANCIA</div>
                </button>
            </div>
            <div class="col-md-4">
                <button type="button" style="background-color:#f0423c;" class="btn btn-primary custom-button"
                    id="btnAnnulla">
                    <div class="btn-icon"><i class="fad fa-backward fa-lg"></i></div>
                    <div class="btn-text">ANNULLA LANCIO</div>
                </button>
            </div>
        </div>
        <!-- <div class="row">
                <div class="col-md-4">
                    
                    <button type="button" class="btn btn-success custom-button" id="btnPlaceholder3">Placeholder
                        3</button>
                </div>
                <div class="col-md-4">
                   
                    <button type="button" class="btn btn-success custom-button" id="btnPlaceholder4">Placeholder
                        4</button>
                </div>
                <div class="col-md-4">
                   
                    <button type="button" class="btn btn-success custom-button" id="btnPlaceholder5">Placeholder
                        5</button>
                </div>
            </div> -->
    </div>
</fieldset>
<div class="modal fade" id="confirmRicezioneModal" tabindex="-1" role="dialog"
    aria-labelledby="confirmRicezioneModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="confirmRicezioneModalLabel">Conferma Ricezione</h4>
            </div>
            <div class="modal-body">
                <p>Confermi di aver ricevuto le paia?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary" id="confirmRicezioneAction">Conferma</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal per la selezione del laboratorio -->
<div class="modal fade" id="popupLaboratorio" tabindex="-1" role="dialog" aria-labelledby="popupLaboratorioLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="popupLaboratorioLabel">Seleziona un laboratorio</h4>
            </div>
            <div class="modal-body">
                <form id="selezionaLaboratorioForm">
                    <ul>
                        <!-- Qui carichiamo dinamicamente l'elenco dei laboratori -->
                        <?php
                        $db = getDbInstance();
                        $db->orderBy('Nome', 'ASC');
                        $laboratori = $db->get('laboratori');

                        foreach ($laboratori as $laboratorio) {
                            echo '<span>';
                            echo '<label style="width:85%;margin:5px;border:solid 1pt #d4d4d4;border-radius:10px;padding:10px;font-size: 12pt;">';
                            echo '<input type="radio" name="laboratorio_id" value="' . $laboratorio['ID'] . '"> ' . $laboratorio['Nome'];
                            echo '</label>';
                            echo '</span>';
                            echo '</br>';

                        }
                        ?>
                    </ul>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-success" id="btnConfermaLaboratorio">Conferma</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="confirm-lancia" role="dialog">
    <div class="modal-dialog">
        <form id="lanciaForm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Conferma</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="lancio_id" id="lancio_id" value="">
                    <p>Confermi di voler cambiare lo stato del lancio <b>#
                            <?php echo $dati_lanci[0]['lancio']; ?>
                        </b> ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" id="confirm-lancia-action">Si</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="confirm-annulla" role="dialog">
    <div class="modal-dialog">
        <form id="AnnullaForm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Conferma</h4>
                </div>
                <div class="modal-body" style="color: #f96363;background: #ffe9e9;">
                    <input type="hidden" name="lancio_id" id="lancio_id" value="">
                    <p>Confermi di voler annullare il lancio <b>#
                            <?php echo $dati_lanci[0]['lancio']; ?>
                        </b> ? <br>Tutte le annotazioni e avanzamenti verranno eliminati.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" id="confirm-annulla-action">Si</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Operazione completata</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Laboratorio assegnato con successo!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lanciaModal" tabindex="-1" role="dialog" aria-labelledby="lanciaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lanciaModalLabel">Operazione completata</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Lancio eseguito correttamente!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="annullaModal" tabindex="-1" role="dialog" aria-labelledby="annullaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="annullaModalLabel">Ripristino eseguito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Il lancio è stato annullato!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="operazioneEseguitaModal" tabindex="-1" role="dialog"
    aria-labelledby="operazioneEseguitaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="operazioneEseguitaModalLabel">Operazione Eseguita</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                La riga del lancio è stata eliminata in quanto ricevuta!
                <span class="label label-success">Verrai riportato alla pagina LANCI</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
<!-- JavaScript -->
<script>
    $(document).ready(function () {
        var btnAssegnaLaboratorio = $("#btnAssegnaLaboratorio");
        var laboratorio = "<?php echo $dati_lanci[0]['id_lab']; ?>"; // Usa 'lancio' invece di 'laboratorio'
        var btnLancia = $("#btnLancia");
        var modalLancia = $("#confirm-lancia");
        var confirmLanciaAction = $("#confirm-lancia-action");
        var btnAnnulla = $("#btnAnnulla");
        var modalAnnulla = $("#confirm-annulla");
        var confirmAnnullaAction = $("#confirm-annulla-action");
        var lancioIdInput = $("#lancio_id");
        var stato = "<?php echo $dati_lanci[0]['stato']; ?>";

        if (stato == "IN ATTESA") {
            if (laboratorio === "" || laboratorio === null) {
                btnLancia.prop("disabled", true);

            }
            btnAnnulla.prop("disabled", true);
        }

        if (stato !== "IN ATTESA") {
            btnAssegnaLaboratorio.prop("disabled", true);
            btnLancia.prop("disabled", true);
        }

        // Gestisci il clic sul pulsante "Assegna Laboratorio"
        $("#btnAssegnaLaboratorio").click(function () {
            $("#popupLaboratorio").modal('show');
        });

        $("#btnConfermaLaboratorio").click(function () {
            // Esegui la richiesta AJAX per assegnare il laboratorio selezionato al lancio
            var laboratorioSelezionato = $("input[name='laboratorio_id']:checked").val();
            var lancioSelezionato = $("input[name='lancio_id']").val();
            $.ajax({
                url: 'set_laboratorio.php',
                type: 'POST',
                data: {
                    lancio_id: lancioSelezionato,
                    laboratorio_id: laboratorioSelezionato
                },
                success: function (response) {
                    // Visualizza il modal di successo
                    $('#successModal').modal('show');

                    // Nascondi il modal di assegnazione
                    $("#popupLaboratorio").modal('hide');

                    // Non ricarica immediatamente la pagina, ma attende la chiusura del modal di successo
                }
            });
        });
        $("input[name='laboratorio_id']").click(function () {
            // Rimuovi la classe "selected" da tutti i label
            $("label").removeClass("selected");

            // Aggiungi la classe "selected" solo all'elemento padre (il <label>) dell'opzione radio selezionata
            $(this).parent("label").addClass("selected");
        });
        //Chiusura popup di successo assegna laboratorio
        $('#successModal').on('hidden.bs.modal', function (e) {
            location.reload();
        })
        // Gestisci il clic sul pulsante "LANCIA"
        btnLancia.click(function () {
            modalLancia.modal('show');
            var lancioSelezionato = $("input[name='lancio_id']").val();
            lancioIdInput.val(lancioSelezionato);
        });

        // Gestisci il clic sul pulsante di conferma all'interno del modale
        confirmLanciaAction.click(function () {
            var lancioSelezionato = lancioIdInput.val();
            $.ajax({
                url: 'lancia.php', // Il nome del file PHP che eseguirà l'aggiornamento nel database
                type: 'POST',
                data: {
                    lancio_id: lancioSelezionato
                },
                success: function (response) {
                    // Visualizza il modal di successo
                    $('#lanciaModal').modal('show');
                }
            });
            modalLancia.modal('hide'); // Chiudi il modale dopo l'azione
        });

        $('#lanciaModal').on('hidden.bs.modal', function (e) {
            location.reload();
        });

        btnAnnulla.click(function () {
            modalAnnulla.modal('show');
            var lancioSelezionato = $("input[name='lancio_id']").val();
            lancioIdInput.val(lancioSelezionato);
        });

        // Gestisci il clic sul pulsante di conferma all'interno del modale
        confirmAnnullaAction.click(function () {
            var lancioSelezionato = lancioIdInput.val();

            $.ajax({
                url: 'annulla.php', // Il nome del file PHP che eseguirà l'aggiornamento nel database
                type: 'POST',
                data: {
                    lancio_id: lancioSelezionato
                },
                success: function (response) {
                    $('#annullaModal').modal('show');

                }
            });
            modalAnnulla.modal('hide'); // Chiudi il modale dopo l'azione
        });
        $('#annullaModal').on('hidden.bs.modal', function (e) {
            location.reload();
        });
        $(".open-modal").click(function () {
            var rigaId = $(this).data("riga-id");
            $("#confirmRicezioneAction").data("riga-id", rigaId);
        });

        // Gestisci il clic sul pulsante di conferma all'interno del modale
        $("#confirmRicezioneAction").click(function () {
            var rigaId = $(this).data("riga-id");

            // Esegui l'azione quando l'utente conferma
            // Puoi utilizzare AJAX per aprire il file 'del_riga_lancio.php' e passare l'ID.
            $.ajax({
                url: 'del_riga_lancio.php', // Il nome del file PHP da aprire
                type: 'POST', // Puoi utilizzare POST o GET a seconda delle tue esigenze
                data: { riga_id: rigaId }, // Passa l'ID alla pagina PHP
                success: function (response) {
                    $('#operazioneEseguitaModal').modal('show');
                },
                error: function (error) {
                    // Gestisci eventuali errori o problemi con la richiesta AJAX
                    console.error("Errore durante l'azione: " + error);
                }
            });

            // Chiudi il modale
            $("#confirmRicezioneModal").modal('hide');
        });
        $('#operazioneEseguitaModal').on('hidden.bs.modal', function (e) {
            window.location.href = "../../functions/lanci/lanci.php";
        });
    });

</script>