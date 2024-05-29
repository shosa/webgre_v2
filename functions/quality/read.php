<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';

// Connessione al database utilizzando PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recupera tutte le date disponibili nel database
    $stmt = $pdo->query("SELECT DISTINCT data FROM cq_records ORDER BY data DESC");
    $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
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
                        <h1 class="h3 mb-0 text-gray-800">Controllo Qualità</h1>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Date disponibili da consultare</h6>
                        </div>
                        <div class="card-body">
                            <ul>
                                <?php foreach ($dates as $date): ?>
                                    <li><a
                                            href="detail?date=<?php echo htmlspecialchars($date['data'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($date['data'], ENT_QUOTES, 'UTF-8'); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
   
    <?php include_once BASE_PATH . '/components/scripts.php'; ?>
</body>