<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/includes/header.php';
$progressivo = $_GET['progressivo'];
$dir = 'src/' . $progressivo;

// Recupera i file Excel presenti nella directory
$files = glob($dir . '/*.xlsx');
$progressivo = $_GET['progressivo'];
$db = getDbInstance();

// Recupera i dati del documento
$documento = $db->where('id', $progressivo)->getOne('exp_documenti');

// Recupera gli articoli
$articoli = $db->where('id_documento', $progressivo)->get('exp_dati_articoli');

// Recupera i dati del terzista
$terzista = $db->where('id', $documento['id_terzista'])->getOne('exp_terzisti');

// Calcola il totale
$total = 0;
foreach ($articoli as $articolo) {
    $total += round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
}

function getUniqueDoganaleCodes($articoli)
{
    $codes = [];
    foreach ($articoli as $articolo) {
        if (!in_array($articolo['voce_doganale'], $codes)) {
            $codes[] = $articolo['voce_doganale'];
        }
    }
    return $codes;
}

function getCountMancanze($progressivo, $db)
{
    return $db->where('id_documento', $progressivo)->getValue('exp_dati_mancanti', 'COUNT(*)');
}

$mancanzeCount = getCountMancanze($progressivo, $db);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">
                <a style="color:#0d6efd;">DA CONFERMARE </a> - DDT n°
                <?php echo $progressivo; ?>
            </h2>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-2 text-left"
            style="font-size:20pt;background-color:#fffab3;border-radius:10px;padding:10px;margin-right: 10px;">
            <strong>Documento:</strong>
            <?php echo $progressivo; ?><br>
            <strong>Data:</strong>
            <?php echo $documento['data']; ?><br>
        </div>

        <div class="col-lg-2" style="background-color:#ededed;border-radius:10px;padding:10px;margin-right: 10px;">
            <strong>Destinatario:</strong><br>
            <?php echo $terzista['ragione_sociale']; ?><br>
            <?php echo $terzista['indirizzo_1']; ?><br>
            <?php if ($terzista['indirizzo_2'])
                echo $terzista['indirizzo_2'] . '<br>'; ?>
            <?php if ($terzista['indirizzo_3'])
                echo $terzista['indirizzo_3'] . '<br>'; ?>
            <?php echo $terzista['nazione']; ?><br>
        </div>

        <div class="col-lg-3" style="background-color:#d0e9ff;border-radius:10px;padding:10px;margin-right: 10px;">
            <strong>Lanci Associati</strong><br><br>
            <?php
            $lanci = $db->where('id_doc', $progressivo)->get('exp_dati_lanci_ddt', null, ['lancio', 'articolo', 'paia']);
            foreach ($lanci as $lancio):
                ?>
                <li style="font-size:10pt;">
                    <strong>#</strong>
                    <?php echo $lancio['lancio']; ?> |
                    <strong>Articolo:</strong>
                    <?php echo $lancio['articolo']; ?> |
                    <strong>Paia:</strong>
                    <?php echo $lancio['paia']; ?><br>
                </li>
            <?php endforeach; ?>
        </div>
        <div class="col-lg-4"
            style="background-color:white; border-radius:10px; padding:10px; box-shadow: 7px 3px 17px 0px rgba(167, 202, 241, 0.8); overflow-x: auto;">
            <strong>Allegati:</strong>
            <div style="display: flex; flex-direction: row; align-items: center; justify-content: flex-start;">
                <?php foreach ($files as $file): ?>
                    <div
                        style="margin-top:10px;display: flex; flex-direction: column; align-items: center; margin-right: 10px;">
                        <a href="<?php echo $file; ?>" download
                            style="vertical-align:middle; text-decoration:none; padding:3px; border-radius:5px; transition: background-color 0.3s ease-in-out; display: flex; flex-direction: column; align-items: center;">
                            <i class="fas fa-file-excel" style="font-size:30px; color:#1D6F42; margin-bottom: 5px;"></i>
                            <span style="font-size:10px; white-space: nowrap;"><?php echo basename($file); ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <style>
                a:hover {
                    background-color: #f0f0f0;
                }
            </style>
        </div>

    </div>
    <hr>

    <div class="row">
        <div class="col-lg-3 text-left">
            <button class="btn btn-secondary" style="height:40px;" onclick="openModal()">
                <i class="fal fa-plus"></i> PESI E ASPETTO MERCE
            </button>
            <button class="btn btn-primary " style="height:40px; margin-left: 10px;"
                onclick="openAutorizzazioneModal()">
                <i class="fal fa-pencil-alt"></i> AUTORIZZAZIONE
            </button>
        </div>
        <div class="col-lg-9 text-right">
            <?php if ($documento['first_boot'] == 1): ?>
                <button class="btn btn-info "
                    style="background-color:#6610f2;border-color:#6610f2; height:40px; margin-left: 10px;"
                    onclick="cercaNcECosti()">
                    <i class="fal fa-search-plus"></i> CERCA VOCI DOGANALI E COSTI
                </button>
            <?php endif; ?>
            <button class="btn btn-warning" style="height:40px;" onclick="elaboraMancanti()">
                <i class="fal fa-sync-alt"></i> ELABORA MANCANTI
            </button>
            <!-- Aggiunta del pulsante "MANCANZE PRESENTI" con il badge -->
            <button class="btn btn-info" style="height:40px;" disabled>
                MANCANZE REGISTRATE
                <?php if ($mancanzeCount > 0): ?>
                    <span
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 12pt;"><?php echo $mancanzeCount; ?></span>
                <?php endif; ?>
            </button>
            <!-- Aggiunta del pulsante per visualizzare i dati -->
            <button class="btn btn-success" style="height:40px; margin-left: 10px;" onclick="exportToExcel()">
                <i class="fal fa-file-excel"></i> EXCEL
            </button>
            <a href="view_ddt_export.php?progressivo=<?php echo $progressivo; ?>" class="btn btn-primary"
                style="height:40px;">
                <i class="fal fa-file-invoice"></i> VISUALIZZA
            </a>
            <br><br>
        </div>
        <div class="col-lg-12">
            <table class="table table-bordered" id="dataTable">
                <thead>
                    <tr>
                        <th>CODICE ARTICOLO</th>
                        <th>DESCRIZIONE</th>
                        <th>VOCE DOGANALE</th>
                        <th>UM</th>
                        <th>QTA</th>
                        <th>QTA REALE</th>
                        <th>COSTO UNIT.</th>
                        <th>TOTALE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articoli as $articolo): ?>
                        <?php
                        $subtotal = round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
                        $qta_mancante = $articolo['qta_originale'] - $articolo['qta_reale'];
                        $style = '';

                        if ($qta_mancante > 0) {
                            $style = 'style="background-color: #ffdeb0"';
                        }
                        ?>
                        <tr>
                            <td contenteditable="false" style="background-color:#f0f0f0;">
                                <?php echo $articolo['codice_articolo']; ?>
                            </td>
                            <td contenteditable="true"
                                onBlur="updateData(<?php echo $articolo['id']; ?>, 'descrizione', this)">
                                <?php echo $articolo['descrizione']; ?>
                            </td>
                            <td contenteditable="true"
                                onBlur="updateData(<?php echo $articolo['id']; ?>, 'voce_doganale', this)">
                                <?php echo $articolo['voce_doganale']; ?>
                            </td>
                            <td contenteditable="false" style="background-color:#f0f0f0;">
                                <?php echo $articolo['um']; ?>
                            </td>
                            <td contenteditable="false" style="background-color:#f0f0f0;">
                                <?php echo $articolo['qta_originale']; ?>
                            </td>
                            <td contenteditable="true" <?php echo $style; ?>
                                onBlur="updateData(<?php echo $articolo['id']; ?>, 'qta_reale', this)">
                                <?php echo $articolo['qta_reale']; ?>
                            </td>
                            <td contenteditable="true"
                                onBlur="updateData(<?php echo $articolo['id']; ?>, 'prezzo_unitario', this)">
                                <?php echo $articolo['prezzo_unitario']; ?>
                            </td>
                            <td style="background-color:#d9fae2;">
                                <?php echo number_format($subtotal, 2, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right"><strong>Totale in €:</strong></td>
                        <td id="totalValue" data-total="<?php echo $total; ?>">
                            <?php echo ($total !== null) ? number_format($total, 2, ',', '.') : '0,00'; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <!-- MODALE AUTORIZZAZIONE -->
        <div class="modal fade" id="autorizzazioneModal" tabindex="-1" aria-labelledby="autorizzazioneModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="autorizzazioneModalLabel">Modifica Autorizzazione</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="autorizzazione" class="form-label">AUTORIZZAZIONE:</label>
                            <textarea class="form-control" id="autorizzazione" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger"
                            onclick="resetAutorizzazioneData()">Resetta</button>
                        <button type="button" class="btn btn-primary" onclick="saveAutorizzazioneData()">Salva</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODALE PIEDE -->
        <div class="modal fade" id="pesiModal" tabindex="-1" aria-labelledby="pesiModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pesiModalLabel">Dati piede documento:</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="aspettoMerce" class="form-label">ASPETTO MERCE:</label>
                            <input type="text" class="form-control" id="aspettoMerce">
                        </div>
                        <div class="mb-3">
                            <label for="numeroColli" class="form-label">NUMERO COLLI:</label>
                            <input type="number" class="form-control" id="numeroColli">
                        </div>
                        <div class="mb-3">
                            <label for="pesoLordo" class="form-label">PESO LORDO:</label>
                            <input type="number" step="0.01" class="form-control" id="pesoLordo">
                        </div>
                        <div class="mb-3">
                            <label for="pesoNetto" class="form-label">PESO NETTO:</label>
                            <input type="number" step="0.01" class="form-control" id="pesoNetto">
                        </div>
                        <div class="mb-3">
                            <label for="trasportatore" class="form-label">TRASPORTATORE:</label>
                            <input type="text" class="form-control" id="trasportatore">
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Voce Doganale</th>
                                    <th>Peso Netto</th>
                                </tr>
                            </thead>
                            <tbody id="doganaleTableBody">
                                <!-- I dati vengono caricati qui -->
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="resetPesiData()">Resetta</button>
                        <button type="button" class="btn btn-primary" onclick="savePesiData()">Salva</button>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.1.1/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.2/FileSaver.min.js"></script>
    <script>
        function exportToExcel() {
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('DDT');

            worksheet.columns = [
                { header: 'CODICE ARTICOLO', key: 'codice_articolo', width: 17 },
                { header: 'DESCRIZIONE', key: 'descrizione', width: 75 },
                { header: 'VOCE DOGANALE', key: 'voce_doganale', width: 15 },
                { header: 'UM', key: 'um', width: 10 },
                { header: 'QTA', key: 'qta_reale', width: 10 },
                { header: 'TOTALE', key: 'subtotal', width: 15 },
            ];

            <?php foreach ($articoli as $articolo): ?>
                worksheet.addRow({
                    codice_articolo: '<?php echo $articolo['codice_articolo']; ?>',
                    descrizione: '<?php echo $articolo['descrizione']; ?>',
                    voce_doganale: '<?php echo $articolo['voce_doganale']; ?>',
                    um: '<?php echo $articolo['um']; ?>',
                    qta_reale: Number('<?php echo $articolo['qta_reale']; ?>'.replace(',', '.')),
                    subtotal: Number('<?php echo number_format($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2, '.', ''); ?>')
                });
            <?php endforeach; ?>

            // Aggiungi i valori univoci di voce_doganale
            const uniqueDoganaleCodes = <?php echo json_encode(getUniqueDoganaleCodes($articoli)); ?>;
            let rowCount = worksheet.rowCount + 2;
            uniqueDoganaleCodes.forEach(code => {
                worksheet.addRow({
                    descrizione: "NC. " + code + " PESO NETTO KG."
                });
            });

            workbook.xlsx.writeBuffer().then((data) => {
                const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                saveAs(blob, `DDT_<?php echo $progressivo; ?>.xlsx`);
            });
        }

        function elaboraMancanti() {
            Swal.fire({
                title: 'Sei sicuro?',
                text: "I mancanti del documento in questione verranno ricalcolati!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Conferma'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('elabora_mancanti.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `progressivo=<?php echo $progressivo; ?>`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Operazione completata!',
                                    text: data.message,
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Errore!',
                                    text: data.message,
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: 'Si è verificato un errore durante l\'elaborazione dei manti',
                            });
                        });
                }
            });
        }

        function updateData(id, field, element) {
            const newValue = element.innerText;
            fetch('ddt_update_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&field=${field}&value=${newValue}`
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: data.message,
                        });
                    } else {
                        // Ricalcola il totale
                        let total = 0;
                        document.querySelectorAll("#dataTable tbody tr").forEach(row => {
                            const qta_reale = parseFloat(row.cells[5].innerText.replace(',', '.'));
                            const qta_originale = parseFloat(row.cells[4].innerText.replace(',', '.'));
                            const prezzo_unitario = parseFloat(row.cells[6].innerText.replace(',', '.'));
                            const subtotal = round(qta_reale * prezzo_unitario, 2);
                            row.cells[7].innerText = number_format(subtotal, 2, ',', '.');
                            total += subtotal;

                            // Applica lo stile arancione se qta_reale è inferiore a qta_originale
                            if (qta_reale < qta_originale) {
                                row.cells[5].style.backgroundColor = "#ffdeb0";
                            } else {
                                row.cells[5].style.backgroundColor = ""; // resetta il colore di sfondo
                            }
                        });
                        document.getElementById('totalValue').setAttribute('data-total', total);
                        document.getElementById('totalValue').innerText = number_format(total, 2, ',', '.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        text: 'Si è verificato un errore durante l\'aggiornamento dei dati',
                    });
                });
        }

        function round(value, decimals) {
            return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
        }

        function number_format(number, decimals, dec_point, thousands_sep) {

            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };

            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }

            return s.join(dec);
        }
        function handleKeyPress(event) {
            // Verifica se il tasto premuto è "Invio"
            if (event.keyCode === 13) {
                event.preventDefault(); // Previene l'inserimento di un nuovo paragrafo
                event.target.blur(); // Toglie il focus dall'elemento attuale
            }
        }

        document.querySelectorAll('[contenteditable="true"]').forEach(function (cell) {
            cell.addEventListener('keydown', handleKeyPress);
        });
        function openModal() {
            // Verifica se i dati sono già presenti in exp_pesi_documenti
            fetch('check_piedi_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `progressivo=<?php echo $progressivo; ?>`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('aspettoMerce').value = data.data.aspetto_colli;
                        document.getElementById('numeroColli').value = data.data.n_colli;
                        document.getElementById('pesoLordo').value = data.data.tot_peso_lordo;
                        document.getElementById('pesoNetto').value = data.data.tot_peso_netto;
                        document.getElementById('trasportatore').value = data.data.trasportatore;

                        let doganaleTableBody = document.getElementById('doganaleTableBody');
                        doganaleTableBody.innerHTML = '';
                        for (let i = 1; i <= 10; i++) { // Supponendo che ci siano massimo 5 voci doganali
                            if (data.data['voce_' + i]) {
                                doganaleTableBody.innerHTML += `
                        <tr>
                            <td>${data.data['voce_' + i]}</td>
                            <td><input type="number" step="0.1" class="form-control" name="pesoDoganale[]" value="${data.data['peso_' + i]}"></td>
                        </tr>
                    `;
                            }
                        }
                    } else {
                        // Se non ci sono dati, carica le voci doganali univoci
                        fetch('get_unique_doganale.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `progressivo=<?php echo $progressivo; ?>`
                        })
                            .then(response => response.json())
                            .then(data => {
                                let doganaleTableBody = document.getElementById('doganaleTableBody');
                                doganaleTableBody.innerHTML = '';
                                data.forEach(voce => {
                                    doganaleTableBody.innerHTML += `
                        <tr>
                            <td>${voce}</td>
                            <td><input type="number" step="0.01" class="form-control" name="pesoDoganale[]"></td>
                        </tr>
                    `;
                                });
                            });
                    }
                });

            // Apri il modale
            var myModal = new bootstrap.Modal(document.getElementById('pesiModal'));
            myModal.show();
        }

        function savePesiData() {
            let aspettoMerce = document.getElementById('aspettoMerce').value;
            let numeroColli = document.getElementById('numeroColli').value;
            let pesoLordo = document.getElementById('pesoLordo').value;
            let pesoNetto = document.getElementById('pesoNetto').value;
            let trasportatore = document.getElementById('trasportatore').value;

            let vociDoganali = [];
            document.querySelectorAll('#doganaleTableBody tr').forEach(row => {
                let voce = row.cells[0].innerText;
                let peso = row.cells[1].querySelector('input').value;
                vociDoganali.push({ voce: voce, peso: peso });
            });

            fetch('save_pesi_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    progressivo: <?php echo $progressivo; ?>,
                    aspettoMerce: aspettoMerce,
                    numeroColli: numeroColli,
                    pesoLordo: pesoLordo,
                    pesoNetto: pesoNetto,
                    trasportatore: trasportatore,
                    vociDoganali: vociDoganali
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Aggiornamento riuscito!',
                            text: data.message,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        text: 'Si è verificato un errore durante l\'aggiornamento dei dati',
                    });
                });
        }

        function resetPesiData() {
            Swal.fire({
                title: 'Sei sicuro?',
                text: "Questa azione cancellerà il record esistente. Vuoi procedere?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sì, cancella!',
                cancelButtonText: 'Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('reset_pesi_data.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            progressivo: <?php echo $progressivo; ?>
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Cancellazione riuscita!',
                                    text: data.message,
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Errore!',
                                    text: data.message,
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: 'Si è verificato un errore durante la cancellazione dei dati',
                            });
                        });
                }
            });
        }
        function openAutorizzazioneModal() {
            const formData = new FormData();
            formData.append('progressivo', <?php echo $progressivo; ?>);
            formData.append('azione', 'get');

            fetch('op_autorizzazione.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Log della risposta
                    if (data.success) {
                        document.getElementById('autorizzazione').value = data.data.autorizzazione;
                    } else {
                        document.getElementById('autorizzazione').value = '';
                    }
                    var autorizzazioneModal = new bootstrap.Modal(document.getElementById('autorizzazioneModal'));
                    autorizzazioneModal.show();
                });
        }
        function saveAutorizzazioneData() {
            const formData = new FormData();
            formData.append('progressivo', <?php echo $progressivo; ?>);
            formData.append('azione', 'save');
            formData.append('data', JSON.stringify({
                'autorizzazione': document.getElementById('autorizzazione').value
            }));

            fetch('op_autorizzazione.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(text => {
                    if (text.startsWith('Dati salvati con successo')) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Aggiornamento riuscito!',
                            text: text,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: text,
                        });
                    }
                });
        }

        function resetAutorizzazioneData() {
            const formData = new FormData();
            formData.append('progressivo', <?php echo $progressivo; ?>);
            formData.append('azione', 'reset');

            fetch('op_autorizzazione.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(text => {
                    if (text.startsWith('Dati ripristinati con successo')) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Ripristino riuscito!',
                            text: text,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: text,
                        });
                    }
                });
        }
        function cercaNcECosti() {
            Swal.fire({
                title: 'Conferma',
                text: "Tutti i dati inseriti verranno sovrascritti. Sei sicuro di voler continuare?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sì, continua',
                cancelButtonText: 'Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Effettua la ricerca e sovrascrivi i dati
                    $.ajax({
                        url: 'cerca_dati_presenti.php',
                        method: 'POST',
                        data: {
                            progressivo: '<?php echo $progressivo; ?>'
                        },
                        success: function (response) {
                            Swal.fire(
                                'Fatto!',
                                'I dati sono stati aggiornati correttamente.',
                                'success'
                            ).then(() => {
                                // Ricarica la pagina per visualizzare i dati aggiornati
                                location.reload();
                            });
                        }
                    });
                }
            });
        }
    </script>

    <?php include_once BASE_PATH . '/includes/footer.php'; ?>