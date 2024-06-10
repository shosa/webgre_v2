<?php
include ("config/config.php");
session_start();
require_once BASE_PATH . '/components/auth_validate.php';


// Ottieni il tipo di utente dall'array di sessione
$tipoUtente = $_SESSION['admin_type'];

// Ottieni un'istanza del database utilizzando PDO
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Prepara le query per ottenere le informazioni del dashboard
$queryNumRiparazioni = "SELECT SUM(QTA) FROM riparazioni";
$stmtNumRiparazioni = $pdo->query($queryNumRiparazioni);
$numRiparazioni = $stmtNumRiparazioni->fetchColumn();

$queryNumDaCompletare = "SELECT SUM(paia) FROM lanci WHERE stato = 'IN ATTESA'";
$stmtNumDaCompletare = $pdo->query($queryNumDaCompletare);
$numDaCompletare = $stmtNumDaCompletare->fetchColumn();

$queryNome = "SELECT nome FROM utenti WHERE user_name = :username";
$stmtNome = $pdo->prepare($queryNome);
$stmtNome->bindParam(':username', $_SESSION["username"], PDO::PARAM_STR);
$stmtNome->execute();
$nome = $stmtNome->fetchColumn();
$data_oggi = date('d/m/Y');
try {
    // Query per contare i record con la data odierna
    $sql = "SELECT COUNT(*) AS num_records FROM cq_records WHERE data = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data_oggi]);

    // Ottieni il risultato della query
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_cq_records = $row['num_records'];
} catch (PDOException $e) {
    // Gestione degli errori
    echo "Errore durante l'esecuzione della query: " . $e->getMessage();
    $num_cq_records = 0; // Imposta il numero di record a 0 in caso di errore
}

?>

<?php include ("components/header.php"); ?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include ("components/navbar.php"); //INCLUSIONE NAVBAR ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->

                <?php include ("components/topbar.php"); //INCLUSIONE TOPBAR ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>

                    </div>

                    <!-- INIZIO ROW CARDS -->
                    <div class="row">
                        <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1): ?>
                            <!-- CARD RIPARAZIONI -->
                            <div class="col-xl-2 col-md-4 mb-4">
                                <div class="card border-left-warning shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    Riparazioni attive</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo empty($numRiparazioni) ? '0' : $numRiparazioni; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-hammer fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="../../functions/riparazioni/riparazioni"
                                        class="card-footer text-white bg-white text-warning">
                                        <span class="float-left">Apri Elenco</span>
                                        <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
                                        <div class="clearfix"></div>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1): ?>
                            <!-- CARD CQ -->
                            <div class="col-xl-2 col-md-4 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Test eseguiti oggi</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo empty($num_cq_records) ? '0' : $num_cq_records; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-box-check fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="../../functions/quality/detail?date=<?php echo $data_oggi ?>"
                                        class="card-footer text-white bg-white text-primary">
                                        <span class="float-left">Visualizza</span>
                                        <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
                                        <div class="clearfix"></div>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- CHIUSURA ROW CARDS -->
                </div>
            </div>
            <!-- End of Main Content -->
            <?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>


    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

