<?php
// File: updateAvanzamento.php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";
require_once "../../helpers/helpers.php";

$pdo = getDbInstance();
$modelliStmt = $pdo->query("SELECT id, nome_modello FROM samples_modelli");
$modelli = $modelliStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $modello_id = $_POST['modello_id'];
    $stato_taglio = isset($_POST['stato_taglio']) ? 1 : 0;
    $data_taglio = $stato_taglio ? date('Y-m-d H:i:s') : null;
    $stato_orlatura = isset($_POST['stato_orlatura']) ? 1 : 0;
    $data_orlatura = $stato_orlatura ? date('Y-m-d H:i:s') : null;
    $stato_montaggio = isset($_POST['stato_montaggio']) ? 1 : 0;
    $data_montaggio = $stato_montaggio ? date('Y-m-d H:i:s') : null;
    $stato_spedito = isset($_POST['stato_spedito']) ? 1 : 0;
    $data_spedito = $stato_spedito ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare("REPLACE INTO samples_avanzamenti (modello_id, stato_taglio, data_taglio, stato_orlatura, data_orlatura, stato_montaggio, data_montaggio, stato_spedito, data_spedito) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$modello_id, $stato_taglio, $data_taglio, $stato_orlatura, $data_orlatura, $stato_montaggio, $data_montaggio, $stato_spedito, $data_spedito]);

    header('Location: updateAvanzamento.php');
    exit;
}
?>

<?php include BASE_PATH . "/components/header.php"; ?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>
                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Aggiorna Avanzamento</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Aggiorna Avanzamento</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="POST" action="updateAvanzamento.php">
                                <div class="form-group">
                                    <label for="modello_id">Modello</label>
                                    <select name="modello_id" id="modello_id" class="form-control">
                                        <?php foreach ($modelli as $modello): ?>
                                            <option value="<?php echo htmlspecialchars($modello['id']); ?>">
                                                <?php echo htmlspecialchars($modello['nome_modello']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="stato_taglio"
                                        name="stato_taglio">
                                    <label class="form-check-label" for="stato_taglio">TAGLIO</label>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="stato_orlatura"
                                        name="stato_orlatura">
                                    <label class="form-check-label" for="stato_orlatura">ORLATURA</label>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="stato_montaggio"
                                        name="stato_montaggio">
                                    <label class="form-check-label" for="stato_montaggio">MONTAGGIO</label>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="stato_spedito"
                                        name="stato_spedito">
                                    <label class="form-check-label" for="stato_spedito">SPEDITO</label>
                                </div>
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script src="<?php echo BASE_PATH ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_PATH ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="<?php echo BASE_PATH ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="<?php echo BASE_PATH ?>/js/sb-admin-2.min.js"></script>
            <?php include BASE_PATH . "/components/footer.php"; ?>
        </div>
    </div>
</body>

</html>