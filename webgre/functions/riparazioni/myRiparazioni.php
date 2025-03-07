<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
$utente = $_SESSION["username"];
try {
    // Connessione al database utilizzando PDO
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Get current page
    $page = filter_input(INPUT_GET, 'page');
    if (!$page) {
        $page = 1;
    }
    // Per page limit for pagination
    $pagelimit = 15;
    // Calculate offset for pagination
    $offset = ($page - 1) * $pagelimit;
    // Prepare SQL statement
    $statement = $pdo->prepare("SELECT URGENZA, IDRIP, CODICE, ARTICOLO, QTA, CARTELLINO, DATA, REPARTO, LINEA, COMPLETA FROM riparazioni WHERE UTENTE = :username");
    $statement->bindParam(':username', $utente, PDO::PARAM_STR);
    // Execute SQL statement
    $statement->execute();
    // Fetch all rows as an associative array
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    // Get unique laboratori
    $laboratori = array_unique(array_column($rows, 'LABORATORIO'));
} catch (PDOException $e) {
    // If an error occurs, display the error message
    echo "Errore: " . $e->getMessage();
}
include(BASE_PATH . "/components/header.php");
function getUrgencyColor($urgency)
{
    switch ($urgency) {
        case 'BASSA':
            return 'success'; // Verde
        case 'MEDIA':
            return 'warning'; // Giallo
        case 'ALTA':
            return 'danger'; // Rosso
        default:
            return 'primary'; // Colore predefinito o nessun colore
    }
}
?>

