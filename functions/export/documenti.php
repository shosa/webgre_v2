<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

// Get current page
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if (!$page) {
    $page = 1;
}

// Set pagination limit
$pagelimit = 15;

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Calcola l'offset per la paginazione
    $offset = ($page - 1) * $pagelimit;
    
    // Query per ottenere i record con paginazione e ordinamento
    $stmt = $conn->prepare("SELECT id, id_terzista, data, stato FROM exp_documenti ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $pagelimit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query per contare il totale dei record
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM exp_documenti");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_records = $count['total'];
    $total_pages = ceil($total_records / $pagelimit);
    
} catch (PDOException $e) {
    error_log("Errore nel recupero dei documenti: " . $e->getMessage());
    $rows = [];
    $total_pages = 0;
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
                    
                    <!-- Header con titolo e pulsante nuovo documento -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Registro DDT</h1>
                        <a href="new_step1" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Nuovo Documento
                        </a>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Registro DDT</li>
                    </ol>
                    
                    <!-- Filtri di ricerca -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Filtri</h6>
                            <button class="btn btn-link btn-sm" type="button" data-toggle="collapse" data-target="#filterCollapse">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                        <div class="collapse" id="filterCollapse">
                            <div class="card-body">
                                <form method="GET" action="documenti" class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="filter_numero">Numero</label>
                                        <input type="text" class="form-control" id="filter_numero" name="filter_numero">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="filter_destinatario">Destinatario</label>
                                        <input type="text" class="form-control" id="filter_destinatario" name="filter_destinatario">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="filter_data">Data</label>
                                        <input type="date" class="form-control" id="filter_data" name="filter_data">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="filter_stato">Stato</label>
                                        <select class="form-control" id="filter_stato" name="filter_stato">
                                            <option value="">Tutti</option>
                                            <option value="Aperto">Aperto</option>
                                            <option value="Chiuso">Chiuso</option>
                                        </select>
                                    </div>
                                    <div class="col-12 text-right">
                                        <button type="submit" class="btn btn-primary">Filtra</button>
                                        <a href="documenti" class="btn btn-secondary ml-2">Reset</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabella documenti -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Lista Documenti</h6>
                            <div>
                                <button class="btn btn-sm btn-outline-primary mr-2" id="refreshTable">
                                    <i class="fas fa-sync-alt"></i> Aggiorna
                                </button>
                              
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered " id="documentiTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="text-align:center;" width="10%">Numero</th>
                                            <th width="30%">Destinatario</th>
                                            <th style="text-align:center;" width="20%">Data</th>
                                            <th style="text-align:center;" width="15%">Stato</th>
                                            <th style="text-align:center;" width="25%">Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $row): ?>
                                            <?php
                                            try {
                                                $stmt = $conn->prepare("SELECT ragione_sociale, nazione FROM exp_terzisti WHERE id = :id");
                                                $stmt->bindParam(':id', $row['id_terzista'], PDO::PARAM_INT);
                                                $stmt->execute();
                                                $terzista = $stmt->fetch(PDO::FETCH_ASSOC);
                                                
                                                $badge_class = ($row['stato'] == 'Aperto') ? 'badge-warning' : 'badge-success';
                                            } catch (PDOException $e) {
                                                $terzista = ['ragione_sociale' => 'N/A', 'nazione' => 'N/A'];
                                                $badge_class = 'badge-secondary';
                                            }
                                            ?>
                                            <tr>
                                                <td style="vertical-align: middle; text-align:center;" class="font-weight-bold">
                                                    <?php echo $row['id']; ?>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <div><?php echo htmlspecialchars($terzista['ragione_sociale']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($terzista['nazione']); ?></small>
                                                </td>
                                                <td style="vertical-align: middle; text-align:center;">
                                                    <?php 
                                                    $data = new DateTime($row['data']);
                                                    echo $data->format('d/m/Y'); 
                                                    ?>
                                                </td>
                                                <td style="vertical-align: middle; text-align:center;">
                                                    <span class="badge <?php echo $badge_class; ?> px-3 py-2">
                                                        <?php echo htmlspecialchars($row['stato']); ?>
                                                    </span>
                                                </td>
                                                <td style="vertical-align: middle; text-align:center;">
                                                    <div class="btn-group">
                                                        <?php if ($row['stato'] == 'Aperto'): ?>
                                                            <a href="continue_ddt?progressivo=<?php echo $row['id']; ?>" 
                                                               class="btn btn-primary btn-sm" 
                                                               data-toggle="tooltip" 
                                                               title="Continua">
                                                                <i class="fal fa-edit"></i>
                                                            </a>
                                                        <?php elseif ($row['stato'] == 'Chiuso'): ?>
                                                            <a href="view_ddt_export?progressivo=<?php echo $row['id']; ?>" 
                                                               class="btn btn-info btn-sm" 
                                                               data-toggle="tooltip" 
                                                               title="Visualizza">
                                                                <i class="fal fa-eye"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <a href="#" 
                                                           class="btn btn-success btn-sm show-record-details" 
                                                           data-record-id="<?php echo $row['id']; ?>" 
                                                           data-toggle="tooltip" 
                                                           title="Dettagli">
                                                            <i class="fal fa-search"></i>
                                                        </a>
                                                        
                                                        <div class="btn-group">
                                                            <button type="button" 
                                                                   class="btn btn-secondary btn-sm dropdown-toggle" 
                                                                   data-toggle="dropdown" 
                                                                   aria-haspopup="true" 
                                                                   aria-expanded="false">
                                                                <i class="fal fa-ellipsis-v"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="#" onclick="printDDT(<?php echo $row['id']; ?>)">
                                                                    <i class="fas fa-print mr-2"></i> Stampa
                                                                </a>
                                                                <?php if ($row['stato'] == 'Aperto'): ?>
                                                                   
                                                                    <a class="dropdown-item text-danger delete-record" href="#" data-record-id="<?php echo $row['id']; ?>">
                                                                        <i class="fas fa-trash-alt mr-2"></i> Elimina
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (empty($rows)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <div class="text-muted mb-3">
                                                        <i class="fas fa-inbox fa-3x"></i>
                                                    </div>
                                                    <h5>Nessun documento trovato</h5>
                                                    <p>Crea un nuovo documento facendo clic sul pulsante "Nuovo Documento".</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="mt-4">
                                <nav aria-label="Navigazione pagine">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="documenti?page=<?php echo ($page - 1); ?>" aria-label="Precedente">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" aria-label="Precedente">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        // Calcola il range di pagine da mostrare
                                        $range = 2; // Numero di pagine da mostrare prima e dopo la pagina corrente
                                        $start_page = max(1, $page - $range);
                                        $end_page = min($total_pages, $page + $range);
                                        
                                        // Mostra il pulsante per la prima pagina se necessario
                                        if ($start_page > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="documenti?page=1">1</a></li>';
                                            if ($start_page > 2) {
                                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                            }
                                        }
                                        
                                        // Mostra le pagine nel range
                                        for ($i = $start_page; $i <= $end_page; $i++) {
                                            if ($i == $page) {
                                                echo '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
                                            } else {
                                                echo '<li class="page-item"><a class="page-link" href="documenti?page=' . $i . '">' . $i . '</a></li>';
                                            }
                                        }
                                        
                                        // Mostra il pulsante per l'ultima pagina se necessario
                                        if ($end_page < $total_pages) {
                                            if ($end_page < $total_pages - 1) {
                                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="documenti?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="documenti?page=<?php echo ($page + 1); ?>" aria-label="Successivo">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" aria-label="Successivo">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                <div class="text-center text-muted small">
                                    Mostrando <?php echo count($rows); ?> di <?php echo $total_records; ?> documenti totali
                                </div>
                            </div>
                            <!-- //Pagination -->
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

    <!-- Record Details Modal -->
    <div class="modal fade" id="record-details-modal" tabindex="-1" role="dialog"
        aria-labelledby="record-details-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="record-details-modal-label">Dettagli DDT</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-5" id="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Caricamento...</span>
                        </div>
                        <p class="mt-2">Caricamento dettagli...</p>
                    </div>
                    <div id="record-details" class="d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-primary" id="print-details">Stampa</button>
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
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function () {
        // Inizializza i tooltip di Bootstrap
        $('[data-toggle="tooltip"]').tooltip();
        
        // Inizializza DataTables per funzionalità aggiuntive alla tabella
        $('#documentiTable').DataTable({
            "paging": false,
            "info": false,
            "searching": false,
            "language": {
                "emptyTable": "Nessun documento trovato"
            }
        });
        
        // Gestione del pulsante Aggiorna
        $('#refreshTable').click(function() {
            location.reload();
        });
        
        // Gestione dell'esportazione
       
        
        

        // Aggiungi un gestore di eventi al clic sul pulsante di visualizzazione dettagli
        $('.show-record-details').click(function (e) {
            e.preventDefault();
            var recordId = $(this).data('record-id');

            // Mostra il loading spinner e nasconde i dettagli
            $('#loading-spinner').removeClass('d-none');
            $('#record-details').addClass('d-none');
            
            // Mostra il modal
            $('#record-details-modal').modal('show');

            // Esegui una richiesta AJAX per ottenere i dettagli del record dal server
            $.ajax({
                url: 'get_ddt_details',
                type: 'GET',
                data: { id: recordId },
                success: function (response) {
                    // Nascondi il loading spinner e mostra i dettagli
                    $('#loading-spinner').addClass('d-none');
                    $('#record-details').removeClass('d-none');
                    
                    // Inserisci i dettagli nel modal
                    $('#record-details').html(response);
                },
                error: function (xhr, status, error) {
                    // Gestisci eventuali errori
                    $('#loading-spinner').addClass('d-none');
                    $('#record-details').removeClass('d-none').html(
                        `<div class="alert alert-danger">
                            Si è verificato un errore durante il caricamento dei dettagli.
                            <br>Errore: ${error}
                        </div>`
                    );
                    console.error(error);
                }
            });
        });

        // Gestione stampa dettagli
        $('#print-details').click(function() {
            var printContents = document.getElementById('record-details').innerHTML;
            var originalContents = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div class="container mt-4">
                    <h2 class="text-center mb-4">Dettagli DDT</h2>
                    ${printContents}
                </div>
            `;
            
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        });

        // Aggiungi un gestore di eventi al clic sul pulsante "delete-record"
        $('.delete-record').click(function (e) {
            e.preventDefault();

            var recordId = $(this).data('record-id');

            // Utilizza SweetAlert per la conferma
            Swal.fire({
                title: 'Sei sicuro?',
                text: "Questa operazione non può essere annullata!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sì, elimina!',
                cancelButtonText: 'Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostra un indicatore di caricamento
                    Swal.fire({
                        title: 'Eliminazione in corso',
                        text: 'Attendere prego...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Esegui una richiesta AJAX per cancellare il record dal server
                    $.ajax({
                        url: 'delete_ddt',
                        type: 'POST',
                        data: { id: recordId },
                        success: function (response) {
                            // Analizza la risposta JSON
                            try {
                                var result = JSON.parse(response);
                                
                                if (result.success) {
                                    // Utilizza SweetAlert per il messaggio di successo
                                    Swal.fire({
                                        title: 'Eliminato!',
                                        text: result.message || 'Il DDT è stato eliminato con successo.',
                                        icon: 'success',
                                        confirmButtonColor: '#3085d6'
                                    }).then(() => {
                                        // Ricarica la pagina dopo la cancellazione
                                        window.location.reload();
                                    });
                                } else {
                                    // Utilizza SweetAlert per il messaggio di errore
                                    Swal.fire({
                                        title: 'Errore!',
                                        text: result.message || 'Si è verificato un errore durante l\'eliminazione del DDT.',
                                        icon: 'error',
                                        confirmButtonColor: '#3085d6'
                                    });
                                }
                            } catch (e) {
                                Swal.fire({
                                    title: 'Errore!',
                                    text: 'Si è verificato un errore durante l\'elaborazione della risposta.',
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            // Utilizza SweetAlert per il messaggio di errore
                            Swal.fire({
                                title: 'Errore!',
                                text: 'Si è verificato un errore durante la connessione al server.',
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                            console.error(error);
                        }
                    });
                }
            });
        });
    });
    
    // Funzioni per operazioni aggiuntive
    function printDDT(id) {
        window.open(`view_ddt_export?progressivo=${id}`, '_blank');
    }
    
    
</script>