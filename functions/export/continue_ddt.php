<?php
/**
 * Dettaglio DDT da confermare
 * 
 * Questo script gestisce la visualizzazione e la modifica dei dettagli di un DDT.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php';

// Recupera e valida il progressivo
$progressivo = filter_input(INPUT_GET, 'progressivo', FILTER_VALIDATE_INT);
if (!$progressivo) {
    $_SESSION['failure'] = "ID documento non valido";
    header('location: documenti.php');
    exit();
}

// Recupera i file Excel presenti nella directory
$dir = 'src/' . $progressivo;
$files = glob($dir . '/*.xlsx');

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recupera i dati del documento
    $stmt = $conn->prepare("SELECT * FROM exp_documenti WHERE id = :id");
    $stmt->bindParam(':id', $progressivo, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $_SESSION['failure'] = "Documento non trovato";
        header('location: documenti.php');
        exit();
    }

    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    // Recupera gli articoli
    $stmt = $conn->prepare("SELECT * FROM exp_dati_articoli WHERE id_documento = :id_documento order by voce_doganale");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $articoli = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recupera i dati del terzista
    $stmt = $conn->prepare("SELECT * FROM exp_terzisti WHERE id = :id");
    $stmt->bindParam(':id', $documento['id_terzista'], PDO::PARAM_INT);
    $stmt->execute();
    $terzista = $stmt->fetch(PDO::FETCH_ASSOC);

    // Recupera i lanci associati
    $stmt = $conn->prepare("SELECT lancio, articolo, paia FROM exp_dati_lanci_ddt WHERE id_doc = :id_doc");
    $stmt->bindParam(':id_doc', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $lanci = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcola il totale
    $total = 0;
    foreach ($articoli as $articolo) {
        $total += round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
    }

    // Ottieni il conteggio delle mancanze
    $stmt = $conn->prepare("SELECT COUNT(*) FROM exp_dati_mancanti WHERE id_documento = :id_documento");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $mancanzeCount = $stmt->fetchColumn();

} catch (PDOException $e) {
    $_SESSION['failure'] = "Errore nel recupero dei dati: " . $e->getMessage();
    header('location: documenti.php');
    exit();
}

/**
 * Ottiene i codici doganali univoci dagli articoli
 * 
 * @param array $articoli Lista degli articoli
 * @return array Lista dei codici doganali univoci
 */
function getUniqueDoganaleCodes($articoli)
{
    $codes = [];
    foreach ($articoli as $articolo) {
        if (!empty($articolo['voce_doganale']) && !in_array($articolo['voce_doganale'], $codes)) {
            $codes[] = $articolo['voce_doganale'];
        }
    }
    return $codes;
}

