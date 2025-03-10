<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Database Manager</h1>
                        <div>
                            <a href="#" class="btn btn-sm btn-primary shadow-sm mr-2" id="backupDb">
                                <i class="fas fa-download fa-sm text-white-50"></i> Backup DB
                            </a>
                            <a href="#" class="btn btn-sm btn-info shadow-sm" id="refreshStructure">
                                <i class="fas fa-sync fa-sm text-white-50"></i> Aggiorna
                            </a>
                        </div>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Gestione Database</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-3 col-lg-3">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Tabelle</h6>
                                    <a href="sql" class="btn btn-primary btn-sm">
                                        <i class="fas fa-terminal mr-1"></i>Console
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="tableSearch"
                                            placeholder="Cerca tabella...">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="clearSearch"
                                                title="Pulisci ricerca">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-list-container">
                                        <ul id="tables-list" class="list-group"></ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Informazioni Database</h6>
                                </div>
                                <div class="card-body">
                                    <div id="db-info">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-database mr-2"></i>DB:</span>
                                            <span class="font-weight-bold" id="db-name"></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-server mr-2"></i>MySQL:</span>
                                            <span id="mysql-version"></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-table mr-2"></i>Tabelle:</span>
                                            <span id="total-tables"></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span><i class="fas fa-hdd mr-2"></i>Dimensione:</span>
                                            <span id="total-size"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9 col-lg-9">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <span id="current-table-name">Seleziona una tabella</span>
                                    </h6>
                                    <div class="ml-auto">
                                        <button type="button" id="addRecordBtn" class="btn btn-success btn-sm d-none">
                                            <i class="fas fa-plus"></i> Nuovo record
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="nav nav-tabs" id="dbTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="browse-tab" data-toggle="tab" href="#browse"
                                                role="tab">
                                                <i class="fas fa-table mr-1"></i>Dati
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="structure-tab" data-toggle="tab" href="#structure"
                                                role="tab">
                                                <i class="fas fa-database mr-1"></i>Struttura
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="operations-tab" data-toggle="tab" href="#operations"
                                                role="tab">
                                                <i class="fas fa-tools mr-1"></i>Operazioni
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content p-3" id="tabContent">
                                        <div class="tab-pane fade show active" id="browse" role="tabpanel">
                                            <div id="table-data" class="table-responsive">
                                                <div class="text-center py-5 text-muted">
                                                    <i class="fas fa-table fa-3x mb-3"></i>
                                                    <p>Seleziona una tabella dalla lista per visualizzare i dati</p>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between mt-3 table-pagination"
                                                style="display: none !important;">
                                                <div class="table-info">
                                                    <span id="showing-records">Mostro 0 record</span>
                                                </div>
                                                <div class="table-pagination-controls">
                                                    <button class="btn btn-sm btn-outline-primary" id="prev-page"
                                                        disabled>
                                                        <i class="fas fa-chevron-left"></i> Precedente
                                                    </button>
                                                    <span class="mx-3" id="page-info">Pagina 1 di 1</span>
                                                    <button class="btn btn-sm btn-outline-primary" id="next-page"
                                                        disabled>
                                                        Successiva <i class="fas fa-chevron-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="structure" role="tabpanel">
                                            <div id="table-structure" class="table-responsive">
                                                <div class="text-center py-5 text-muted">
                                                    <i class="fas fa-database fa-3x mb-3"></i>
                                                    <p>Seleziona una tabella dalla lista per visualizzare la struttura
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="operations" role="tabpanel">
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="card border-primary mb-3">
                                                        <div class="card-header bg-primary text-white">
                                                            <i class="fas fa-wrench mr-1"></i>Manutenzione
                                                        </div>
                                                        <div class="card-body">
                                                            <button id="optimizeTableBtn"
                                                                class="btn btn-outline-primary btn-block mb-2">
                                                                <i class="fas fa-bolt mr-1"></i>Ottimizza tabella
                                                            </button>
                                                            <button id="repairTableBtn"
                                                                class="btn btn-outline-primary btn-block mb-2">
                                                                <i class="fas fa-wrench mr-1"></i>Ripara tabella
                                                            </button>
                                                            <button id="truncateTableBtn"
                                                                class="btn btn-outline-danger btn-block">
                                                                <i class="fas fa-trash-alt mr-1"></i>Svuota tabella
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card border-info mb-3">
                                                        <div class="card-header bg-info text-white">
                                                            <i class="fas fa-file-export mr-1"></i>Esportazione
                                                        </div>
                                                        <div class="card-body">
                                                            <button id="exportCSVBtn"
                                                                class="btn btn-outline-info btn-block mb-2">
                                                                <i class="fas fa-file-csv mr-1"></i>Esporta come CSV
                                                            </button>
                                                            <button id="exportSQLBtn"
                                                                class="btn btn-outline-info btn-block mb-2">
                                                                <i class="fas fa-file-code mr-1"></i>Esporta come SQL
                                                            </button>
                                                            <button id="exportJSONBtn"
                                                                class="btn btn-outline-info btn-block">
                                                                <i class="fas fa-file-code mr-1"></i>Esporta come JSON
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SQL Modal -->
                    <div class="modal fade" id="sqlModal" tabindex="-1" role="dialog" aria-labelledby="sqlModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="sqlModalLabel">
                                        <i class="fas fa-terminal mr-2"></i>SQL Console
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="btn-group btn-group-sm mb-3" role="group">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="insertText('SELECT * FROM ')">SELECT *</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="insertText('INSERT INTO ')">INSERT INTO</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="insertText('UPDATE ')">UPDATE</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="insertText('DELETE FROM ')">DELETE FROM</button>
                                        <button type="button" class="btn btn-info"
                                            onclick="insertText(' WHERE ')">WHERE</button>
                                        <button type="button" class="btn btn-info"
                                            onclick="insertText(' ORDER BY ')">ORDER BY</button>
                                        <button type="button" class="btn btn-info"
                                            onclick="insertText(' LIMIT ')">LIMIT</button>
                                    </div>
                                    <div class="form-group">
                                        <select id="savedQueries" class="form-control mb-2">
                                            <option value="">-- Query salvate --</option>
                                        </select>
                                    </div>
                                    <textarea id="sqlQuery" class="form-control" rows="10"
                                        placeholder="Scrivi l'SQL qui..."
                                        style="font-family: 'Consolas', monospace; font-size: 14px; line-height: 1.5; background-color: #f8f9fc;"></textarea>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="saveQuery">
                                        <label class="form-check-label" for="saveQuery">
                                            Salva questa query
                                        </label>
                                    </div>
                                    <div id="sqlResult" class="mt-3 border-top pt-3"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                                    <button type="button" id="clearSql" class="btn btn-warning">
                                        <i class="fas fa-eraser mr-1"></i> Pulisci
                                    </button>
                                    <button type="button" id="executeSql" class="btn btn-success">
                                        <i class="fas fa-play mr-1"></i> Esegui
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Record Modal -->
                    <div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-plus-circle mr-2"></i>Aggiungi nuovo record
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="addRecordForm">
                                        <div id="formFields"></div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Annulla</button>
                                    <button type="button" class="btn btn-primary" id="saveRecordBtn">
                                        <i class="fas fa-save mr-1"></i> Salva
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Record Modal -->
                    <div class="modal fade" id="editRecordModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit mr-2"></i>Modifica record
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editRecordForm">
                                        <input type="hidden" id="editRecordId" name="id">
                                        <div id="editFormFields"></div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Annulla</button>
                                    <button type="button" class="btn btn-primary" id="updateRecordBtn">
                                        <i class="fas fa-save mr-1"></i> Aggiorna
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Modal -->
                    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>Conferma operazione
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" id="confirmModalBody">
                                    Sei sicuro di voler procedere con questa operazione?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Annulla</button>
                                    <button type="button" class="btn btn-danger" id="confirmActionBtn">Conferma</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
            <?php include(BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>

    <style>
        /* Migliorare lista tabelle */
        .table-list-container {
            max-height: 400px !important;
            overflow-y: auto !important;
            border: 1px solid #e3e6f0 !important;
            border-radius: 4px !important;
        }

        .table-item {
            cursor: pointer !important;
            transition: all 0.2s !important;
            border-radius: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            padding: 10px 15px !important;
            font-size: 0.9rem !important;
            border-bottom: 1px solid #f1f1f1 !important;
        }

        .table-item:first-child {
            border-top: 0 !important;
        }

        .table-item:last-child {
            border-bottom: 0 !important;
        }

        .table-item:hover {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
        }

        .table-item.active {
            background-color: #4e73df !important;
            color: white !important;
            width: 100% !important;
        }

        /* Personalizzazione tab */
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
            width:  100% !important;
        }

        .nav-tabs .nav-link:hover {
            color: #4e73df !important;
            border-bottom: 3px solid #e3e6f0 !important;
        }

        /* Migliorare tabelle dati */
        .table-custom {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 0.9rem !important;
        }

        .table-custom th {
            background-color: #f8f9fc!important;
            font-weight: 600 !important;
            text-align: left !important;
            padding: 12px 10px !important;
            border-bottom: 2px solid #e3e6f0 !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 10 !important;
        }

        .table-custom td {
            padding: 10px !important ;
            border-bottom: 1px solid #e3e6f0 !important;
            vertical-align: middle !important;
        }

        .table-custom tbody tr:hover {
            background-color: #f8f9fc !important;
        }

        .table-custom th:first-child,
        .table-custom td:first-child {
            padding-left: 15px !important;
        }

        .table-custom th:last-child,
        .table-custom td:last-child {
            padding-right: 15px !important;
        }

        /* Stile per console SQL */
        #sqlQuery {
            font-family: 'Consolas', 'Monaco', 'Menlo', 'Ubuntu Mono', monospace !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            resize: vertical !important;
        }

        /* Pulsanti di azione */
        .btn-circle {
            border-radius: 100% !important;
            width: 32px !important;
            height: 32px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Paginazione personalizzata */
        .table-pagination {
            margin-top: 15px !important;
            padding-top: 15px !important;
            border-top: 1px solid #e3e6f0 !important;
        }

        /* Stile badge personalizzati */
        .type-badge {
            font-size: 85% !important;
            padding: 0.25em 0.6em !important;
            border-radius: 3px !important;
            font-weight: 500 !important;
        }

        .type-int {
            background-color: #cfe3ff !important;
            color: #1e429f !important;
        }

        .type-varchar {
            background-color: #d1f5ea !important;
            color: #0d6e4e !important;
        }

        .type-text {
            background-color: #ffefd1 !important;
            color: #a16207 !important;
        }

        .type-date {
            background-color: #d6f5d6 !important;
            color: #16953c !important;
        }

        .type-other {
            background-color: #e9ecef !important;
            color: #495057 !important;
        }

        .key-badge {
            font-size: 85% !important;
            padding: 0.25em 0.6em !important;
            border-radius: 3px !important;
            font-weight: 500 !important;
        }

        .key-primary {
            background-color: #ffdddd !important;
            color: #e62e2e !important;
        }

        .key-unique {
            background-color: #fff1cc !important;
            color: #cc8800 !important;
        }

        .key-index {
            background-color: #def !important;
            color: #07c !important;
        }
    </style>
    <script>
        let currentTable = '';
        let tableStructure = [];
        let currentData = [];
        let currentPage = 1;
        let rowsPerPage = 15;
        let totalPages = 1;

        function insertText(text) {
            var textarea = document.getElementById('sqlQuery');
            var cursorPos = textarea.selectionStart;
            var textBefore = textarea.value.substring(0, cursorPos);
            var textAfter = textarea.value.substring(cursorPos, textarea.value.length);
            textarea.value = textBefore + text + textAfter;
            textarea.focus();
            textarea.setSelectionRange(cursorPos + text.length, cursorPos + text.length);
        }

        function showAlert(message, type = 'success') {
            const alertDiv = $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`);

            alertDiv.prependTo('.container-fluid');

            setTimeout(() => {
                alertDiv.alert('close');
            }, 5000);
        }

        function confirmAction(message, callback) {
            $('#confirmModalBody').text(message);
            $('#confirmActionBtn').off('click').on('click', function () {
                callback();
                $('#confirmModal').modal('hide');
            });
            $('#confirmModal').modal('show');
        }

        function loadDatabaseInfo() {
            $.ajax({
                url: 'get_db_info.php',
                method: 'GET',
                success: function (response) {
                    try {
                        const info = JSON.parse(response);
                        if (info.error) {
                            console.error("Error fetching database info:", info.error);
                            return;
                        }

                        $('#db-name').text(info.dbName);
                        $('#mysql-version').text(info.mysqlVersion);
                        $('#total-tables').text(info.totalTables);
                        $('#total-size').text(info.totalSize);
                    } catch (e) {
                        console.error("Failed to parse database info response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching database info.");
                }
            });
        }

        function loadTableStructure(tableName) {
            $.ajax({
                url: 'get_table_structure.php',
                method: 'GET',
                data: { table: tableName },
                success: function (response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.error) {
                            console.error("Error fetching table structure:", data.error);
                            $('#table-structure').html(`<p class="text-danger">${data.error}</p>`);
                            return;
                        }

                        tableStructure = data;

                        let tableHtml = '<table class="table-custom"><thead><tr>' +
                            '<th>Campo</th>' +
                            '<th>Tipo</th>' +
                            '<th>Null</th>' +
                            '<th>Chiave</th>' +
                            '<th>Default</th>' +
                            '<th>Extra</th>' +
                            '</tr></thead><tbody>';

                        data.forEach(column => {
                            // Determine type badge class
                            let typeBadgeClass = 'type-other';
                            if (column.Type.includes('int')) {
                                typeBadgeClass = 'type-int';
                            } else if (column.Type.includes('varchar')) {
                                typeBadgeClass = 'type-varchar';
                            } else if (column.Type.includes('text')) {
                                typeBadgeClass = 'type-text';
                            } else if (column.Type.includes('date')) {
                                typeBadgeClass = 'type-date';
                            }

                            // Determine key badge class
                            let keyBadgeHtml = '';
                            if (column.Key === 'PRI') {
                                keyBadgeHtml = '<span class="key-badge key-primary">PRIMARY</span>';
                            } else if (column.Key === 'UNI') {
                                keyBadgeHtml = '<span class="key-badge key-unique">UNIQUE</span>';
                            } else if (column.Key === 'MUL') {
                                keyBadgeHtml = '<span class="key-badge key-index">INDEX</span>';
                            }

                            tableHtml += '<tr>' +
                                `<td><code>${column.Field}</code></td>` +
                                `<td><span class="type-badge ${typeBadgeClass}">${column.Type}</span></td>` +
                                `<td>${column.Null === 'YES' ? '<span class="text-success">SI</span>' : '<span class="text-danger">NO</span>'}</td>` +
                                `<td>${keyBadgeHtml}</td>` +
                                `<td>${column.Default === null ? '<em class="text-muted">NULL</em>' : column.Default}</td>` +
                                `<td>${column.Extra ? `<small class="font-weight-bold">${column.Extra}</small>` : ''}</td>` +
                                '</tr>';
                        });

                        tableHtml += '</tbody></table>';
                        $('#table-structure').html(tableHtml);
                    } catch (e) {
                        console.error("Failed to parse table structure response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching table structure.");
                }
            });
        }

        function renderTableData(page = 1) {
            if (!currentData || currentData.length === 0) {
                return;
            }

            const start = (page - 1) * rowsPerPage;
            const end = Math.min(start + rowsPerPage, currentData.length);
            const pageData = currentData.slice(start, end);

            if (pageData.length === 0) {
                $('#table-data').html('<div class="alert alert-info">Nessun dato nella tabella</div>');
                $('.table-pagination').hide();
                return;
            }

            const headers = Object.keys(currentData[0]);

            let tableHtml = '<table class="table-custom"><thead><tr>';
            tableHtml += '<th style="width: 80px;" class="text-center">Azioni</th>';

            headers.forEach(header => {
                tableHtml += `<th>${header}</th>`;
            });

            tableHtml += '</tr></thead><tbody>';

            pageData.forEach(row => {
                tableHtml += '<tr>';

                // Add action buttons
                tableHtml += '<td class="text-center">' +
                    `<button class="btn btn-sm btn-light text-primary btn-circle edit-row-btn" data-id="${row[headers[0]]}" title="Modifica"><i class="fas fa-edit"></i></button> ` +
                    `<button class="btn btn-sm btn-light text-danger btn-circle delete-row-btn" data-id="${row[headers[0]]}" title="Elimina"><i class="fas fa-trash"></i></button>` +
                    '</td>';

                headers.forEach(header => {
                    const value = row[header] === null ? '<em class="text-muted">NULL</em>' : row[header];
                    tableHtml += `<td>${value}</td>`;
                });

                tableHtml += '</tr>';
            });

            tableHtml += '</tbody></table>';

            $('#table-data').html(tableHtml);
            $('#showing-records').text(`Mostro ${start + 1}-${end} di ${currentData.length} record`);
            $('#page-info').text(`Pagina ${page} di ${totalPages}`);

            // Update pagination buttons state
            $('#prev-page').prop('disabled', page === 1);
            $('#next-page').prop('disabled', page >= totalPages);

            // Show pagination controls
            $('.table-pagination').show();

            currentPage = page;
        }

        function generateAddRecordForm(tableName) {
            $.ajax({
                url: 'get_table_structure.php',
                method: 'GET',
                data: { table: tableName },
                success: function (response) {
                    try {
                        const columns = JSON.parse(response);
                        if (columns.error) {
                            console.error("Error fetching columns:", columns.error);
                            return;
                        }

                        let formHtml = '';

                        columns.forEach(column => {
                            const isAutoIncrement = column.Extra.includes('auto_increment');
                            const isRequired = column.Null === 'NO' && !isAutoIncrement && column.Default === null;
                            const isDisabled = isAutoIncrement;

                            formHtml += `<div class="form-group">
                                <label for="field_${column.Field}">
                                    <code>${column.Field}</code> ${isRequired ? '<span class="text-danger">*</span>' : ''}
                                </label>`;

                            if (column.Type.includes('text')) {
                                formHtml += `<textarea class="form-control" id="field_${column.Field}" name="${column.Field}" 
                                    ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}></textarea>`;
                            } else if (column.Type.includes('enum')) {
                                // Extract enum values
                                const enumValues = column.Type.match(/enum\(\'(.*?)\'\)/)[1].split("','");
                                formHtml += `<select class="form-control" id="field_${column.Field}" name="${column.Field}" 
                                    ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>
                                    <option value="">-- Seleziona --</option>`;

                                enumValues.forEach(val => {
                                    formHtml += `<option value="${val}">${val}</option>`;
                                });

                                formHtml += `</select>`;
                            } else if (column.Type.includes('date')) {
                                formHtml += `<input type="date" class="form-control" id="field_${column.Field}" name="${column.Field}" 
                                    ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>`;
                            } else if (column.Type.includes('datetime')) {
                                formHtml += `<input type="datetime-local" class="form-control" id="field_${column.Field}" name="${column.Field}" 
                                    ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>`;
                            } else if (column.Type.includes('int')) {
                                formHtml += `<input type="number" class="form-control" id="field_${column.Field}" name="${column.Field}" 
                                    ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>`;
                            } else {
                                formHtml += `<input type="text" class="form-control" id="field_${column.Field}" name="${column.Field}" 
                                    ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>`;
                            }

                            formHtml += `<small class="form-text text-muted">
                                <span class="type-badge ${column.Type.includes('int') ? 'type-int' : column.Type.includes('varchar') ? 'type-varchar' : column.Type.includes('text') ? 'type-text' : column.Type.includes('date') ? 'type-date' : 'type-other'}">${column.Type}</span>`;

                            if (column.Default !== null) {
                                formHtml += ` <span class="text-info">Default: ${column.Default}</span>`;
                            }

                            if (isAutoIncrement) {
                                formHtml += ` <span class="text-warning">Auto Increment</span>`;
                            }

                            formHtml += `</small></div>`;
                        });

                        $('#formFields').html(formHtml);
                        $('#addRecordModal').modal('show');
                    } catch (e) {
                        console.error("Failed to parse columns response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching columns.");
                }
            });
        }

        function generateEditRecordForm(tableName, id) {
            $.ajax({
                url: 'get_record.php',
                method: 'GET',
                data: { table: tableName, id: id },
                success: function (response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.error) {
                            showAlert(result.error, 'danger');
                            return;
                        }

                        const record = result.data;

                        // Get table structure to create the form
                        $.ajax({
                            url: 'get_table_structure.php',
                            method: 'GET',
                            data: { table: tableName },
                            success: function (structResponse) {
                                try {
                                    const columns = JSON.parse(structResponse);

                                    if (columns.error) {
                                        console.error("Error fetching columns:", columns.error);
                                        return;
                                    }

                                    let formHtml = '';
                                    let primaryKeyField = '';

                                    columns.forEach(column => {
                                        const isAutoIncrement = column.Extra.includes('auto_increment');
                                        const isRequired = column.Null === 'NO' && !isAutoIncrement && column.Default === null;
                                        const isDisabled = isAutoIncrement;
                                        const fieldValue = record[column.Field];

                                        // Store primary key
                                        if (column.Key === 'PRI') {
                                            primaryKeyField = column.Field;
                                            $('#editRecordId').val(fieldValue);
                                        }

                                        formHtml += `<div class="form-group">
                                            <label for="edit_field_${column.Field}">
                                                <code>${column.Field}</code> ${isRequired ? '<span class="text-danger">*</span>' : ''}
                                            </label>`;

                                        if (column.Type.includes('text')) {
                                            formHtml += `<textarea class="form-control" id="edit_field_${column.Field}" name="${column.Field}" 
                                                ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>${fieldValue || ''}</textarea>`;
                                        } else if (column.Type.includes('enum')) {
                                            // Extract enum values
                                            const enumValues = column.Type.match(/enum\(\'(.*?)\'\)/)[1].split("','");
                                            formHtml += `<select class="form-control" id="edit_field_${column.Field}" name="${column.Field}" 
                                                ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>
                                                <option value="">-- Seleziona --</option>`;

                                            enumValues.forEach(val => {
                                                formHtml += `<option value="${val}" ${val === fieldValue ? 'selected' : ''}>${val}</option>`;
                                            });

                                            formHtml += `</select>`;
                                        } else if (column.Type.includes('date')) {
                                            formHtml += `<input type="date" class="form-control" id="edit_field_${column.Field}" name="${column.Field}" 
                                                value="${fieldValue || ''}" ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>`;
                                        } else if (column.Type.includes('datetime')) {
                                            // Format datetime for input
                                            let datetimeValue = '';
                                            if (fieldValue) {
                                                const date = new Date(fieldValue);
                                                datetimeValue = date.toISOString().slice(0, 16);
                                            }

                                            formHtml += `<input type="datetime-local" class="form-control" id="edit_field_${column.Field}" name="${column.Field}" 
                                                value="${datetimeValue}" ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>`;
                                        } else if (column.Type.includes('int')) {
                                            formHtml += `<input type="number" class="form-control" id="edit_field_${column.Field}" name="${column.Field}" 
                                                value="${fieldValue || ''}" ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>`;
                                        } else {
                                            formHtml += `<input type="text" class="form-control" id="edit_field_${column.Field}" name="${column.Field}" 
                                                value="${fieldValue || ''}" ${isRequired ? 'required' : ''} ${isDisabled ? 'disabled' : ''}>`;
                                        }

                                        formHtml += `<small class="form-text text-muted">
                                            <span class="type-badge ${column.Type.includes('int') ? 'type-int' : column.Type.includes('varchar') ? 'type-varchar' : column.Type.includes('text') ? 'type-text' : column.Type.includes('date') ? 'type-date' : 'type-other'}">${column.Type}</span>`;

                                        if (column.Default !== null) {
                                            formHtml += ` <span class="text-info">Default: ${column.Default}</span>`;
                                        }

                                        if (isAutoIncrement) {
                                            formHtml += ` <span class="text-warning">Auto Increment</span>`;
                                        }

                                        formHtml += `</small></div>`;
                                    });

                                    $('#editFormFields').html(formHtml);
                                    $('#editRecordModal').modal('show');
                                } catch (e) {
                                    console.error("Failed to parse structure response:", e);
                                }
                            },
                            error: function () {
                                console.error("Error fetching structure.");
                            }
                        });
                    } catch (e) {
                        console.error("Failed to parse record response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching record.");
                }
            });
        }

        $(document).ready(function () {
            // Load saved queries
            if (localStorage.getItem('savedQueries')) {
                const savedQueries = JSON.parse(localStorage.getItem('savedQueries'));

                for (const [name, query] of Object.entries(savedQueries)) {
                    $('#savedQueries').append(`<option value="${query}">${name}</option>`);
                }
            }

            // Load database info
            loadDatabaseInfo();

            // Load tables
            $.ajax({
                url: 'get_tables.php',
                method: 'GET',
                success: function (response) {
                    try {
                        const tables = JSON.parse(response);
                        if (tables.error) {
                            console.error("Error fetching tables:", tables.error);
                            return;
                        }

                        tables.forEach(table => {
                            $('#tables-list').append(
                                `<li class="list-group-item table-item" data-table="${table}">
                                    <i class="fas fa-table mr-2 text-primary"></i>${table.toUpperCase()}
                                </li>`
                            );
                        });
                    } catch (e) {
                        console.error("Failed to parse table list response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching table list.");
                }
            });

            // Table search
            $('#tableSearch').on('keyup', function () {
                const value = $(this).val().toLowerCase();
                $("#tables-list li").filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Clear search
            $('#clearSearch').on('click', function () {
                $('#tableSearch').val('');
                $("#tables-list li").show();
            });

            // Pagination controls
            $('#prev-page').on('click', function () {
                if (currentPage > 1) {
                    renderTableData(currentPage - 1);
                }
            });

            $('#next-page').on('click', function () {
                if (currentPage < totalPages) {
                    renderTableData(currentPage + 1);
                }
            });

            // Table click
            $(document).on('click', '.table-item', function () {
                const tableName = $(this).data('table');
                currentTable = tableName;

                $('.table-item').removeClass('active');
                $(this).addClass('active');

                $('#current-table-name').text(tableName.toUpperCase());
                $('#addRecordBtn').removeClass('d-none');

                // Load table data
                $.ajax({
                    url: 'get_table_data.php',
                    method: 'GET',
                    data: { table: tableName },
                    success: function (response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.error) {
                                console.error("Error fetching table data:", data.error);
                                $('#table-data').html(`<div class="alert alert-danger">${data.error}</div>`);
                                return;
                            }

                            // Store data for pagination
                            currentData = data;

                            // Calculate total pages
                            totalPages = Math.ceil(data.length / rowsPerPage);

                            // Render first page
                            currentPage = 1;
                            renderTableData(currentPage);

                            // Load table structure
                            loadTableStructure(tableName);
                        } catch (e) {
                            console.error("Failed to parse table data response:", e);
                        }
                    },
                    error: function () {
                        console.error("Error fetching table data.");
                    }
                });
            });

            // SQL Console
            $('a[href="sql"]').on('click', function (e) {
                e.preventDefault();
                $('#sqlModal').modal('show');
                // Reset query textarea and result area
                $('#sqlQuery').val('');
                $('#sqlResult').html('');
            });

            // Load saved query
            $('#savedQueries').on('change', function () {
                const query = $(this).val();
                if (query) {
                    $('#sqlQuery').val(query);
                }
            });

            // Clear SQL
            $('#clearSql').on('click', function () {
                $('#sqlQuery').val('');
                $('#sqlResult').html('');
            });

            // Execute SQL
            $('#executeSql').on('click', function () {
                const query = $('#sqlQuery').val();
                const saveQuery = $('#saveQuery').is(':checked');

                if (!query) {
                    showAlert('Inserisci una query SQL', 'warning');
                    return;
                }

                $.ajax({
                    url: 'execute_sql.php',
                    method: 'POST',
                    data: { query: query },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);

                            if (result.error) {
                                $('#sqlResult').html(`<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>${result.error}</div>`);
                            } else if (result.success) {
                                $('#sqlResult').html(`<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>${result.success}</div>`);

                                // Refresh table data if current table affected
                                if (currentTable && query.toLowerCase().includes(currentTable.toLowerCase())) {
                                    $('.table-item.active').click();
                                }
                            } else {
                                const data = result.data;
                                const columns = result.columns;

                                let tableHtml = `
                                <div class="mb-3">
                                    <span class="badge badge-primary p-2">${data.length} righe</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-custom">
                                        <thead>
                                            <tr>`;

                                columns.forEach(column => {
                                    tableHtml += `<th>${column}</th>`;
                                });

                                tableHtml += `</tr>
                                        </thead>
                                        <tbody>`;

                                data.forEach(row => {
                                    tableHtml += '<tr>';
                                    columns.forEach(column => {
                                        const value = row[column] === null ? '<em class="text-muted">NULL</em>' : row[column];
                                        tableHtml += `<td>${value}</td>`;
                                    });
                                    tableHtml += '</tr>';
                                });

                                tableHtml += `</tbody>
                                    </table>
                                </div>`;

                                $('#sqlResult').html(tableHtml);
                            }

                            // Save query
                            if (saveQuery && query.trim()) {
                                const queryName = prompt('Inserisci un nome per questa query:');

                                if (queryName) {
                                    let savedQueries = {};

                                    if (localStorage.getItem('savedQueries')) {
                                        savedQueries = JSON.parse(localStorage.getItem('savedQueries'));
                                    }

                                    savedQueries[queryName] = query;
                                    localStorage.setItem('savedQueries', JSON.stringify(savedQueries));

                                    // Add to dropdown
                                    $('#savedQueries').append(`<option value="${query}">${queryName}</option>`);

                                    showAlert('Query salvata con successo', 'success');
                                }
                            }
                        } catch (e) {
                            console.error("Failed to parse SQL response:", e);
                        }
                    },
                    error: function () {
                        console.error("Error executing SQL query.");
                    }
                });
            });

            // Add record button
            $('#addRecordBtn').on('click', function () {
                generateAddRecordForm(currentTable);
            });

            // Save record
            $('#saveRecordBtn').on('click', function () {
                const formData = $('#addRecordForm').serializeArray();
                const data = {};

                formData.forEach(item => {
                    data[item.name] = item.value;
                });

                $.ajax({
                    url: 'add_record.php',
                    method: 'POST',
                    data: {
                        table: currentTable,
                        data: JSON.stringify(data)
                    },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);

                            if (result.error) {
                                showAlert(result.error, 'danger');
                            } else {
                                showAlert('Record aggiunto con successo', 'success');
                                $('#addRecordModal').modal('hide');

                                // Refresh table data
                                $('.table-item.active').click();
                            }
                        } catch (e) {
                            console.error("Failed to parse add record response:", e);
                        }
                    },
                    error: function () {
                        console.error("Error adding record.");
                    }
                });
            });

            // Edit record button
            $(document).on('click', '.edit-row-btn', function () {
                const id = $(this).data('id');
                generateEditRecordForm(currentTable, id);
            });

            // Update record
            $('#updateRecordBtn').on('click', function () {
                const formData = $('#editRecordForm').serializeArray();
                const data = {};
                const id = $('#editRecordId').val();

                formData.forEach(item => {
                    if (item.name !== 'id') {
                        data[item.name] = item.value;
                    }
                });

                $.ajax({
                    url: 'edit_record.php',
                    method: 'POST',
                    data: {
                        table: currentTable,
                        id: id,
                        data: JSON.stringify(data)
                    },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);

                            if (result.error) {
                                showAlert(result.error, 'danger');
                            } else {
                                showAlert(result.message || 'Record aggiornato con successo', 'success');
                                $('#editRecordModal').modal('hide');

                                // Refresh table data
                                $('.table-item.active').click();
                            }
                        } catch (e) {
                            console.error("Failed to parse update record response:", e);
                        }
                    },
                    error: function () {
                        console.error("Error updating record.");
                    }
                });
            });

            // Delete record
            $(document).on('click', '.delete-row-btn', function () {
                const id = $(this).data('id');

                confirmAction(`Sei sicuro di voler eliminare questo record (ID: ${id})?`, function () {
                    $.ajax({
                        url: 'delete_record.php',
                        method: 'POST',
                        data: {
                            table: currentTable,
                            id: id
                        },
                        success: function (response) {
                            try {
                                const result = JSON.parse(response);

                                if (result.error) {
                                    showAlert(result.error, 'danger');
                                } else {
                                    showAlert('Record eliminato con successo', 'success');

                                    // Refresh table data
                                    $('.table-item.active').click();
                                }
                            } catch (e) {
                                console.error("Failed to parse delete record response:", e);
                            }
                        },
                        error: function () {
                            console.error("Error deleting record.");
                        }
                    });
                });
            });

            // Database backup
            $('#backupDb').on('click', function () {
                window.location.href = 'backup_db.php';
            });

            // Refresh structure
            $('#refreshStructure').on('click', function () {
                loadDatabaseInfo();

                if (currentTable) {
                    $('.table-item.active').click();
                }

                showAlert('Struttura del database aggiornata', 'success');
            });

            // Table operations
            $('#optimizeTableBtn').on('click', function () {
                if (!currentTable) {
                    showAlert('Seleziona prima una tabella', 'warning');
                    return;
                }

                $.ajax({
                    url: 'table_operation.php',
                    method: 'POST',
                    data: {
                        table: currentTable,
                        operation: 'optimize'
                    },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);

                            if (result.error) {
                                showAlert(result.error, 'danger');
                            } else {
                                showAlert('Tabella ottimizzata con successo', 'success');
                            }
                        } catch (e) {
                            console.error("Failed to parse optimize response:", e);
                        }
                    },
                    error: function () {
                        console.error("Error optimizing table.");
                    }
                });
            });

            $('#repairTableBtn').on('click', function () {
                if (!currentTable) {
                    showAlert('Seleziona prima una tabella', 'warning');
                    return;
                }

                $.ajax({
                    url: 'table_operation.php',
                    method: 'POST',
                    data: {
                        table: currentTable,
                        operation: 'repair'
                    },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);

                            if (result.error) {
                                showAlert(result.error, 'danger');
                            } else {
                                showAlert('Tabella riparata con successo', 'success');
                            }
                        } catch (e) {
                            console.error("Failed to parse repair response:", e);
                        }
                    },
                    error: function () {
                        console.error("Error repairing table.");
                    }
                });
            });

            $('#truncateTableBtn').on('click', function () {
                if (!currentTable) {
                    showAlert('Seleziona prima una tabella', 'warning');
                    return;
                }

                confirmAction(`Sei sicuro di voler svuotare la tabella ${currentTable.toUpperCase()}? Tutti i dati saranno eliminati.`, function () {
                    $.ajax({
                        url: 'table_operation.php',
                        method: 'POST',
                        data: {
                            table: currentTable,
                            operation: 'truncate'
                        },
                        success: function (response) {
                            try {
                                const result = JSON.parse(response);

                                if (result.error) {
                                    showAlert(result.error, 'danger');
                                } else {
                                    showAlert('Tabella svuotata con successo', 'success');

                                    // Refresh table data
                                    $('.table-item.active').click();
                                }
                            } catch (e) {
                                console.error("Failed to parse truncate response:", e);
                            }
                        },
                        error: function () {
                            console.error("Error truncating table.");
                        }
                    });
                });
            });

            // Export operations
            $('#exportCSVBtn').on('click', function () {
                if (!currentTable) {
                    showAlert('Seleziona prima una tabella', 'warning');
                    return;
                }

                window.location.href = `export.php?table=${currentTable}&format=csv`;
            });

            $('#exportSQLBtn').on('click', function () {
                if (!currentTable) {
                    showAlert('Seleziona prima una tabella', 'warning');
                    return;
                }

                window.location.href = `export.php?table=${currentTable}&format=sql`;
            });

            $('#exportJSONBtn').on('click', function () {
                if (!currentTable) {
                    showAlert('Seleziona prima una tabella', 'warning');
                    return;
                }

                window.location.href = `export.php?table=${currentTable}&format=json`;
            });
        });
    </script>
</body>