<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/config.php';

try {
    // Connessione al database utilizzando PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
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
    $statement = $pdo->prepare("SELECT IDRIP, CODICE, ARTICOLO, QTA, CARTELLINO, DATA, REPARTO, LINEA, COMPLETA FROM riparazioni");

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
                        <h1 class="h3 mb-0 text-gray-800">Pagina Test</h1>
                    </div>

                    <!-- QUI VA IL CONTENUTO -->

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
    <?php include_once BASE_PATH . '/components/script.php'; ?>

</body>