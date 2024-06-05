<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
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
                                    <h6 class="m-0 font-weight-bold text-primary">Tabelle</h6>
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
                    </div>
                </div>
            </div>
            <script src="<?php BASE_PATH ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php BASE_PATH ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="<?php BASE_PATH ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="<?php BASE_PATH ?>/js/sb-admin-2.min.js"></script>
            <script src="<?php BASE_PATH ?>/vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="<?php BASE_PATH ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Fetch table list
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
                            $('#tables-list').append(`<li class="list-group-item table-item" data-table="${table}">${table}</li>`);
                        });
                    } catch (e) {
                        console.error("Failed to parse table list response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching table list.");
                }
            });

            // Fetch table data on table click
            $(document).on('click', '.table-item', function () {
                const tableName = $(this).data('table');
                $.ajax({
                    url: 'get_table_data.php',
                    method: 'GET',
                    data: { table: tableName },
                    success: function (response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.error) {
                                console.error("Error fetching table data:", data.error);
                                $('#table-data').html(`<p class="text-danger">${data.error}</p>`);
                                return;
                            }

                            // Generate table
                            let tableHtml = '<table class="table table-bordered" id="dataTable"><thead><tr>';
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

                            $('#dataTable').DataTable({
                                "order": [[0, "desc"]]
                            });
                        } catch (e) {
                            console.error("Failed to parse table data response:", e);
                        }
                    },
                    error: function () {
                        console.error("Error fetching table data.");
                    }
                });
            });
        });

    </script>
</body>