<style>
    /* Stile moderno per la checkbox */
    .custom-checkbox input[type="checkbox"] {
        display: none;
    }

    .custom-checkbox label {
        position: relative;
        cursor: pointer;
    }

    .custom-checkbox label:before {
        content: "";
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #4e73df;
        border-radius: 4px;
        vertical-align: middle;
        margin-right: 10px;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    .custom-checkbox input[type="checkbox"]:checked+label:before {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .custom-checkbox label:after {
        content: "✓";
        position: absolute;
        left: 4px;
        top: 2px;
        font-size: 16px;
        color: white;
        display: none;
    }

    .custom-checkbox input[type="checkbox"]:checked+label:after {
        display: block;
    }

    /* Evidenziazione della riga selezionata */
    .selected-row {
        background-color: rgba(107 197 247) !important;
        color: var(--light);
        font-weight: bold !important;
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
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Elenco Riparazioni</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Elenco riparazioni</h6>
                            <a href="add_step1?operation=create" class="btn btn-success btn-circle shadow-sm ml-auto"
                                style="margin-left:5px;"><i class="fal fa-plus fa-xl"></i></a>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-4">

                                <div>
                                    <button id="delete-selected" class="btn btn-danger btn-xl btn-circle text-white "
                                        disabled><i class="fa fa-trash"></i>
                                    </button>
                                    <button id="print-selected" class="btn btn-yellow btn-xl btn-circle text-white "
                                        disabled><i class="fa fa-print"></i>
                                    </button>
                                </div>

                            </div>
                            <table class="table table-bordered table-responsive table-striped rounded shadow-sm"
                                style="font-size:10pt;" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="2%"><input type="checkbox" id="select-all"></th>
                                        <th width="5%">#</th>
                                        <th width="15%">Codice</th>
                                        <th width="7%"></th>
                                        <th width="36%">Articolo</th>
                                        <th width="5%">PA</th>
                                        <th width="10%">Cartellino</th>
                                        <th width="5%">Data</th>
                                        <th width="10%">Reparto</th>
                                        <th width="5%" hidden>Linea</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row) { ?>
                                        <tr>
                                            <td class="align-middle text-center">
                                                <input type="checkbox" class="select-row"
                                                    id="checkbox-<?php echo $row['IDRIP']; ?>"
                                                    value="<?php echo $row['IDRIP']; ?>">
                                                <label for="checkbox-<?php echo $row['IDRIP']; ?>"></label>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-<?php echo getUrgencyColor($row['URGENZA']); ?>"
                                                    style="font-size:10pt !important;"><?php echo $row['IDRIP']; ?></span>

                                            </td>
                                            <td class="align-middle"><?php echo $row['CODICE']; ?></td>
                                            <td class="align-middle text-center">
                                                <a href="#"
                                                    class="btn  btn-sm btn-circle btn-light border-success border text-success show-record-details"
                                                    data-record-id="<?php echo htmlspecialchars($row['IDRIP']); ?>">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                                <a href="edit_riparazioni.php?riparazione_id=<?php echo htmlspecialchars($row['IDRIP']); ?>&operation=edit"
                                                    class="btn btn-sm btn-circle btn-light border-primary text-primary">
                                                    <i class="fas fa-pencil-alt"></i>

                                                </a>

                                            </td>
                                            <td class="align-middle"><?php echo $row['ARTICOLO']; ?></td>
                                            <td class="align-middle"><?php echo $row['QTA']; ?></td>
                                            <td class="align-middle"><?php echo $row['CARTELLINO']; ?></td>
                                            <td class="align-middle"><?php echo $row['DATA']; ?></td>
                                            <td class="align-middle"><?php echo $row['REPARTO']; ?></td>
                                            <td class="align-middle" hidden><?php echo $row['LINEA']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <?php include(BASE_PATH . "/components/footer.php"); ?>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- MODALE DETTAGLI -->
    <div class="modal fade" id="record-details-modal" tabindex="-1" role="dialog"
        aria-labelledby="record-details-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="record-details-modal-label">Dettagli</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="record-details"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Page Wrapper -->
    </div>
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <!-- Bootstrap core JavaScript-->
    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jszip/jszip.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/pdfmake.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/vfs_fonts.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.html5.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.print.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.colVis.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.colReorder.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/datatables.js"></script>
    <?php include_once BASE_PATH . '/components/footer.php'; ?>
</body>
<script>
    $(document).ready(function () {
        $('.show-record-details').on('click', function (e) {
            e.preventDefault();
            var recordId = $(this).data('record-id');
            // Effettua una richiesta AJAX per ottenere i dettagli del record
            $.ajax({
                url: 'get_riparazione_details.php', // URL del file PHP che restituisce i dettagli del record
                type: 'GET',
                data: { id: recordId },
                success: function (response) {
                    $('#record-details').html(response);
                    $('#record-details-modal').modal('show');
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
        // Seleziona/Deseleziona tutte le righe
        $('#select-all').on('click', function () {
            $('.select-row').prop('checked', this.checked);
            $('.select-row').each(function () {
                toggleRowSelection($(this));
            });
            toggleActionButtons();
        });

        // Gestisci la selezione delle singole righe
        $('.select-row').on('change', function () {
            toggleRowSelection($(this));
            toggleActionButtons();
        });

        function toggleRowSelection(checkbox) {
            if (checkbox.is(':checked')) {
                checkbox.closest('tr').addClass('selected-row');
            } else {
                checkbox.closest('tr').removeClass('selected-row');
            }
        }

        function toggleActionButtons() {
            let selectedCount = $('.select-row:checked').length;
            if (selectedCount > 0) {
                $('#delete-selected, #print-selected').prop('disabled', false);
            } else {
                $('#delete-selected, #print-selected').prop('disabled', true);
            }
        }

        // Gestione per eliminare record selezionati
        $('#delete-selected').on('click', function () {
            let selectedIds = $('.select-row:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length > 0) {
                $.ajax({
                    url: 'delete_plus.php',
                    type: 'POST',
                    data: { ids: selectedIds },
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }
        });
        $('#print-selected').on('click', function () {
            let selectedIds = $('.select-row:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length > 0) {
                // Redirigi verso la pagina di preview con gli ID selezionati come parametro GET
                let idsParam = selectedIds.join(';');
                window.location.href = 'filePreview?ids=' + idsParam;
            }
        });

    });



</script>