<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/helpers/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';


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
    $statement = $pdo->prepare("SELECT URGENZA, IDRIP, CODICE, ARTICOLO, QTA, CARTELLINO, DATA, REPARTO, LINEA, COMPLETA FROM riparazioni");

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

include (BASE_PATH . "/components/header.php");

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
                    <?php include (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Elenco Riparazioni</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Attive</h6>
                            <a href="add_step1?operation=create" class="btn btn-info ml-auto"
                                style="margin-left:5px;"><i class="fal fa-plus-circle fa-l"></i> NUOVA</a>
                        </div>


                        <div class="card-body">
                            <table class="table table-bordered table-responsive table-striped " id="dataTable"
                                width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="15%">Codice</th>
                                        <th width="35%">Articolo</th>
                                        <th width="5%">Quantità</th>
                                        <th width="10%">Cartellino</th>
                                        <th width="5%">Data</th>
                                        <th width="10%">Reparto</th>
                                        <th width="5%">Linea</th>
                                        <th class='notexport' width="10%">Azioni</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row) { ?>
                                        <!-- MODALE CANCELLA  -->
                                        <div class="modal fade" style="z-index: 5000"
                                            id="confirm-delete-<?php echo $row['IDRIP']; ?>" role="dialog"
                                            aria-labelledby="confirm-delete-modal-label" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered ">
                                                <form action="delete_riparazioni.php" method="POST">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="confirm-delete-modal-label">Conferma
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body"
                                                            style="color: #f96363; background: #ffe9e9;">
                                                            <input type="hidden" name="del_id" id="del_id"
                                                                value="<?php echo $row['IDRIP']; ?>">
                                                            <p>Sicuro di voler procedere ad eliminare questa riga?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-danger">Si</button>
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">No</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <tr>
                                            <td
                                                class="align-middle border-left-<?php echo getUrgencyColor($row['URGENZA']); ?>">
                                                <?php echo $row['IDRIP']; ?>
                                            </td>
                                            <td class="align-middle"><?php echo $row['CODICE']; ?></td>
                                            <td class="align-middle"><?php echo $row['ARTICOLO']; ?></td>
                                            <td class="align-middle"><?php echo $row['QTA']; ?></td>
                                            <td class="align-middle"><?php echo $row['CARTELLINO']; ?></td>
                                            <td class="align-middle"><?php echo $row['DATA']; ?></td>
                                            <td class="align-middle"><?php echo $row['REPARTO']; ?></td>
                                            <td class="align-middle"><?php echo $row['LINEA']; ?></td>
                                            <td class="align-middle">
                                                <div class="btn-group">
                                                    <a href="#" class="btn btn-success show-record-details"
                                                        data-record-id="<?php echo htmlspecialchars($row['IDRIP']); ?>">
                                                        <i class="fal fa-search"></i>
                                                    </a>
                                                    <a href="edit_riparazioni.php?riparazione_id=<?php echo htmlspecialchars($row['IDRIP']); ?>&operation=edit"
                                                        class="btn btn-primary">
                                                        <i class="fal fa-edit"></i>
                                                    </a>
                                                    <div class="btn-group">
                                                        <a href="file_preview.php?riparazione_id=<?php echo htmlspecialchars($row['IDRIP']); ?>"
                                                            type="button" class="btn btn-warning">
                                                            <i class="fal fa-print"></i>
                                                        </a>
                                                        <div class="btn-group">
                                                            <button type="button"
                                                                class="btn btn-warning dropdown-toggle btn-xs"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <span class="caret"></span>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item"
                                                                    href="download_report.php?riparazione_id=<?php echo htmlspecialchars($row['IDRIP']); ?>">
                                                                    <i class="fal fa-download"></i> Scarica
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                                                        data-target="#confirm-delete-<?php echo htmlspecialchars($row['IDRIP']); ?>">
                                                        <i class="fal fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include (BASE_PATH . "/components/footer.php"); ?>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- MODALE DETTAGLI -->
    <div class="modal fade" id="record-details-modal" tabindex="-1" role="dialog"
        aria-labelledby="record-details-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
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
    <script src="<?php BASE_PATH ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php BASE_PATH ?>/js/sb-admin-2.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/datatables/buttons.bootstrap4.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/jszip/jszip.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/pdfmake/pdfmake.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/pdfmake/vfs_fonts.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/datatables/buttons.html5.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/datatables/buttons.print.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/datatables/buttons.colVis.min.js"></script>
    <script src="<?php BASE_PATH ?>/vendor/datatables/dataTables.colReorder.min.js"></script>
    <script src="<?php BASE_PATH ?>/js/datatables.js"></script>
    <?php include_once BASE_PATH . '/components/footer.php'; ?>
</body>

<script></script>