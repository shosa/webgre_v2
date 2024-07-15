<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
include (BASE_PATH . "/components/header.php");
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include (BASE_PATH . "/components/navbar.php"); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Log Attività</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Log Attività Generale</li>
                    </ol>
                    <div class="col-xl-12 col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Log Attività Admin</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="dataTable" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Utente</th>
                                                <th>Categoria</th>
                                                <th>Tipo</th>
                                                <th>Descrizione</th>
                                                <th>Note</th>
                                                <?php if ($_SESSION['tipo'] == 'Admin' || $_SESSION['tipo'] == 'Super') {
                                                    echo " <th>Query</th>";
                                                } ?>
                                                <th>Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Connessione al database utilizzando PDO
                                            $conn = getDbInstance(); // Suppongo che questa funzione restituisca un'istanza di PDO già configurata
                                            $sql = "SELECT activity_log.*, utenti.user_name 
                                                FROM activity_log 
                                                JOIN utenti ON activity_log.user_id = utenti.id 
                                                ORDER BY activity_log.id DESC";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->execute();
                                            $activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            // Iterazione attraverso le righe del risultato della query
                                            foreach ($activity_logs as $log) {
                                                echo "<tr>";
                                                echo "<td>{$log['id']}</td>";
                                                echo "<td>{$log['user_name']}</td>";
                                                echo "<td>{$log['category']}</td>";
                                                echo "<td>{$log['activity_type']}</td>";
                                                echo "<td>{$log['description']}</td>";
                                                echo "<td>{$log['note']}</td>";

                                                // Visualizza la colonna "Query" solo per Admin e Super se il campo "text_query" non è vuoto
                                                if (($_SESSION['tipo'] == 'Admin' || $_SESSION['tipo'] == 'Super') && !empty($log['text_query'])) {
                                                    echo '<td class="text-center">';
                                                    echo "<i class='fal fa-search view-query' style='cursor: pointer; color: #007bff;' data-query-id='{$log['id']}' data-toggle='modal' data-target='#queryModal'></i>";
                                                    echo '</td>';
                                                }
                                                if (($_SESSION['tipo'] == 'Admin' || $_SESSION['tipo'] == 'Super') && empty($log['text_query'])) {
                                                    echo '<td>';

                                                    echo "</td>";
                                                }
                                                echo "<td>{$log['created_at']}</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- MODALE QUERY -->
            <div class="modal fade" id="queryModal" tabindex="-1" role="dialog" aria-labelledby="queryModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="queryModalLabel">Query Dettagliata</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <textarea id="queryText" class="form-control" rows="10" readonly></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
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
            <?php include (BASE_PATH . "/components/footer.php"); ?>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>

    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const viewButtons = document.querySelectorAll('.view-query');

        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const queryId = this.dataset.queryId;
                // Effettua una richiesta AJAX per ottenere il testo completo della query
                $.ajax({
                    url: 'get_query.php',
                    method: 'POST',
                    data: { query_id: queryId },
                    success: function (response) {
                        document.getElementById('queryText').textContent = response;
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });
    });

</script>