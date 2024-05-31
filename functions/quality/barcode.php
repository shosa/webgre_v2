<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
include_once BASE_PATH . '/components/header.php';
require_once '../../utils/log_utils.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'];

        if ($action === 'add') {
            $code = $_POST['code'];
            $test = $_POST['test'];

            $sql = "INSERT INTO cq_barcodes (code, test) VALUES (:code, :test)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->bindParam(':test', $test, PDO::PARAM_STR);
            $stmt->execute();
            logActivity($_SESSION['user_id'], 'CQ_BARCODES', 'CREA', 'Creato Barcode', $code . ' / ' . $test, '');
            $_SESSION['success'] = 'Barcode aggiunto con successo!';
        } elseif ($action === 'delete') {
            $code = $_POST['code'];

            $sql = "DELETE FROM cq_barcodes WHERE code = :code";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();
            logActivity($_SESSION['user_id'], 'CQ_BARCODES', 'ELIMINA', 'Eliminato Barcode ', $code, '');
            $_SESSION['success'] = 'Barcode eliminato con successo!';
        }
    }

    // Fetch barcodes
    $sql = "SELECT code, test FROM cq_barcodes";
    $stmt = $pdo->query($sql);
    $barcodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Errore di connessione: " . $e->getMessage();
}
?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Controllo Qualità</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Gestione Barcodes</li>
                    </ol>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success'];
                            unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-xl-9 col-lg-8">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Associazioni Barcodes</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mt-4" id="dataTable">
                                            <thead>
                                                <tr>
                                                    <th width="30%">Codice</th>
                                                    <th width="65%">Test</th>
                                                    <th width="5%">Elimina</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($barcodes as $barcode): ?>
                                                    <tr>
                                                        <td class="align-middle">
                                                            <?php echo htmlspecialchars($barcode['code']); ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?php echo htmlspecialchars($barcode['test']); ?>
                                                        </td>
                                                        <td class="align-middle text-center">
                                                            <form method="POST" action="" style="display: inline-block;">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="code"
                                                                    value="<?php echo htmlspecialchars($barcode['code']); ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm"><i
                                                                        class="fal fa-trash fa-l"></i></button>
                                                            </form>

                                                            <form method="POST" action="" style="display: inline-block;">
                                                                <input type="hidden" name="action" value="edit">
                                                                <input type="hidden" name="code"
                                                                    value="<?php echo htmlspecialchars($barcode['code']); ?>">
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Aggiungi nuovo</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="add">
                                        <div class="form-group">
                                            <label for="code">Codice</label>
                                            <input type="text" class="form-control" id="code" name="code" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="test">Test</label>
                                            <input type="text" class="form-control" id="test" name="test" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">Aggiungi</button>
                                    </form>
                                </div>
                            </div>
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Tabellone Operativo</h6>
                                </div>
                                <div class="card-body">
                                    <a href="print_manual" class="btn btn-warning btn-lg btn-block"><i
                                            class="fal fa-print fa-l"></i>
                                        STAMPA</a>
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
                <script src="<?php BASE_PATH ?>/js/datatables.js"></script>
                <?php include_once BASE_PATH . '/components/footer.php'; ?>
            </div>
        </div>
</body>