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
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .card:hover {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .file-item {
        padding: 12px;
        margin-bottom: 10px;
        background-color: #f8f9fa;
        border-radius: 6px;
        transition: all 0.2s ease;
        
    }
    
    .file-item:hover {
        background-color: #e9ecef;
    }
    
    .btn-primary {
        background-color: #007BFF;
        border-color: #007BFF;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-success {
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .modal-content {
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        border-radius: 12px 12px 0 0;
        background-color: #f0f8ff;
    }
    
    .modal-footer {
        border-radius: 0 0 12px 12px;
    }
    
    /* Responsive tables */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .breadcrumb {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 12px 20px;
    }
    
    .badge-custom {
        padding: 6px 10px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    /* Info box nel modale */
    .info-box {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
        
    }
    
    /* Smartphone e tablet */
    @media (max-width: 991.98px) {
        .table th, .table td {
            white-space: nowrap;
            font-size: 14px;
        }
        
        .file-details {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .file-details h5 {
            margin-bottom: 10px;
        }
    }
</style>

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
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                                <h1 class="h3 mb-0 text-gray-800">
                                    <span class="text-primary">STEP 3</span> > Elenco Articoli DDT n° <?php echo $progressivo; ?>
                                </h1>
                            </div>
                            
                            <ol class="breadcrumb mb-4">
                                <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="documenti.php">Registro DDT</a></li>
                                <li class="breadcrumb-item">Step 2</a></li>
                                <li class="breadcrumb-item active">Step 3</li>
                            </ol>
                            
                            <?php if (empty($files)): ?>
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle mr-2"></i> Nessun file Excel caricato. Torna allo <a href="new_step2.php?progressivo=<?php echo $progressivo; ?>">step precedente</a> per caricare dei file.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle mr-2"></i> Le schede caricate sono pronte per essere elaborate. Clicca su "Visualizza" per vedere i dettagli di ciascun file.
                                </div>
                                
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">
                                            <i class="fas fa-file-excel mr-2"></i> Schede Tecniche Caricate:
                                            <span class="badge badge-primary ml-2"><?php echo count($files); ?> file</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($files as $file): ?>
                                            <?php $details = getDetailsFromExcel($dir . $file); ?>
                                            <div class="file-item mb-3">
                                                <div class="d-flex justify-content-between align-items-center file-details">
                                                    <h5 class="mb-0">
                                                        <i class="fas fa-file-excel text-success mr-2"></i> <?php echo htmlspecialchars($file); ?>
                                                    </h5>
                                                    <div>
                                                        <span class="badge badge-primary badge-custom mr-2">
                                                            <i class="fas fa-tag mr-1"></i> Lancio: <?php echo htmlspecialchars($details['LANCIO']); ?>
                                                        </span>
                                                        <span class="badge badge-info badge-custom">
                                                            <i class="fas fa-cubes mr-1"></i> Paia: <?php echo htmlspecialchars($details['PAIA_DA_PRODURRE']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <button class="btn btn-sm btn-primary" onclick="showExcelContent('<?php echo $file; ?>')">
                                                        <i class="fas fa-eye mr-1"></i> Visualizza
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="text-right mt-4">
                                    <button class="btn btn-success btn-lg" onclick="generaDDT()">
                                        <i class="fas fa-file-import mr-2"></i> Genera DDT
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="excelModalLabel">
                        <i class="fas fa-file-excel mr-2"></i> Dettagli Scheda Tecnica
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="info-box mb-4" id="excel-info">
                        <!-- Qui verranno visualizzate le informazioni del file Excel -->
                    </div>
                    <div class="table-responsive">
                        <div id="excelContent"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
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
        // Mostra un loader mentre si carica il file
        Swal.fire({
            title: 'Caricamento...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
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
                
                // Estrai informazioni aggiuntive
                let infoModello = worksheet.getCell('A1').value || 'N/A';
                let infoLancio = worksheet.getCell('B2').value || 'N/A';
                let infoPaia = worksheet.getCell('B3').value || 'N/A';
                
                // Crea la sezione di informazioni
                document.getElementById('excel-info').innerHTML = `
                    <div class="row">
                        <div class="col-md-4">
                            <strong><i class="fas fa-tag mr-2"></i>Modello:</strong> ${infoModello}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-rocket mr-2"></i>Lancio:</strong> ${infoLancio}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-cubes mr-2"></i>Paia:</strong> ${infoPaia}
                        </div>
                    </div>
                `;

                let tableContent = `
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
                `;

                // Chiudi il loader SweetAlert
                Swal.close();
                
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
            didOpen: () => {
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
                            title: 'DDT generato con successo!',
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