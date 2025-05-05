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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Registro DDT</h1>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Registro DDT</li>
                    </ol>
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Lista Documenti</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center;" width="3%">Numero</th>
                                            <th width="30%">Destinatario</th>
                                            <th style="text-align:center;" width="20%">Data</th>
                                            <th style="text-align:center;" width="20%">Stato</th>
                                            <th style="text-align:center;" width="27%">Azioni</th>
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
                                                
                                                $text_color = ($row['stato'] == 'Aperto') ? 'blue' : 'green';
                                            } catch (PDOException $e) {
                                                $terzista = ['ragione_sociale' => 'N/A', 'nazione' => 'N/A'];
                                                $text_color = 'black';
                                            }
                                            ?>
                                            <tr>
                                                <td style="vertical-align: middle; text-align:center;">
                                                    <?php echo $row['id']; ?>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <?php echo htmlspecialchars($terzista['ragione_sociale']) . ' (' . htmlspecialchars($terzista['nazione']) . ')'; ?>
                                                </td>
                                                <td style="vertical-align: middle; text-align:center;">
                                                    <?php echo htmlspecialchars($row['data']); ?>
                                                </td>
                                                <td style="vertical-align: middle; text-align:center; color: <?php echo $text_color; ?>">
                                                    <?php echo htmlspecialchars($row['stato']); ?>
                                                </td>
                                                <td style="vertical-align: middle; text-align:center;">
                                                    <?php if ($row['stato'] == 'Aperto'): ?>
                                                        <a href="continue_ddt.php?progressivo=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-money-check-edit"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-danger btn-sm delete-record" data-record-id="<?php echo $row['id']; ?>">
                                                            <i class="far fa-trash-alt"></i>
                                                        </a>
                                                    <?php elseif ($row['stato'] == 'Chiuso'): ?>
                                                        <a href="view_ddt_export.php?progressivo=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                                            <i class="fal fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="#" class="btn btn-success btn-sm show-record-details"
                                                        data-record-id="<?php echo $row['id']; ?>">
                                                        <i class="fas fa-search"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="text-center mt-3">
                                <?php
                                // Funzione di paginazione
                                function paginationLinks($current_page, $total_pages, $base_url) {
                                    $links = "";
                                    
                                    if ($current_page > 1) {
                                        $links .= '<a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="btn btn-sm btn-primary"><i class="fas fa-chevron-left"></i></a> ';
                                    }
                                    
                                    for ($i = 1; $i <= $total_pages; $i++) {
                                        if ($i == $current_page) {
                                            $links .= '<a href="' . $base_url . '?page=' . $i . '" class="btn btn-sm btn-primary active">' . $i . '</a> ';
                                        } else {
                                            $links .= '<a href="' . $base_url . '?page=' . $i . '" class="btn btn-sm btn-outline-primary">' . $i . '</a> ';
                                        }
                                    }
                                    
                                    if ($current_page < $total_pages) {
                                        $links .= '<a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="btn btn-sm btn-primary"><i class="fas fa-chevron-right"></i></a>';
                                    }
                                    
                                    return $links;
                                }
                                
                                echo paginationLinks($page, $total_pages, 'documenti.php');
                                ?>
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
                <div class="modal-header">
                    <h5 class="modal-title" id="record-details-modal-label">Dettagli DDT</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="record-details"></div>
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

<script>
    $(document).ready(function () {
        // Aggiungi un gestore di eventi al clic sul pulsante "fa-search"
        $('.show-record-details').click(function () {
            var recordId = $(this).data('record-id');

            // Esegui una richiesta AJAX per ottenere i dettagli del record dal server
            $.ajax({
                url: 'get_ddt_details.php',
                type: 'GET',
                data: { id: recordId },
                success: function (response) {
                    // Inserisci i dettagli nel modal
                    $('#record-details').html(response);
                    // Apri il modal
                    $('#record-details-modal').modal('show');
                },
                error: function (xhr, status, error) {
                    // Gestisci eventuali errori
                    console.error(error);
                }
            });
        });

        // Aggiungi un gestore di eventi al clic sul pulsante "delete-record"
        $('.delete-record').click(function (e) {
            e.preventDefault();

            var recordId = $(this).data('record-id');

            // Utilizza SweetAlert per la conferma
            Swal.fire({
                title: 'Sei sicuro?',
                text: "Vuoi davvero cancellare questo DDT?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sì, cancella!',
                cancelButtonText: 'Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Esegui una richiesta AJAX per cancellare il record dal server
                    $.ajax({
                        url: 'delete_ddt.php',
                        type: 'POST',
                        data: { id: recordId },
                        success: function (response) {
                            // Utilizza SweetAlert per il messaggio di successo
                            Swal.fire(
                                'Cancellato!',
                                'Il DDT è stato cancellato con successo.',
                                'success'
                            ).then(() => {
                                // Ricarica la pagina dopo la cancellazione
                                window.location.href = 'documenti.php';
                            });
                        },
                        error: function (xhr, status, error) {
                            // Utilizza SweetAlert per il messaggio di errore
                            Swal.fire(
                                'Errore!',
                                'Si è verificato un errore durante la cancellazione del DDT.',
                                'error'
                            );
                            console.error(error);
                        }
                    });
                }
            });
        });
    });
</script>