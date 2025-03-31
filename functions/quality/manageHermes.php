<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include(BASE_PATH . "/components/header.php");
?>

<!-- Fallback jQuery loader -->
<script>
    if (typeof jQuery == 'undefined') {
        var script = document.createElement('script');
        script.src = '<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js';
        document.head.appendChild(script);
    }
</script>

<style>
    /* Custom tab styling */
    .nav-tabs {
        border-bottom: 1px solid #e3e6f0 !important;
    }

    .nav-tabs .nav-link {
        border: none !important;
        color: #6c757d !important;
        padding: 0.75rem 1.25rem !important;
        font-weight: 500 !important;
        font-size: 0.85rem !important;
        border-radius: 0 !important;
    }

    .nav-tabs .nav-link.active {
        color: #4e73df !important;
        background-color: transparent !important;
        border-bottom: 3px solid #4e73df !important;
        width: 100% !important;
    }

    .nav-tabs .nav-link:hover {
        color: #4e73df !important;
        border-bottom: 3px solid #e3e6f0 !important;
    }

    /* Stile per la sezione eccezioni */
    #recordExceptionsSection {
        margin-top: 20px;
    }
</style>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>

                <div class="container-fluid">
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">CQ Dashboard Hermes</h1>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Controllo Qualità</a></li>
                        <li class="breadcrumb-item active">Divisione Hermes</li>
                    </ol>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Divisione Hermes</h6>
                        </div>

                        <div class="card-body">
                            <!-- Navigation Tabs -->
                            <ul class="nav nav-tabs" id="hermesTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview"
                                        role="tab">
                                        <i class="fas fa-chart-pie mr-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="records-tab" data-toggle="tab" href="#records" role="tab">
                                        <i class="fas fa-clipboard-list mr-1"></i>Cartellini per Data
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="departments-tab" data-toggle="tab" href="#departments"
                                        role="tab">
                                        <i class="fas fa-building mr-1"></i>Reparti
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="defects-tab" data-toggle="tab" href="#defects" role="tab">
                                        <i class="fas fa-bug mr-1"></i>Tipi Difetti
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="reports-tab" data-toggle="tab" href="#reports" role="tab">
                                        <i class="fas fa-file-alt mr-1"></i>Reports
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content">
                                <!-- Dashboard/Overview Tab -->
                                <div class="tab-pane fade show active" id="overview" role="tabpanel"
                                    aria-labelledby="overview-tab">
                                    <!-- [Previous overview content remains the same] -->
                                    <div class="row mt-4">
                                        <!-- Dashboard stats cards -->
                                        <div class="col-xl-3 col-md-6 mb-4">
                                            <div class="card border-left-primary shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                Cartellini Totali</div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800"
                                                                id="total-records">Caricamento...</div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- [Other dashboard cards remain the same] -->
                                    </div>

                                    <!-- Charts section -->
                                    <div class="row">
                                        <!-- [Previous charts remain the same] -->
                                    </div>
                                </div>

                                <!-- Cartellini Tab -->
                                <div class="tab-pane fade" id="records" role="tabpanel" aria-labelledby="records-tab">
                                    <div
                                        class="card-header py-3 d-flex justify-content-between align-items-center mt-4">
                                        <h6 class="m-0 font-weight-bold text-primary">Gestione Cartellini</h6>
                                        <div>
                                            <div class="form-inline mr-2">
                                                <label for="recordDateFilter" class="mr-2">Data Controllo:</label>
                                                <input type="date" class="form-control mr-2" id="recordDateFilter">
                                                <button id="applyDateFilter" class="btn btn-primary mr-2">
                                                    <i class="fas fa-filter"></i> Filtra
                                                </button>
                                                <button id="clearDateFilter" class="btn btn-secondary mr-2">
                                                    <i class="fas fa-times"></i> Azzera
                                                </button>
                                            </div>
                                            <button class="btn btn-primary" data-toggle="modal"
                                                data-target="#addRecordModal">
                                                <i class="fas fa-plus"></i> Nuovo Cartellino
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive mt-3">
                                        <table class="table table-bordered" id="recordsDataTable" width="100%"
                                            cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Numero Cartellino</th>
                                                    <th>Reparto</th>
                                                    <th>Data Controllo</th>
                                                    <th>Operatore</th>
                                                    <th>Tipo CQ</th>
                                                    <th>Paia Totali</th>
                                                    <th>Cod. Articolo</th>
                                                    <th>Articolo</th>
                                                    <th>Linea</th>
                                                    <th>Eccezioni</th>
                                                    <th>Azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dati caricati dinamicamente -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Sezione Eccezioni per il Cartellino Selezionato -->
                                    <div id="recordExceptionsSection" style="display:none;">
                                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                            <h6 class="m-0 font-weight-bold text-primary">Eccezioni per Cartellino</h6>
                                            <button class="btn btn-primary" id="addExceptionBtn">
                                                <i class="fas fa-plus"></i> Nuova Eccezione
                                            </button>
                                        </div>
                                        <div class="table-responsive mt-3">
                                            <table class="table table-bordered" id="recordExceptionsTable" width="100%"
                                                cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Taglia</th>
                                                        <th>Tipo Difetto</th>
                                                        <th>Note Operatore</th>
                                                        <th>Foto</th>
                                                        <th>Data Creazione</th>
                                                        <th>Azioni</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Eccezioni caricate dinamicamente -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reparti e Tipi Difetti Tabs -->
                                <div class="tab-pane fade" id="departments" role="tabpanel"
                                    aria-labelledby="departments-tab">
                                    <!-- [Departments section remains the same] -->
                                </div>

                                <div class="tab-pane fade" id="defects" role="tabpanel" aria-labelledby="defects-tab">
                                    <!-- [Defects section remains the same] -->
                                </div>

                                <!-- Reports Tab -->
                                <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">
                                    <!-- [Reports section remains the same] -->
                                </div>
                            </div>

                            <!-- Modals -->
                            <!-- Modal Aggiungi Cartellino -->
                            <div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog"
                                aria-labelledby="addRecordModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addRecordModalLabel">Aggiungi Nuovo Cartellino
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addRecordForm">
                                                <!-- [Form fields remain the same as in previous version] -->
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary"
                                                id="saveRecordBtn">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Aggiungi Eccezione -->
                            <div class="modal fade" id="addExceptionModal" tabindex="-1" role="dialog"
                                aria-labelledby="addExceptionModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addExceptionModalLabel">Aggiungi Nuova Eccezione
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addExceptionForm" enctype="multipart/form-data">
                                                <div class="form-group">
                                                    <label for="cartellino_id">Cartellino*</label>
                                                    <select class="form-control" id="cartellino_id" name="cartellino_id"
                                                        required>
                                                        <!-- Opzioni caricate dinamicamente con JavaScript -->
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="taglia">Taglia*</label>
                                                    <input type="text" class="form-control" id="taglia" name="taglia"
                                                        required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="tipo_difetto">Tipo Difetto*</label>
                                                    <select class="form-control" id="tipo_difetto" name="tipo_difetto"
                                                        required>
                                                        <!-- Opzioni caricate dinamicamente con JavaScript -->
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="note_operatore">Note Operatore</label>
                                                    <textarea class="form-control" id="note_operatore"
                                                        name="note_operatore" rows="3"></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="fotoPath">Foto</label>
                                                    <input type="file" class="form-control-file" id="fotoPath"
                                                        name="fotoPath" accept="image/*">
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary"
                                                id="saveExceptionBtn">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Aggiungi Reparto -->
                            <div class="modal fade" id="addDepartmentModal" tabindex="-1" role="dialog"
                                aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addDepartmentModalLabel">Aggiungi Nuovo Reparto
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addDepartmentForm">
                                                <div class="form-group">
                                                    <label for="nome_reparto">Nome Reparto*</label>
                                                    <input type="text" class="form-control" id="nome_reparto"
                                                        name="nome_reparto" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ordine">Ordine</label>
                                                    <input type="number" class="form-control" id="ordine" name="ordine"
                                                        value="0" min="0">
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="attivo"
                                                        name="attivo" checked>
                                                    <label class="form-check-label" for="attivo">Attivo</label>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary"
                                                id="saveDepartmentBtn">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Aggiungi Tipo Difetto -->
                            <div class="modal fade" id="addDefectModal" tabindex="-1" role="dialog"
                                aria-labelledby="addDefectModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addDefectModalLabel">Aggiungi Nuovo Tipo Difetto
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addDefectForm">
                                                <div class="form-group">
                                                    <label for="descrizione">Descrizione*</label>
                                                    <input type="text" class="form-control" id="descrizione"
                                                        name="descrizione" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="categoria">Categoria</label>
                                                    <input type="text" class="form-control" id="categoria"
                                                        name="categoria" list="categorie">
                                                    <datalist id="categorie">
                                                        <option value="CUCITURE">
                                                        <option value="MATERIALE">
                                                        <option value="FINITURE">
                                                    </datalist>
                                                </div>
                                                <div class="form-group">
                                                    <label for="defect_ordine">Ordine</label>
                                                    <input type="number" class="form-control" id="defect_ordine"
                                                        name="ordine" value="0" min="0">
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="defect_attivo"
                                                        name="attivo" checked>
                                                    <label class="form-check-label" for="defect_attivo">Attivo</label>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary"
                                                id="saveDefectBtn">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Visualizza Foto -->
                            <div class="modal fade" id="viewPhotoModal" tabindex="-1" role="dialog"
                                aria-labelledby="viewPhotoModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewPhotoModalLabel">Visualizza Foto</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img id="exceptionPhoto" src="" class="img-fluid" alt="Foto eccezione">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Chiudi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include(BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>

    <!-- Essential Scripts -->
    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>

    <!-- DataTables Core -->
    <script src="<?php echo BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.bootstrap4.min.js"></script>

    <!-- Buttons Dependencies -->
    <script src="<?php echo BASE_URL ?>/vendor/jszip/jszip.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/pdfmake.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/vfs_fonts.js"></script>

    <!-- Chart.js -->
    <script src="<?php echo BASE_URL ?>/vendor/chart.js/Chart.min.js"></script>

    <!-- DataTables Extensions -->
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.html5.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.print.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.colVis.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.colReorder.min.js"></script>

    <!-- Custom Scripts -->
    <script src="<?php echo BASE_URL ?>/js/datatables.js"></script>

    <!-- Custom JavaScript for Hermes Dashboard -->
    <script>
        function initDataTables() {
            // DataTable for Records
            var recordsTable = $('#recordsDataTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_records.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'numero_cartellino' },
                    { data: 'reparto' },
                    { data: 'data_controllo' },
                    { data: 'operatore' },
                    { data: 'tipo_cq' },
                    { data: 'paia_totali' },
                    { data: 'cod_articolo' },
                    { data: 'articolo' },
                    { data: 'linea' },
                    {
                        data: 'ha_eccezioni',
                        render: function (data, type, row) {
                            return data == 1
                                ? `<span class="badge badge-warning" data-record-id="${row.id}">Sì (${row.eccezioni_count})</span>`
                                : '<span class="badge badge-success">No</span>';
                        }
                    },
                    {
                        data: null,
                        render: function (data) {
                            return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info edit-record" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger delete-record" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                        }
                    }
                ],
                order: [[3, 'desc']]
            });

            // Date filter for Records
            $('#applyDateFilter').on('click', function () {
                var selectedDate = $('#recordDateFilter').val();
                recordsTable.column(3).search(selectedDate).draw();
            });

            // Clear date filter
            $('#clearDateFilter').on('click', function () {
                $('#recordDateFilter').val('');
                recordsTable.column(3).search('').draw();
            });

            // Event listener for showing record exceptions
            $('#recordsDataTable').on('click', '.badge-warning', function () {
                var recordId = $(this).data('record-id');
                loadRecordExceptions(recordId);
            });

            // Departments DataTable
            $('#departmentsDataTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_departments.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'nome_reparto' },
                    {
                        data: 'attivo',
                        render: function (data) {
                            return data == 1
                                ? '<span class="badge badge-success">Attivo</span>'
                                : '<span class="badge badge-danger">Inattivo</span>';
                        }
                    },
                    { data: 'ordine' },
                    { data: 'data_creazione' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info edit-department" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger delete-department" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                        }
                    }
                ],
                order: [[3, 'asc']]
            });

            // Defects DataTable
            $('#defectsDataTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_defects.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'descrizione' },
                    { data: 'categoria' },
                    {
                        data: 'attivo',
                        render: function (data) {
                            return data == 1
                                ? '<span class="badge badge-success">Attivo</span>'
                                : '<span class="badge badge-danger">Inattivo</span>';
                        }
                    },
                    { data: 'ordine' },
                    { data: 'data_creazione' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info edit-defect" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger delete-defect" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                        }
                    }
                ],
                order: [[4, 'asc']]
            });
        }

        function loadRecordExceptions(recordId) {
            // Show exceptions section
            $('#recordExceptionsSection').show();

            // Destroy existing DataTable instance if exists
            if ($.fn.DataTable.isDataTable('#recordExceptionsTable')) {
                $('#recordExceptionsTable').DataTable().destroy();
            }

            // Load exceptions for specific record
            $('#recordExceptionsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_record_exceptions.php',
                    type: 'GET',
                    data: { record_id: recordId },
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'taglia' },
                    { data: 'tipo_difetto' },
                    { data: 'note_operatore' },
                    {
                        data: 'fotoPath',
                        render: function (data) {
                            if (data) {
                                return `<button class="btn btn-sm btn-info view-photo" data-path="<?php echo BASE_URL; ?>/${data}"><i class="fas fa-image"></i> Visualizza</button>`;
                            } else {
                                return '<span class="badge badge-secondary">No foto</span>';
                            }
                        }
                    },
                    { data: 'data_creazione' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info edit-exception" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger delete-exception" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                        }
                    }
                ],
                order: [[5, 'desc']]
            });
        }

        function loadDashboardData() {
            // Load dashboard statistics
            $.ajax({
                url: 'hermes/get_dashboard_stats.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#total-records').text(data.total_records);
                    $('#today-records').text(data.today_records);
                    $('#total-exceptions').text(data.total_exceptions);
                    $('#today-exceptions').text(data.today_exceptions);
                },
                error: function (xhr, status, error) {
                    console.error('Errore nel caricamento delle statistiche dashboard:', error);
                }
            });
        }

        function loadSelectOptions() {
            // Load department options
            $.ajax({
                url: 'hermes/get_departments.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var options = '';
                    $.each(data, function (index, department) {
                        if (department.attivo == 1) {
                            options += `<option value="${department.nome_reparto}">${department.nome_reparto}</option>`;
                        }
                    });
                    $('#reparto').html(options);
                }
            });

            // Load record options for exceptions
            $.ajax({
                url: 'hermes/get_records.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var options = '';
                    $.each(data, function (index, record) {
                        options += `<option value="${record.id}">${record.numero_cartellino} - ${record.articolo}</option>`;
                    });
                    $('#cartellino_id').html(options);
                }
            });

            // Load defect type options
            $.ajax({
                url: 'hermes/get_defects.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var options = '';
                    $.each(data, function (index, defect) {
                        if (defect.attivo == 1) {
                            options += `<option value="${defect.descrizione}">${defect.descrizione}</option>`;
                        }
                    });
                    $('#tipo_difetto').html(options);
                }
            });
        }

        function saveRecord() {
            var formData = new FormData($('#addRecordForm')[0]);

            $.ajax({
                url: 'hermes/save_record.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#addRecordModal').modal('hide');
                    $('#recordsDataTable').DataTable().ajax.reload();
                    showAlert('success', 'Cartellino salvato con successo');
                    $('#addRecordForm')[0].reset();
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel salvataggio del cartellino: ' + error);
                }
            });
        }

        function saveException() {
            var formData = new FormData($('#addExceptionForm')[0]);

            $.ajax({
                url: 'hermes/save_exception.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#addExceptionModal').modal('hide');
                    $('#recordsDataTable').DataTable().ajax.reload();

                    // Reload exceptions if a record is currently selected
                    var currentRecordId = $('#recordExceptionsTable').data('current-record-id');
                    if (currentRecordId) {
                        loadRecordExceptions(currentRecordId);
                    }

                    showAlert('success', 'Eccezione salvata con successo');
                    $('#addExceptionForm')[0].reset();
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel salvataggio dell\'eccezione: ' + error);
                }
            });
        }

        function saveDepartment() {
            var formData = new FormData($('#addDepartmentForm')[0]);
            formData.append('attivo', $('#attivo').is(':checked') ? 1 : 0);

            $.ajax({
                url: 'hermes/save_department.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#addDepartmentModal').modal('hide');
                    $('#departmentsDataTable').DataTable().ajax.reload();
                    loadSelectOptions();
                    showAlert('success', 'Reparto salvato con successo');
                    $('#addDepartmentForm')[0].reset();
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel salvataggio del reparto: ' + error);
                }
            });
        }

        function saveDefect() {
            var formData = new FormData($('#addDefectForm')[0]);
            formData.append('attivo', $('#defect_attivo').is(':checked') ? 1 : 0);

            $.ajax({
                url: 'hermes/save_defect.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#addDefectModal').modal('hide');
                    $('#defectsDataTable').DataTable().ajax.reload();
                    loadSelectOptions();
                    showAlert('success', 'Tipo difetto salvato con successo');
                    $('#addDefectForm')[0].reset();
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel salvataggio del tipo difetto: ' + error);
                }
            });
        }

        function editRecord(id) {
            $.ajax({
                url: 'hermes/get_record.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    $('#addRecordModalLabel').text('Modifica Cartellino');
                    $('#numero_cartellino').val(data.numero_cartellino);
                    $('#reparto').val(data.reparto);
                    $('#operatore').val(data.operatore);
                    $('#tipo_cq').val(data.tipo_cq);
                    $('#paia_totali').val(data.paia_totali);
                    $('#cod_articolo').val(data.cod_articolo);
                    $('#articolo').val(data.articolo);
                    $('#linea').val(data.linea);
                    $('#note').val(data.note);

                    // Add hidden ID for update
                    if ($('#record_id').length === 0) {
                        $('#addRecordForm').append('<input type="hidden" id="record_id" name="id" value="' + id + '">');
                    } else {
                        $('#record_id').val(id);
                    }

                    $('#addRecordModal').modal('show');
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel caricamento dei dati del cartellino: ' + error);
                }
            });
        }

        function editException(id) {
            $.ajax({
                url: 'hermes/get_exception.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    $('#addExceptionModalLabel').text('Modifica Eccezione');
                    $('#cartellino_id').val(data.cartellino_id);
                    $('#taglia').val(data.taglia);
                    $('#tipo_difetto').val(data.tipo_difetto);
                    $('#note_operatore').val(data.note_operatore);

                    // Add hidden ID for update
                    if ($('#exception_id').length === 0) {
                        $('#addExceptionForm').append('<input type="hidden" id="exception_id" name="id" value="' + id + '">');
                    } else {
                        $('#exception_id').val(id);
                    }

                    $('#addExceptionModal').modal('show');
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel caricamento dei dati dell\'eccezione: ' + error);
                }
            });
        }

        function editDepartment(id) {
            $.ajax({
                url: 'hermes/get_department.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    $('#addDepartmentModalLabel').text('Modifica Reparto');
                    $('#nome_reparto').val(data.nome_reparto);
                    $('#ordine').val(data.ordine);
                    $('#attivo').prop('checked', data.attivo == 1);

                    // Add hidden ID for update
                    if ($('#department_id').length === 0) {
                        $('#addDepartmentForm').append('<input type="hidden" id="department_id" name="id" value="' + id + '">');
                    } else {
                        $('#department_id').val(id);
                    }

                    $('#addDepartmentModal').modal('show');
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel caricamento dei dati del reparto: ' + error);
                }
            });
        }

        function editDefect(id) {
            $.ajax({
                url: 'hermes/get_defect.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    $('#addDefectModalLabel').text('Modifica Tipo Difetto');
                    $('#descrizione').val(data.descrizione);
                    $('#categoria').val(data.categoria);
                    $('#defect_ordine').val(data.ordine);
                    $('#defect_attivo').prop('checked', data.attivo == 1);

                    // Add hidden ID for update
                    if ($('#defect_id').length === 0) {
                        $('#addDefectForm').append('<input type="hidden" id="defect_id" name="id" value="' + id + '">');
                    } else {
                        $('#defect_id').val(id);
                    }

                    $('#addDefectModal').modal('show');
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel caricamento dei dati del tipo difetto: ' + error);
                }
            });
        }

        function deleteRecord(id) {
            if (confirm('Sei sicuro di voler eliminare questo cartellino? Questa azione non può essere annullata.')) {
                $.ajax({
                    url: 'hermes/delete_record.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        $('#recordsDataTable').DataTable().ajax.reload();
                        showAlert('success', 'Cartellino eliminato con successo');
                    },
                    error: function (xhr, status, error) {
                        showAlert('danger', 'Errore nell\'eliminazione del cartellino: ' + error);
                    }
                });
            }
        }

        function deleteException(id) {
            if (confirm('Sei sicuro di voler eliminare questa eccezione? Questa azione non può essere annullata.')) {
                $.ajax({
                    url: 'hermes/delete_exception.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        $('#recordsDataTable').DataTable().ajax.reload();

                        // Reload exceptions if a record is currently selected
                        var currentRecordId = $('#recordExceptionsTable').data('current-record-id');
                        if (currentRecordId) {
                            loadRecordExceptions(currentRecordId);
                        }

                        showAlert('success', 'Eccezione eliminata con successo');
                    },
                    error: function (xhr, status, error) {
                        showAlert('danger', 'Errore nell\'eliminazione dell\'eccezione: ' + error);
                    }
                });
            }
        }

        function deleteDepartment(id) {
            if (confirm('Sei sicuro di voler eliminare questo reparto? Questa azione non può essere annullata.')) {
                $.ajax({
                    url: 'hermes/delete_department.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        $('#departmentsDataTable').DataTable().ajax.reload();
                        loadSelectOptions();
                        showAlert('success', 'Reparto eliminato con successo');
                    },
                    error: function (xhr, status, error) {
                        showAlert('danger', 'Errore nell\'eliminazione del reparto: ' + error);
                    }
                });
            }
        }

        function deleteDefect(id) {
            if (confirm('Sei sicuro di voler eliminare questo tipo difetto? Questa azione non può essere annullata.')) {
                $.ajax({
                    url: 'hermes/delete_defect.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        $('#defectsDataTable').DataTable().ajax.reload();
                        loadSelectOptions();
                        showAlert('success', 'Tipo difetto eliminato con successo');
                    },
                    error: function (xhr, status, error) {
                        showAlert('danger', 'Errore nell\'eliminazione del tipo difetto: ' + error);
                    }
                });
            }
        }

        function initCharts() {
            // Weekly control chart
            $.ajax({
                url: 'hermes/get_weekly_data.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var ctx = document.getElementById('weeklyCQChart').getContext('2d');
                    var chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Controlli Totali',
                                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                    borderColor: 'rgba(78, 115, 223, 1)',
                                    pointRadius: 3,
                                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                                    pointBorderColor: 'rgba(78, 115, 223, 1)',
                                    pointHoverRadius: 3,
                                    pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                                    pointHoverBorderColor: 'rgba(78, 115, 223,1)',
                                    pointHitRadius: 10,
                                    pointBorderWidth: 2,
                                    data: data.countRecords
                                },
                                {
                                    label: 'Eccezioni',
                                    backgroundColor: 'rgba(231, 74, 59, 0.05)',
                                    borderColor: 'rgba(231, 74, 59, 1)',
                                    pointRadius: 3,
                                    pointBackgroundColor: 'rgba(231, 74, 59, 1)',
                                    pointBorderColor: 'rgba(231, 74, 59, 1)',
                                    pointHoverRadius: 3,
                                    pointHoverBackgroundColor: 'rgba(231, 74, 59, 1)',
                                    pointHoverBorderColor: 'rgba(231, 74, 59, 1)',
                                    pointHitRadius: 10,
                                    pointBorderWidth: 2,
                                    data: data.countExceptions
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    left: 10,
                                    right: 25,
                                    top: 25,
                                    bottom: 0
                                }
                            },
                            scales: {
                                xAxes: [{
                                    time: {
                                        unit: 'day'
                                    },
                                    gridLines: {
                                        display: false,
                                        drawBorder: false
                                    },
                                    ticks: {
                                        maxTicksLimit: 7
                                    }
                                }],
                                yAxes: [{
                                    ticks: {
                                        maxTicksLimit: 5,
                                        padding: 10,
                                        beginAtZero: true
                                    },
                                    gridLines: {
                                        color: "rgb(234, 236, 244)",
                                        zeroLineColor: "rgb(234, 236, 244)",
                                        drawBorder: false,
                                        borderDash: [2],
                                        zeroLineBorderDash: [2]
                                    }
                                }],
                            },
                            legend: {
                                display: true
                            },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                titleMarginBottom: 10,
                                titleFontColor: '#6e707e',
                                titleFontSize: 14,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10
                            }
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Errore nel caricamento dei dati settimanali:', error);
                }
            });

            // Defect types pie chart
            $.ajax({
                url: 'hermes/get_defects_stats.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var ctx = document.getElementById('defectsPieChart').getContext('2d');
                    var chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.counts,
                                backgroundColor: [
                                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                                    '#e74a3b', '#5a5c69', '#858796'
                                ],
                                hoverBackgroundColor: [
                                    '#2e59d9', '#17a673', '#2c9faf', '#dda20a',
                                    '#be2617', '#3a3b45', '#60616f'
                                ],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                caretPadding: 10,
                            },
                            legend: {
                                display: true,
                                position: 'bottom'
                            },
                            cutoutPercentage: 70,
                        },
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Errore nel caricamento delle statistiche difetti:', error);
                }
            });
        }

        function showAlert(type, message) {
            var alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

            // Add alert above content and set timer to remove it
            $('.container-fluid').prepend(alertHtml);

            // Remove alert after 5 seconds
            setTimeout(function () {
                $('.alert').alert('close');
            }, 5000);
        }

        // Document ready function
        $(document).ready(function () {
            // Initialize DataTables
            initDataTables();

            // Load dashboard data
            loadDashboardData();

            // Load select options for forms
            loadSelectOptions();

            // Initialize charts
            initCharts();

            // Event listeners for save buttons
            $('#saveRecordBtn').on('click', saveRecord);
            $('#saveExceptionBtn').on('click', saveException);
            $('#saveDepartmentBtn').on('click', saveDepartment);
            $('#saveDefectBtn').on('click', saveDefect);

            // Event listeners for edit buttons in tables
            $('#recordsDataTable').on('click', '.edit-record', function () {
                var id = $(this).data('id');
                editRecord(id);
            });

            $('#recordExceptionsTable').on('click', '.edit-exception', function () {
                var id = $(this).data('id');
                editException(id);
            });

            $('#departmentsDataTable').on('click', '.edit-department', function () {
                var id = $(this).data('id');
                editDepartment(id);
            });

            $('#defectsDataTable').on('click', '.edit-defect', function () {
                var id = $(this).data('id');
                editDefect(id);
            });

            // Event listeners for delete buttons in tables
            $('#recordsDataTable').on('click', '.delete-record', function () {
                var id = $(this).data('id');
                deleteRecord(id);
            });

            $('#recordExceptionsTable').on('click', '.delete-exception', function () {
                var id = $(this).data('id');
                deleteException(id);
            });

            $('#departmentsDataTable').on('click', '.delete-department', function () {
                var id = $(this).data('id');
                deleteDepartment(id);
            });

            $('#defectsDataTable').on('click', '.delete-defect', function () {
                var id = $(this).data('id');
                deleteDefect(id);
            });

            // Photo view event listener
            $('#recordExceptionsTable').on('click', '.view-photo', function () {
                var path = $(this).data('path');
                $('#exceptionPhoto').attr('src', path);
                $('#viewPhotoModal').modal('show');
            });

            // Add exception button for record exceptions
            $('#addExceptionBtn').on('click', function () {
                var currentRecordId = $('#recordExceptionsTable').data('current-record-id');
                if (currentRecordId) {
                    $('#cartellino_id').val(currentRecordId);
                    $('#addExceptionModal').modal('show');
                } else {
                    showAlert('warning', 'Seleziona prima un cartellino');
                }
            });
        });
    </script>
</body>
