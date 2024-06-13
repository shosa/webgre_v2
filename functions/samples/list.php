<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";
require_once "../../helpers/helpers.php";

$pdo = getDbInstance();
$stmt = $pdo->query("SELECT * FROM samples_modelli");
$modelli = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include BASE_PATH . "/components/header.php"; ?>
<style>
    @keyframes blink {
        0% {
            background-color: white;
        }


        50% {
            background-color: #fffaab;
        }

        100% {
            background-color: white;
        }
    }

    .notify-row {
        animation: blink 1.5s infinite;
    }
</style>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>
                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Elenco Modelli</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Elenco Modelli</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome Modello</th>
                                            <th>Variante</th>
                                            <th>Forma</th>
                                            <th>Consegna</th>
                                            <th>Avanzamento</th>
                                            <th>Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($modelli as $modello): ?>
                                            <tr class="<?php echo $modello['notify_edits'] ? 'notify-row' : ''; ?>">
                                                <td><?php echo htmlspecialchars($modello['id']); ?></td>
                                                <td><?php echo htmlspecialchars($modello['nome_modello']); ?></td>
                                                <td><?php echo htmlspecialchars($modello['variante']); ?></td>
                                                <td><?php echo htmlspecialchars($modello['forma']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($modello['consegna']))); ?>
                                                </td>
                                                <td></td>
                                                <td>
                                                    <a href="editDiba.php?model_id=<?php echo htmlspecialchars($modello['id']); ?>"
                                                        class="btn btn-primary btn-sm"><i class="fas fa-pencil-alt"></i></a>
                                                    <?php if ($modello['notify_edits']): ?>
                                                        <button class="btn btn-warning btn-sm ml-2"><i
                                                                class="fas fa-bell-exclamation"></i></button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
            <?php include_once BASE_PATH . "/components/footer.php"; ?>
        </div>
    </div>
</body>