include(BASE_PATH . "/components/header.php");
?>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <span class="text-primary">DA CONFERMARE</span> - DDT n° <?php echo $progressivo; ?>
                        </h1>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="documenti.php">Documenti</a></li>
                        <li class="breadcrumb-item active">Dettaglio DDT</li>
                    </ol>

                    <!-- Informazioni Documento -->
                    <div class="row mb-4">
                        <!-- Documento Box -->
                        <div class="col-lg-3 mb-3">
                            <div class="card bg-warning text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="h5 mb-0 font-weight-bold">DDT N°:   <?php echo $progressivo; ?>
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold">Del:
                                                <?php echo $documento['data']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice fa-2x "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Destinatario Box -->
                        <div class="col-lg-3 mb-3">
                            <div class="card bg-light border-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Destinatario</div>
                                            <div class="h6 mb-0 font-weight-bold">
                                                <?php echo htmlspecialchars($terzista['ragione_sociale']); ?></div>
                                            <div class="small"><?php echo htmlspecialchars($terzista['indirizzo_1']); ?>
                                            </div>
                                            <?php if (!empty($terzista['indirizzo_2'])): ?>
                                                <div class="small"><?php echo htmlspecialchars($terzista['indirizzo_2']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($terzista['indirizzo_3'])): ?>
                                                <div class="small"><?php echo htmlspecialchars($terzista['indirizzo_3']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="small font-weight-bold">
                                                <?php echo htmlspecialchars($terzista['nazione']); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lanci Associati Box -->
                        <div class="col-lg-3 mb-3">
                            <div class="card bg-info text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Lanci Associati
                                    </div>
                                    <div style="max-height: 120px; overflow-y: auto;">
                                        <ul class="list-unstyled">
                                            <?php foreach ($lanci as $lancio): ?>
                                                <li class="small">
                                                    <strong>#</strong> <?php echo $lancio['lancio']; ?> |
                                                    <strong>Art:</strong> <?php echo $lancio['articolo']; ?> |
                                                    <strong>Paia:</strong> <?php echo $lancio['paia']; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Allegati Box -->
                        <div class="col-lg-3 mb-3">
                            <div class="card shadow border-primary h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Allegati
                                    </div>
                                    <div style="max-height: 120px; overflow-y: auto;">
                                        <div class="d-flex flex-wrap">
                                            <?php foreach ($files as $file): ?>
                                                <div class="text-center mx-2 mb-2">
                                                    <a href="<?php echo $file; ?>" download class="text-decoration-none">
                                                        <i class="fas fa-file-excel fa-2x text-success"></i>
                                                        <div class="small mt-1"><?php echo basename($file); ?></div>
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <button class="btn btn-secondary mr-2" onclick="openModal()">
                                <i class="fas fa-weight"></i> Pesi e Aspetto Merce
                            </button>
                            <button class="btn btn-primary" onclick="openAutorizzazioneModal()">
                                <i class="fas fa-pencil-alt"></i> Autorizzazione
                            </button>
                        </div>
                        <div class="col-lg-6 text-right">
                            <?php if ($documento['first_boot'] == 1): ?>
                                <button class="btn btn-light border-dark mr-2" onclick="cercaNcECosti()">
                                    <i class="fas fa-search-plus"></i> Cerca Voci Doganali e Costi
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-warning mr-2" onclick="elaboraMancanti()">
                                <i class="fas fa-sync-alt"></i> Elabora Mancanti
                            </button>
                            <button class="btn btn-info mr-2" disabled>
                                Mancanze Registrate
                                <?php if ($mancanzeCount > 0): ?>
                                    <span class="badge badge-danger"><?php echo $mancanzeCount; ?></span>
                                <?php endif; ?>
                            </button>
                            <button class="btn btn-success mr-2" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <a href="view_ddt_export.php?progressivo=<?php echo $progressivo; ?>"
                                class="btn btn-primary">
                                <i class="fas fa-file-invoice"></i> Visualizza
                            </a>
                        </div>
                    </div>

                    <!-- Tabella Articoli -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Dettaglio Articoli</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
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
                                            $style = ($qta_mancante > 0) ? 'style="background-color: #ffdeb0"' : '';
                                            ?>
                                            <tr>
                                                <td contenteditable="false" style="background-color:#f0f0f0;">
                                                    <?php echo htmlspecialchars($articolo['codice_articolo']); ?>
                                                </td>
                                                <td contenteditable="true"
                                                    onBlur="updateData(<?php echo $articolo['id']; ?>, 'descrizione', this)">
                                                    <?php echo htmlspecialchars($articolo['descrizione']); ?>
                                                </td>
                                                <td contenteditable="true"
                                                    onBlur="updateData(<?php echo $articolo['id']; ?>, 'voce_doganale', this)">
                                                    <?php echo htmlspecialchars($articolo['voce_doganale']); ?>
                                                </td>
                                                <td contenteditable="false" style="background-color:#f0f0f0;">
                                                    <?php echo htmlspecialchars($articolo['um']); ?>
                                                </td>
                                                <td contenteditable="false" style="background-color:#f0f0f0;">
                                                    <?php echo htmlspecialchars($articolo['qta_originale']); ?>
                                                </td>
                                                <td contenteditable="true" <?php echo $style; ?>
                                                    onBlur="updateData(<?php echo $articolo['id']; ?>, 'qta_reale', this)">
                                                    <?php echo htmlspecialchars($articolo['qta_reale']); ?>
                                                </td>
                                                <td contenteditable="true"
                                                    onBlur="updateData(<?php echo $articolo['id']; ?>, 'prezzo_unitario', this)">
                                                    <?php echo htmlspecialchars($articolo['prezzo_unitario']); ?>
                                                </td>
                                                <td style="background-color:#d9fae2;">
                                                    <?php echo number_format($subtotal, 2, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="7" class="text-right"><strong>Totale in €:</strong></td>
                                            <td id="totalValue" data-total="<?php echo $total; ?>">
                                                <?php echo number_format($total, 2, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- MODALE PESI E ASPETTO MERCE -->
                    <div class="modal fade" id="pesiModal" tabindex="-1" aria-labelledby="pesiModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="pesiModalLabel">Dati piede documento:</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
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
                                            <!-- I dati verranno caricati dinamicamente -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger"
                                        onclick="resetPesiData()">Resetta</button>
                                    <button type="button" class="btn btn-primary"
                                        onclick="savePesiData()">Salva</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODALE AUTORIZZAZIONE -->
                    <div class="modal fade" id="autorizzazioneModal" tabindex="-1"
                        aria-labelledby="autorizzazioneModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="autorizzazioneModalLabel">Modifica Autorizzazione</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
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
                                    <button type="button" class="btn btn-primary"
                                        onclick="saveAutorizzazioneData()">Salva</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
</body>

<script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.1.1/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.2/FileSaver.min.js"></script>

<script>
    /**
     * Esporta i dati degli articoli in un file Excel
     */
    function exportToExcel() {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('DDT');

        // Definizione delle colonne
        worksheet.columns = [
            { header: 'CODICE ARTICOLO', key: 'codice_articolo', width: 17 },
            { header: 'DESCRIZIONE', key: 'descrizione', width: 75 },
            { header: 'VOCE DOGANALE', key: 'voce_doganale', width: 15 },
            { header: 'UM', key: 'um', width: 10 },
            { header: 'QTA', key: 'qta_reale', width: 10 },
            { header: 'TOTALE', key: 'subtotal', width: 15 },
        ];

        // Aggiunta delle righe con i dati degli articoli
        <?php foreach ($articoli as $articolo): ?>
            worksheet.addRow({
                codice_articolo: '<?php echo addslashes($articolo['codice_articolo']); ?>',
                descrizione: '<?php echo addslashes($articolo['descrizione']); ?>',
                voce_doganale: '<?php echo addslashes($articolo['voce_doganale']); ?>',
                um: '<?php echo addslashes($articolo['um']); ?>',
                qta_reale: Number('<?php echo str_replace(',', '.', $articolo['qta_reale']); ?>'),
                subtotal: Number('<?php echo number_format($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2, '.', ''); ?>')
            });
        <?php endforeach; ?>

        // Aggiunta delle voci doganali uniche
        const uniqueDoganaleCodes = <?php echo json_encode(getUniqueDoganaleCodes($articoli)); ?>;
        let rowCount = worksheet.rowCount + 2;
        uniqueDoganaleCodes.forEach(code => {
            worksheet.addRow({
                descrizione: "NC. " + code + " PESO NETTO KG."
            });
        });

        // Generazione e download del file Excel
        workbook.xlsx.writeBuffer().then((data) => {
            const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            saveAs(blob, `DDT_<?php echo $progressivo; ?>.xlsx`);
        });
    }

    /**
     * Avvia l'elaborazione dei mancanti
     */
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
                            text: 'Si è verificato un errore durante l\'elaborazione dei mancanti',
                        });
                    });
            }
        });
    }

    /**
     * Aggiorna i dati di un articolo
     */
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

    /**
     * Arrotonda un numero al numero specificato di decimali
     */
    function round(value, decimals) {
        return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
    }

    /**
     * Formatta un numero con separatori delle migliaia e decimali
     */
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

    /**
     * Gestisce la pressione del tasto invio nelle celle editabili
     */
    function handleKeyPress(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            event.target.blur();
        }
    }

    // Aggiunge l'event listener a tutte le celle editabili
    document.querySelectorAll('[contenteditable="true"]').forEach(function (cell) {
        cell.addEventListener('keydown', handleKeyPress);
    });

    /**
     * Apre il modal per i pesi e l'aspetto della merce
     */
    function openModal() {
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
                    // Carica i dati esistenti nel form
                    document.getElementById('aspettoMerce').value = data.data.aspetto_colli;
                    document.getElementById('numeroColli').value = data.data.n_colli;
                    document.getElementById('pesoLordo').value = data.data.tot_peso_lordo;
                    document.getElementById('pesoNetto').value = data.data.tot_peso_netto;
                    document.getElementById('trasportatore').value = data.data.trasportatore;

                    // Carica i dati delle voci doganali
                    let doganaleTableBody = document.getElementById('doganaleTableBody');
                    doganaleTableBody.innerHTML = '';
                    for (let i = 1; i <= 10; i++) {
                        if (data.data['voce_' + i]) {
                            doganaleTableBody.innerHTML += `
                        <tr>
                            <td>${data.data['voce_' + i]}</td>
                            <td><input type="number" step="0.01" class="form-control" name="pesoDoganale[]" value="${data.data['peso_' + i]}"></td>
                        </tr>
                        `;
                        }
                    }
                } else {
                    // Se non ci sono dati esistenti, carica le voci doganali univoche
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

        // Mostra il modal
        var myModal = new bootstrap.Modal(document.getElementById('pesiModal'));
        myModal.show();
    }

    /**
     * Salva i dati dei pesi e dell'aspetto della merce
     */
    function savePesiData() {
        let aspettoMerce = document.getElementById('aspettoMerce').value;
        let numeroColli = document.getElementById('numeroColli').value;
        let pesoLordo = document.getElementById('pesoLordo').value;
        let pesoNetto = document.getElementById('pesoNetto').value;
        let trasportatore = document.getElementById('trasportatore').value;

        // Raccolta dati delle voci doganali
        let vociDoganali = [];
        document.querySelectorAll('#doganaleTableBody tr').forEach(row => {
            let voce = row.cells[0].innerText;
            let peso = row.cells[1].querySelector('input').value;
            vociDoganali.push({ voce: voce, peso: peso });
        });

        // Invio dei dati al server
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

    /**
     * Resetta i dati dei pesi e dell'aspetto della merce
     */
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

    /**
     * Apre il modal per l'autorizzazione
     */
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
                if (data.success) {
                    document.getElementById('autorizzazione').value = data.data.autorizzazione;
                } else {
                    document.getElementById('autorizzazione').value = '';
                }
                var autorizzazioneModal = new bootstrap.Modal(document.getElementById('autorizzazioneModal'));
                autorizzazioneModal.show();
            });
    }

    /**
     * Salva i dati dell'autorizzazione
     */
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

    /**
     * Resetta i dati dell'autorizzazione
     */
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

    /**
     * Cerca voci doganali e costi
     */
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
                fetch('cerca_dati_presenti.php', {
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
                                text: 'I dati sono stati aggiornati correttamente.',
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: data.message || 'Si è verificato un errore durante l\'operazione',
                            });
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
        });
    }
</script>