<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php';

// Recupera il progressivo dalla query string
$progressivo = filter_input(INPUT_GET, 'progressivo', FILTER_VALIDATE_INT);
if (!$progressivo) {
    $_SESSION['failure'] = "ID documento non valido";
    header('location: documenti.php');
    exit();
}

// Percorso della cartella temp
$dir = 'temp/';

// Leggi tutti i file nella cartella temp
$files = scandir($dir);

// Rimuovi . e ..
$files = array_diff($files, array('.', '..'));

/**
 * Estrae i dettagli da un file Excel
 * @param string $filePath Percorso del file Excel
 * @return array Dettagli estratti dal file
 */
function getDetailsFromExcel($filePath) {
    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $worksheet = $reader->load($filePath)->getActiveSheet();

        $details = [
            'LANCIO' => $worksheet->getCell('B2')->getValue(),
            'PAIA_DA_PRODURRE' => $worksheet->getCell('B3')->getValue()
        ];

        return $details;
    } catch (Exception $e) {
        error_log("Errore nell'elaborazione del file Excel: " . $e->getMessage());
        return [
            'LANCIO' => 'N/A',
            'PAIA_DA_PRODURRE' => 'N/A'
        ];
    }
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
                            <span class="text-primary">STEP 3</span> > Elenco Articoli DDT n° <?php echo $progressivo; ?>
                        </h1>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item">Registro DDT</a></li>
                        <li class="breadcrumb-item active">Nuovo DDT - Step 3</li>
                    </ol>
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">File Excel Caricati</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($files)): ?>
                                <div class="alert alert-warning">
                                    Nessun file Excel caricato. Torna allo <a href="new_step2.php?progressivo=<?php echo $progressivo; ?>">step precedente</a> per caricare dei file.
                                </div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($files as $file): ?>
                                        <?php $details = getDetailsFromExcel($dir . $file); ?>
                                        <a href="javascript:void(0);" onclick="showExcelContent('<?php echo $file; ?>')" class="list-group-item list-group-item-action flex-column align-items-start">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1">
                                                    <i class="fas fa-file-excel text-success"></i> <?php echo htmlspecialchars($file); ?>
                                                </h5>
                                                <small>
                                                    <span class="badge badge-primary">Lancio: <?php echo htmlspecialchars($details['LANCIO']); ?></span>
                                                    <span class="badge badge-info">Paia: <?php echo htmlspecialchars($details['PAIA_DA_PRODURRE']); ?></span>
                                                </small>
                                            </div>
                                            <small>Clicca per visualizzare i dettagli</small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="text-right mt-4">
                                    <button class="btn btn-success" onclick="generaDDT()">
                                        <i class="fas fa-file-import"></i> Genera DDT
                                    </button>
                                </div>
                            <?php endif; ?>
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
    
    <!-- Modal per visualizzare il contenuto dell'Excel -->
    <div class="modal fade" id="excelModal" tabindex="-1" aria-labelledby="excelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="excelModalLabel">Contenuto Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="excelContent"></div>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.1.1/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.2/FileSaver.min.js"></script>

<script>
    function showExcelContent(fileName) {
        fetch(`temp/${fileName}`)
            .then(response => response.arrayBuffer())
            .then(data => {
                let workbook = new ExcelJS.Workbook();
                return workbook.xlsx.load(data);
            })
            .then(workbook => {
                let worksheet = workbook.worksheets[0];
                let headers = worksheet.getRow(5).values;
                let rows = worksheet.getSheetValues();

                // Rimuovi l'intestazione ripetuta
                rows = rows.slice(6);

                let tableContent = `
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    ${headers.map(header => `<th>${header || ''}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.map(row => {
                                    if (!row || row.length <= 1) return '';
                                    
                                    let highlight = '';
                                    if (row[0] === 'TAGLIO') {
                                        highlight = 'class="table-danger"';
                                    } else if (row[0] === 'ORLATURA') {
                                        highlight = 'class="table-success"';
                                    }
                                    
                                    return `<tr ${highlight}>
                                        ${row.map((cell, index) => `<td>${cell || ''}</td>`).join('')}
                                    </tr>`;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `;

                document.getElementById('excelContent').innerHTML = tableContent;
                $('#excelModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Errore!',
                    text: 'Errore nel caricamento del file Excel.'
                });
            });
    }

    function generaDDT() {
        Swal.fire({
            title: 'Generazione DDT',
            text: 'Elaborazione in corso...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('genera_ddt.php?progressivo=<?php echo $progressivo; ?>')
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(text);
                    });
                }
                return response.text();
            })
            .then(text => {
                try {
                    let data = JSON.parse(text);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'DDT generati con successo!',
                            text: data.message,
                        }).then((result) => {
                            window.location.href = `continue_ddt.php?progressivo=<?php echo $progressivo; ?>`;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: data.message,
                        });
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        text: 'Risposta non valida dal server: ' + text
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Errore!',
                    text: 'Errore nella generazione dei DDT: ' + error.message
                });
            });
    }
</script>