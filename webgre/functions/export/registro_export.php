<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select = array('id', 'id_terzista', 'data', 'stato');

// Set pagination limit
$pagelimit = 15;
$db->pageLimit = $pagelimit;

// Get current page.
$page = filter_input(INPUT_GET, 'page');
if (!$page) {
    $page = 1;
}

// Order by document number in descending order
$db->orderBy('id', 'DESC');

// Get result of the query.
$rows = $db->arraybuilder()->paginate('exp_documenti', $page, $select);
$total_pages = $db->totalPages;

include BASE_PATH . '/includes/header.php';
?>

<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header page-action-links text-left">Registro DDT</h1>
        </div>
    </div>
    <hr>

    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
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
                    $terzista = $db->where('id', $row['id_terzista'])->getOne('exp_terzisti', ['ragione_sociale', 'nazione']);
                    $text_color = ($row['stato'] == 'Aperto') ? 'blue' : 'green';
                    ?>
                    <tr>
                        <td style="vertical-align: middle; text-align:center;" data-id="<?php echo $row['id']; ?>">
                            <?php echo $row['id']; ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['id_terzista']; ?>">
                            <?php echo xss_clean($terzista['ragione_sociale']) . ' (' . xss_clean($terzista['nazione']) . ')'; ?>
                        </td>
                        <td style="vertical-align: middle; text-align:center;" data-id="<?php echo $row['data']; ?>">
                            <?php echo xss_clean($row['data']); ?>
                        </td>
                        <td style="vertical-align: middle; text-align:center; color: <?php echo $text_color; ?>"
                            data-id="<?php echo $row['stato']; ?>">
                            <?php echo xss_clean($row['stato']); ?>
                        </td>
                        <td style="vertical-align: middle; text-align:center;">
                            <?php if ($row['stato'] == 'Aperto'): ?>
                                <a href="continue_ddt.php?progressivo=<?php echo $row['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-money-check-edit"></i>
                                </a>
                                <a href="#" class="btn btn-danger delete-record" data-record-id="<?php echo $row['id']; ?>">
                                    <i class="far fa-trash-alt"></i>
                                </a>
                            <?php elseif ($row['stato'] == 'Chiuso'): ?>
                                <a href="view_ddt_export.php?progressivo=<?php echo $row['id']; ?>" class="btn btn-info">
                                    <i class="fal fa-eye"></i>
                                </a>
                            <?php endif; ?>
                            <a href="#" class="btn btn-success show-record-details"
                                data-record-id="<?php echo $row['id']; ?>">
                                <i class="fas fa-search"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- //Table -->

    <!-- Pagination -->
    <div class="text-center">
        <?php echo paginationLinks($page, $total_pages, 'registro.php'); ?>
    </div>
    <!-- //Pagination -->

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
                    <!-- Contenuto del modal per i dettagli del record -->
                    <div id="record-details"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <!-- //Record Details Modal -->
</div>
<!-- //Main container -->

<?php include BASE_PATH . '/includes/footer.php'; ?>

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
                                window.location.href = 'registro_export.php';
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