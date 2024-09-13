<?php session_start(); require_once '../../config/config.php'; require_once BASE_PATH . '/components/auth_validate.php'; require_once BASE_PATH . '/components/header.php'; ?>

<body id="page-top">
    <div id="wrapper"> <?php include (BASE_PATH . "/components/navbar.php"); ?> <div id="content-wrapper"
            class="d-flex flex-column">
            <div id="content"> <?php include (BASE_PATH . "/components/topbar.php"); ?> <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?> <div
                        class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Database</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Gestione Database</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-3 col-lg-3">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Tabelle</h6> <a href="sql"
                                        class="btn btn-indigo ml-auto">Console</a>
                                </div>
                                <div class="card-body">
                                    <ul id="tables-list" class="list-group"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9 col-lg-9">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Visualizza</h6>
                                </div>
                                <div class="card-body">
                                    <div id="table-data" class="table-responsive"></div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Modal -->
                    <div class="modal fade" id="sqlModal" tabindex="-1" role="dialog" aria-labelledby="sqlModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="sqlModalLabel">SQL Console</h5> <button type="button"
                                        class="close" data-dismiss="modal" aria-label="Close"> <span
                                            aria-hidden="true">&times;</span> </button>
                                </div>
                                <div class="modal-body">
                                    <div class="btn-group mb-3" role="group" aria-label="Basic example"> <button
                                            type="button" class="btn btn-secondary"
                                            onclick="insertText('SELECT * FROM ')">SELECT *</button> <button
                                            type="button" class="btn btn-secondary"
                                            onclick="insertText('INSERT INTO ')">INSERT INTO</button> <button
                                            type="button" class="btn btn-secondary"
                                            onclick="insertText('UPDATE ')">UPDATE</button> <button type="button"
                                            class="btn btn-secondary" onclick="insertText('DELETE FROM ')">DELETE
                                            FROM</button> <button type="button" class="btn btn-pink"
                                            onclick="insertText(' WHERE ')">WHERE</button> <button type="button"
                                            class="btn btn-success" onclick="insertText(' ORDER BY ')">ORDER BY</button>
                                    </div> <textarea id="sqlQuery" class="form-control" rows="10"
                                        placeholder="Scrivi l'SQL qui..."
                                        style="background-color: #1e1e1e; color: #dcdcdc; font-family: monospace;"></textarea>
                                    <div id="sqlResult" class="mt-3"></div>
                                </div>
                                <div class="modal-footer"> <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Chiudi</button> <button type="button" id="executeSql"
                                        class="btn btn-success">Esegui</button> </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            <?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
    <script>
    function insertText(text) {
        var textarea = document.getElementById('sqlQuery');
        var cursorPos = textarea.selectionStart;
        var textBefore = textarea.value.substring(0, cursorPos);
        var textAfter = textarea.value.substring(cursorPos, textarea.value.length);
        textarea.value = textBefore + text + textAfter;
        textarea.focus();
        textarea.setSelectionRange(cursorPos + text.length, cursorPos + text.length);
    }
    $(document).ready(function() {
        $.ajax({
            url: 'get_tables.php',
            method: 'GET',
            success: function(response) {
                try {
                    const tables = JSON.parse(response);
                    if (tables.error) {
                        console.error("Error fetching tables:", tables.error);
                        return;
                    }
                    tables.forEach(table => {
                        $('#tables-list').append(
                            `<li class="list-group-item table-item" data-table="${table}">${table.toUpperCase()}</li>`
                        );
                    });
                } catch (e) {
                    console.error("Failed to parse table list response:", e);
                }
            },
            error: function() {
                console.error("Error fetching table list.");
            }
        });
        $(document).on('click', '.table-item', function() {
            const tableName = $(this).data('table');
            $('.table-item').removeClass('active');
            $(this).addClass('active');
            $.ajax({
                url: 'get_table_data.php',
                method: 'GET',
                data: {
                    table: tableName
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.error) {
                            console.error("Error fetching table data:", data.error);
                            $('#table-data').html(
                                `<p class="text-danger">${data.error}</p>`);
                            return;
                        }
                        let tableHtml =
                            '<table class="table table-bordered" id="dataTable"><thead><tr>';
                        if (data.length > 0) {
                            const headers = Object.keys(data[0]);
                            headers.forEach(header => {
                                tableHtml += `<th>${header}</th>`;
                            });
                            tableHtml += '</tr></thead><tbody>';
                            data.forEach(row => {
                                tableHtml += '<tr>';
                                headers.forEach(header => {
                                    tableHtml += `<td>${row[header]}</td>`;
                                });
                                tableHtml += '</tr>';
                            });
                            tableHtml += '</tbody></table>';
                        } else {
                            tableHtml = '<p>No data available in this table.</p>';
                        }
                        $('#table-data').html(tableHtml);
                        $.fn.dataTable.ext.type.order['num-html-pre'] = function(data) {
                            var num = data.replace(/<.*?>/g, '');
                            return parseFloat(num);
                        };
                        $('#dataTable').DataTable({
                            "columnDefs": [{
                                "type": "num-html",
                                "targets": 0
                            }],
                            "info": true,
                            "colReorder": true,
                            "order": [
                                [0, "desc"]
                            ],
                            dom: '<"top"Bf>rt<"bottom"lip><"clear">',
                            buttons: [{
                                extend: 'copy',
                                text: '<i class="fas fa-copy"></i> COPIA',
                                className: 'btn-primary'
                            }, {
                                extend: 'excel',
                                text: '<i class="fas fa-file-excel"></i> EXCEL',
                                className: 'btn-primary'
                            }, {
                                extend: 'pdf',
                                orientation: 'landscape',
                                text: '<i class="fas fa-file-pdf"></i> PDF',
                                className: 'btn-primary',
                                exportOptions: {
                                    columns: ':not(.notexport)'
                                }
                            }, {
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> STAMPA',
                                className: 'btn-primary'
                            }, ],
                            language: {
                                url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json"
                            },
                        });
                    } catch (e) {
                        console.error("Failed to parse table data response:", e);
                    }
                },
                error: function() {
                    console.error("Error fetching table data.");
                }
            });
        });
        $('a[href="sql"]').on('click', function(e) {
            e.preventDefault();
            $('#sqlModal').modal('show');
        });
        $('#executeSql').on('click', function() {
            const query = $('#sqlQuery').val();
            $.ajax({
                url: 'execute_sql.php',
                method: 'POST',
                data: {
                    query: query
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.error) {
                            $('#sqlResult').html(
                                `<p class="text-danger">${result.error}</p>`);
                        } else if (result.success) {
                            $('#sqlResult').html(
                                `<p class="text-success">${result.success}</p>`);
                        } else {
                            let tableHtml =
                                '<h6>Risultato:</h6><table class="table table-bordered table-responsive table-striped" id="sqlDataTable"><thead><tr>';
                            const columns = result.columns;
                            columns.forEach(column => {
                                tableHtml += `<th>${column}</th>`;
                            });
                            tableHtml += '</tr></thead><tbody>';
                            const data = result.data;
                            data.forEach(row => {
                                tableHtml += '<tr>';
                                columns.forEach(column => {
                                    tableHtml += `<td>${row[column]}</td>`;
                                });
                                tableHtml += '</tr>';
                            });
                            tableHtml += '</tbody></table>';
                            $('#sqlResult').html(tableHtml);
                            $('#sqlDataTable').DataTable({
                                "order": [
                                    [0, "desc"]
                                ]
                            });
                        }
                    } catch (e) {
                        console.error("Failed to parse SQL response:", e);
                    }
                },
                error: function() {
                    console.error("Error executing SQL query.");
                }
            });
        });
    });
    </script>
</body>