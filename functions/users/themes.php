<?php
// Includi il file di configurazione e avvia la sessione
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/helpers/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
include (BASE_PATH . "/components/header.php");

// Verifica se è stata ricevuta una richiesta POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['colore'])) {
    $coloreScelto = $_POST['colore'];

    // Aggiorna il colore nel database
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Log Attività</h1>
                    </div>
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Log Attività</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-3 col-lg-4">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Utente</h6>
                                </div>
                                <div class="card-body text-left">
                                    <span class="text-left"> ID: <?php echo $_SESSION['user_id']; ?></span>
                                </div>
                                <div class="card-body text-center">
                                    <i class="fas fa-user-circle fa-8x  " style="color: #74C0FC;"></i>
                                    <h3> <?php echo $_SESSION['nome']; ?></h3>
                                    <h6> <?php echo $_SESSION['username']; ?></h6>
                                    <span> <?php echo $_SESSION['tipo']; ?></span>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-9 col-lg-8">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Tema Menù</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php
                                        // Array dei colori disponibili
                                        $colori = array("success", "primary", "indigo", "info", "warning", "pink", "danger", "dark");
                                        $nomi = array("VERDE", "BLU", "INDIGO", "CIANO", "ARANCIO", "ROSA", "ROSSO", "SCURO");

                                        // Genera pulsanti per i colori
                                        foreach ($colori as $index => $colore) {
                                            echo '<div class="col-md-6 mb-2">';
                                            echo '<form method="POST" action="">';
                                            echo '<input type="hidden" name="colore" value="' . $colore . '">';
                                            echo '<button type="submit" class="btn btn-lg text-white btn-' . $colore . ' w-100">' . $nomi[$index] . '</button>';
                                            echo '</form>';
                                            echo '</div>';
                                        }
                                        ?>
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
                <?php include (BASE_PATH . "/components/footer.php"); ?>
                <!-- End of Footer -->
            </div>
            <!-- End of Content Wrapper -->
        </div>

    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script src="<?php echo BASE_URL?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL?>/js/sb-admin-2.min.js"></script>
    <script src="<?php echo BASE_URL?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL?>/js/datatables.js"></script>

</body>