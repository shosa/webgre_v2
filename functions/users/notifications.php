<?php
// Includi il file di configurazione e avvia la sessione
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
include BASE_PATH . "/components/header.php";

// Verifica se è stata ricevuta una richiesta POST per aggiornare il tema
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
        $_SESSION['danger'] = "Errore durante l'aggiornamento del tema.";
    }
}

// Recupera tutte le notifiche dell'utente corrente
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY timestamp DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $notifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['danger'] = "Errore durante il recupero delle notifiche.";
}

?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include BASE_PATH . "/components/navbar.php"; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Centro Notifiche</h1>
                    </div>
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Centro Notifiche</li>
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
                                    <h6 class="m-0 font-weight-bold text-primary">Tutte le notifiche</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <?php foreach ($notifiche as $notifica): ?>
                                            <span 
                                                class="list-group-item list-group-item-action border-left-<?php echo $notifica['type']; ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1"><?php echo $notifica['message']; ?></h5>
                                                    <small><?php echo $notifica['timestamp']; ?></small>
                                                </div>


                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <?php include BASE_PATH . "/components/footer.php"; ?>
                <!-- End of Footer -->
            </div>
            <!-- End of Content Wrapper -->
        </div>

    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/datatables.js"></script>

</body>