<?php ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
include (BASE_PATH . "/components/header.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['colore'])) {
    $coloreScelto = $_POST['colore'];
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare("UPDATE utenti SET theme_color = :theme_color WHERE id = :user_id");
        $stmt->bindParam(':theme_color', $coloreScelto, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        logActivity($_SESSION['user_id'], 'OPZIONI', 'AGGIORNAMENTO', 'Aggiornato Tema', "COLORE: " . $coloreScelto, '');
        $_SESSION['tema'] = $coloreScelto;
        $_SESSION['success'] = "Tema Aggiornato!";
    } catch (PDOException $e) {
        $_SESSION['danger'] = "Tema Aggiornato!";
    }
} ?>

<body id="page-top">
    <div id="wrapper"><?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content"><?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <div class="mb-4 align-items-center d-sm-flex justify-content-between">
                        <h1 class="h3 mb-0 text-gray-800">Scelta Tema </h1>
                    </div><?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <ol class="mb-4 breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Scelta Tema</li>
                    </ol>

                    <div class="col-lg-12 col-xl-12">
                        <div class="mb-4 card shadow">
                            <div class="align-items-center justify-content-between card-header d-flex flex-row py-3">
                                <h6 class="font-weight-bold m-0 text-primary">Tema Menù</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php $colori = array("success", "primary", "indigo", "info", "warning", "orange", "pink", "danger", "dark");
                                    $nomi = array("VERDE", "BLU", "INDIGO", "CIANO", "GIALLO", "ARANCIONE", "ROSA", "ROSSO", "SCURO");
                                    foreach ($colori as $index => $colore) {
                                        echo '<div class="col-md-6 mb-2">';
                                        echo '<form method="POST" action="">';
                                        echo '<input type="hidden" name="colore" value="' . $colore . '">';
                                        echo '<button type="submit" class="btn btn-lg text-white btn-' . $colore . ' w-100">' . $nomi[$index] . '</button>';
                                        echo '</form>';
                                        echo '</div>';
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fade modal" id="queryModal" aria-hidden="true" aria-labelledby="queryModalLabel" role="dialog"
                tabindex="-1">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="queryModalLabel">Query Dettagliata</h5><button class="close"
                                data-dismiss="modal" type="button" aria-label="Close"><span
                                    aria-hidden="true">×</span></button>
                        </div>
                        <div class="modal-body"><textarea class="form-control" id="queryText" readonly
                                rows="10"></textarea></div>
                        <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal"
                                type="button">Chiudi</button></div>
                    </div>
                </div>
            </div><?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
    </div><a href="#page-top" class="rounded scroll-to-top"><i class="fas fa-angle-up"></i></a>
    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/datatables.js"></script>
</body>